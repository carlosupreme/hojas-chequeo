<?php

namespace App\Console\Commands;

use App\Services\ImageService;
use App\Models\Turno;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportChequeosDiarios extends Command
{
    protected $signature = 'import:chequeos-diarios
        {--host=127.0.0.1 : MySQL v1 host}
        {--port=3306 : MySQL v1 port}
        {--database= : MySQL v1 database name (required)}
        {--username=root : MySQL v1 username}
        {--password= : MySQL v1 password}
        {--dry-run : Preview records without saving}';

    protected $description = 'Import chequeo_diarios + items into v2 hoja_ejecucions + hoja_fila_respuestas. Run import:hoja-chequeos and import:users first.';

    public function handle(): int
    {
        if (! $this->option('database')) {
            $this->error('--database is required.');

            return self::FAILURE;
        }

        config()->set('database.connections.mysql_v1', [
            'driver'    => 'mysql',
            'host'      => $this->option('host'),
            'port'      => $this->option('port'),
            'database'  => $this->option('database'),
            'username'  => $this->option('username'),
            'password'  => $this->option('password'),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => true,
        ]);

        DB::purge('mysql_v1');

        $this->info('Connecting to MySQL v1...');

        try {
            $rows = DB::connection('mysql_v1')->table('chequeo_diarios')->get();
        } catch (\Exception $e) {
            $this->error('Connection failed: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info("Found {$rows->count()} chequeo_diarios in v1.");

        if ($this->option('dry-run')) {
            $this->table(
                ['v1 id', 'hoja_chequeo_id', 'operador_id', 'nombre_operador', 'created_at'],
                $rows->map(fn ($r) => [$r->id, $r->hoja_chequeo_id, $r->operador_id, $r->nombre_operador, $r->created_at])
            );

            return self::SUCCESS;
        }

        // ── Build v1→v2 hoja_chequeo ID mapping ──────────────────────────────
        // ImportHojaChequeos upserted on (equipo_id, version), so v2 IDs may
        // differ from v1 IDs. Rebuild the mapping here from equipo_id+version.
        $this->info('Building hoja_chequeo ID mapping...');

        $v1Hojas = DB::connection('mysql_v1')
            ->table('hoja_chequeos')
            ->get(['id', 'equipo_id', 'version']);

        $hojaIdMap = []; // v1_id => v2_id

        foreach ($v1Hojas as $h) {
            $v2Id = DB::table('hoja_chequeos')
                ->where('equipo_id', $h->equipo_id)
                ->where('version', $h->version)
                ->value('id');

            if ($v2Id) {
                $hojaIdMap[$h->id] = $v2Id;
            }
        }

        $this->info('Mapping built for '.count($hojaIdMap).' hoja_chequeos.');

        $imageService = app(ImageService::class);

        $bar = $this->output->createProgressBar($rows->count());
        $bar->start();

        $imported        = 0;
        $turnoNotFound   = 0;
        $firmaErrors     = 0;
        $totalRespuestas = 0;

        foreach ($rows as $row) {
            $v2HojaId = $hojaIdMap[$row->hoja_chequeo_id] ?? null;

            if (! $v2HojaId) {
                $bar->advance();
                continue; // hoja_chequeo wasn't imported; skip
            }

            $timestamp = Carbon::parse($row->created_at);

            // Resolve turno + centro_costo from the execution timestamp
            $turno = Turno::findForTimestamp($timestamp, $v2HojaId);

            if (! $turno) {
                $turnoNotFound++;
            }

            // Store firma_operador: v1 stores raw base64 data URI, v2 expects a file path
            $firmaOperador = null;

            if (! empty($row->firma_operador)) {
                try {
                    $firmaOperador = $imageService->storeBase64('firmas', $row->firma_operador);
                } catch (\Exception $e) {
                    $firmaErrors++;
                }
            }

            $firmaSupervisor = null;

            if (! empty($row->firma_supervisor)) {
                try {
                    $firmaSupervisor = $imageService->storeBase64('firmas', $row->firma_supervisor);
                } catch (\Exception $e) {
                    $firmaErrors++;
                }
            }

            // Use DB::table() to bypass HojaEjecucionObserver::saved() which
            // sends a Filament notification to all admins for every finalizado record
            DB::table('hoja_ejecucions')->upsert(
                [
                    'id'               => $row->id,
                    'hoja_chequeo_id'  => $v2HojaId,
                    'user_id'          => $row->operador_id,  // v1: operador_id → v2: user_id
                    'turno_id'         => $turno?->id,
                    'centro_costo_id'  => $turno?->centro_costo_id,
                    'nombre_operador'  => $row->nombre_operador,
                    'firma_operador'   => $firmaOperador,
                    'firma_supervisor' => $firmaSupervisor,
                    'observaciones'    => $row->observaciones,
                    'finalizado_en'    => $row->created_at,   // v1 has no finalizado_en; use created_at
                    'created_at'       => $row->created_at,
                    'updated_at'       => $row->updated_at,
                ],
                ['id'],
                ['hoja_chequeo_id', 'user_id', 'turno_id', 'centro_costo_id', 'nombre_operador', 'firma_operador', 'firma_supervisor', 'observaciones', 'finalizado_en', 'updated_at']
            );

            // ── hoja_fila_respuestas ──────────────────────────────────────────
            $items = DB::connection('mysql_v1')
                ->table('item_chequeo_diarios')
                ->where('chequeo_diario_id', $row->id)
                ->get();

            foreach ($items as $item) {
                DB::table('hoja_fila_respuestas')->upsert(
                    [
                        'hoja_ejecucion_id' => $row->id,
                        'hoja_fila_id'      => $item->item_id,      // v1 item.id was preserved as hoja_fila.id
                        'answer_option_id'  => $item->simbologia_id ?? null,
                        'numeric_value'     => null,
                        'text_value'        => $item->valor ?? null,
                        'boolean_value'     => null,
                        'created_at'        => $item->created_at,
                        'updated_at'        => $item->updated_at,
                    ],
                    ['hoja_ejecucion_id', 'hoja_fila_id'],
                    ['answer_option_id', 'numeric_value', 'text_value', 'boolean_value', 'updated_at']
                );

                $totalRespuestas++;
            }

            $bar->advance();
            $imported++;
        }

        $bar->finish();
        $this->newLine();

        foreach (['hoja_ejecucions', 'hoja_fila_respuestas'] as $table) {
            DB::statement("SELECT setval('{$table}_id_seq', (SELECT MAX(id) FROM {$table}))");
        }
        $this->line('  Postgres sequences reset.');

        $this->info("Done. Imported: {$imported} ejecuciones, {$totalRespuestas} respuestas.");

        if ($turnoNotFound > 0) {
            $this->warn("{$turnoNotFound} records had no matching turno — turno_id/centro_costo_id set to NULL.");
        }

        if ($firmaErrors > 0) {
            $this->warn("{$firmaErrors} firma(s) could not be stored and were set to NULL.");
        }

        return self::SUCCESS;
    }
}
