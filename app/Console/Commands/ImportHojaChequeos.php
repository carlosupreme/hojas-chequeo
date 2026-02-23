<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportHojaChequeos extends Command
{
    protected $signature = 'import:hoja-chequeos
        {--host=127.0.0.1 : MySQL v1 host}
        {--port=3306 : MySQL v1 port}
        {--database= : MySQL v1 database name (required)}
        {--username=root : MySQL v1 username}
        {--password= : MySQL v1 password}
        {--dry-run : Preview records without saving}';

    protected $description = 'Import hoja_chequeos + items (JSON) into v2 hoja_chequeos / hoja_columnas / hoja_filas / hoja_fila_valors. Run import:equipos first.';

    public function handle(): int
    {
        if (! $this->option('database')) {
            $this->error('--database is required.');

            return self::FAILURE;
        }

        config()->set('database.connections.mysql_v1', [
            'driver' => 'mysql',
            'host' => $this->option('host'),
            'port' => $this->option('port'),
            'database' => $this->option('database'),
            'username' => $this->option('username'),
            'password' => $this->option('password'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
        ]);

        DB::purge('mysql_v1');

        $this->info('Connecting to MySQL v1...');

        try {
            $hojas = DB::connection('mysql_v1')->table('hoja_chequeos')->get();
        } catch (\Exception $e) {
            $this->error('Connection failed: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info("Found {$hojas->count()} hoja_chequeos in v1.");

        if ($this->option('dry-run')) {
            $this->table(
                ['v1 id', 'equipo_id', 'version', 'area'],
                $hojas->map(fn ($h) => [$h->id, $h->equipo_id, $h->version, $h->area ?? '-'])
            );

            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($hojas->count());
        $bar->start();

        $totalFilas = 0;
        $totalColumnas = 0;
        $totalValores = 0;

        foreach ($hojas as $hoja) {
            // ── 1. hoja_chequeos ─────────────────────────────────────────────
            // Drop v1-only fields: area, active, turno
            // Add v2-only field:   encendido = true
            //
            // Conflict key is (equipo_id, version) — not id — because v1 can
            // have duplicate (equipo_id, version) pairs with different ids.
            // Conflicting on id alone would trigger the secondary unique
            // constraint and raise a duplicate key error.
            DB::table('hoja_chequeos')->upsert(
                [
                    'equipo_id' => $hoja->equipo_id,
                    'version' => $hoja->version,
                    'observaciones' => $hoja->observaciones,
                    'encendido' => true,
                    'created_at' => $hoja->created_at,
                    'updated_at' => $hoja->updated_at,
                ],
                ['equipo_id', 'version'],
                ['observaciones', 'updated_at']
            );

            // Read back the v2 id (may differ from v1 id for duplicates)
            $v2HojaId = DB::table('hoja_chequeos')
                ->where('equipo_id', $hoja->equipo_id)
                ->where('version', $hoja->version)
                ->value('id');

            // ── 2. Fetch v1 items for this hoja ──────────────────────────────
            $items = DB::connection('mysql_v1')
                ->table('items')
                ->where('hoja_chequeo_id', $hoja->id)
                ->orderBy('id')
                ->get();

            if ($items->isEmpty()) {
                $bar->advance();

                continue;
            }

            // ── 3. Build hoja_columnas from the JSON keys of the first item ──
            // All items for the same hoja share the same key structure.
            $firstValores = json_decode($items->first()->valores, true) ?? [];
            $columnaIdByKey = [];  // lowercase key => hoja_columna.id in v2
            $colOrder = 0;

            foreach (array_keys($firstValores) as $jsonKey) {
                $key = strtolower($jsonKey);
                $label = ucfirst(strtolower($jsonKey));

                // insertOrIgnore respects the unique (hoja_chequeo_id, key) constraint
                DB::table('hoja_columnas')->insertOrIgnore([
                    'hoja_chequeo_id' => $v2HojaId,
                    'key' => $key,
                    'label' => $label,
                    'is_fixed' => true,
                    'order' => $colOrder++,
                    'created_at' => $hoja->created_at,
                    'updated_at' => $hoja->updated_at,
                ]);

                $columnaIdByKey[$key] = DB::table('hoja_columnas')
                    ->where('hoja_chequeo_id', $v2HojaId)
                    ->where('key', $key)
                    ->value('id');

                $totalColumnas++;
            }

            // ── 4. hoja_filas + hoja_fila_valors ─────────────────────────────
            $filaOrder = 0;

            foreach ($items as $item) {
                $valores = json_decode($item->valores, true) ?? [];
                $metodo = $valores['METODO'] ?? '';

                // answer_type_id 1 = icon_set (visual check)
                // answer_type_id 2 = numeric  (manual measurement)
                $answerTypeId = str_contains(strtolower($metodo), 'visual') ? 1 : 2;

                // Use v1 item.id as hoja_fila.id so hoja_fila_valors FK stays consistent
                DB::table('hoja_filas')->upsert(
                    [
                        'id' => $item->id,
                        'hoja_chequeo_id' => $v2HojaId,
                        'answer_type_id' => $answerTypeId,
                        'order' => $filaOrder++,
                        'categoria' => $item->categoria ?? 'limpieza',
                        'created_at' => $item->created_at,
                        'updated_at' => $item->updated_at,
                    ],
                    ['id'],
                    ['answer_type_id', 'order', 'categoria', 'updated_at']
                );

                $totalFilas++;

                // One hoja_fila_valor per cell (fila × columna)
                foreach ($valores as $jsonKey => $cellValue) {
                    $key = strtolower($jsonKey);

                    if (! isset($columnaIdByKey[$key])) {
                        continue; // skip keys not seen in the first item
                    }

                    DB::table('hoja_fila_valors')->upsert(
                        [
                            'hoja_fila_id' => $item->id,
                            'hoja_columna_id' => $columnaIdByKey[$key],
                            'valor' => (string) $cellValue,
                            'created_at' => $item->created_at,
                            'updated_at' => $item->updated_at,
                        ],
                        ['hoja_fila_id', 'hoja_columna_id'],
                        ['valor', 'updated_at']
                    );

                    $totalValores++;
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        // Reset all four sequences
        foreach (['hoja_chequeos', 'hoja_columnas', 'hoja_filas', 'hoja_fila_valors'] as $table) {
            DB::statement("SELECT setval('{$table}_id_seq', (SELECT MAX(id) FROM {$table}))");
        }
        $this->line('  Postgres sequences reset.');

        $this->info('Done.');
        $this->line("  hoja_chequeos : {$hojas->count()}");
        $this->line("  hoja_columnas : {$totalColumnas}");
        $this->line("  hoja_filas    : {$totalFilas}");
        $this->line("  hoja_fila_valors: {$totalValores}");

        return self::SUCCESS;
    }
}
