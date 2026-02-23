<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportTarjetons extends Command
{
    protected $signature = 'import:tarjetons
        {--host=127.0.0.1 : MySQL v1 host}
        {--port=3306 : MySQL v1 port}
        {--database= : MySQL v1 database name (required)}
        {--username=root : MySQL v1 username}
        {--password= : MySQL v1 password}
        {--dry-run : Preview records without saving}';

    protected $description = 'Import tarjetons from v1 MySQL into v2 PostgreSQL. Run import:equipos first.';

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
            $rows = DB::connection('mysql_v1')->table('tarjetons')->get();
        } catch (\Exception $e) {
            $this->error('Connection failed: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info("Found {$rows->count()} tarjetons in v1.");

        if ($this->option('dry-run')) {
            $this->table(
                ['v1 id', 'equipo_id', 'fecha', 'estado', 'hora_encendido', 'hora_apagado'],
                $rows->map(fn ($r) => [$r->id, $r->equipo_id, $r->fecha, $r->estado, $r->hora_encendido ?? '-', $r->hora_apagado ?? '-'])
            );

            return self::SUCCESS;
        }

        // Validate all referenced equipo_ids exist in v2 before starting
        $equipoIds = DB::table('equipos')->pluck('id');
        $missing = collect($rows)->pluck('equipo_id')->unique()->diff($equipoIds);

        if ($missing->isNotEmpty()) {
            $this->error("These equipo_ids from v1 don't exist in v2: {$missing->join(', ')}");
            $this->error('Run import:equipos first.');

            return self::FAILURE;
        }

        $bar = $this->output->createProgressBar($rows->count());
        $bar->start();

        $imported = 0;

        foreach ($rows as $row) {
            // Use DB::table() to bypass the booted() hook (calcularTiempoOperacion)
            // which would overwrite tiempo_operacion_minutos using the wrong time format
            DB::table('tarjetons')->upsert(
                [
                    'id' => $row->id,
                    'equipo_id' => $row->equipo_id,
                    'fecha' => $row->fecha,
                    'hora_encendido' => $row->hora_encendido,
                    'hora_apagado' => $row->hora_apagado,
                    'encendido_por' => $row->encendido_por,
                    'apagado_por' => $row->apagado_por,
                    'tiempo_operacion_minutos' => $row->tiempo_operacion_minutos,
                    'observaciones' => $row->observaciones,
                    'estado' => $row->estado,
                    'falla_vapor' => false,  // new in v2
                    'falla_vapor_descripcion' => '',     // new in v2
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ],
                ['id'],
                ['equipo_id', 'fecha', 'hora_encendido', 'hora_apagado', 'encendido_por', 'apagado_por', 'tiempo_operacion_minutos', 'observaciones', 'estado', 'updated_at']
            );

            $bar->advance();
            $imported++;
        }

        $bar->finish();
        $this->newLine();

        DB::statement("SELECT setval('tarjetons_id_seq', (SELECT MAX(id) FROM tarjetons))");
        $this->line('  Postgres sequence reset.');

        $this->info("Done. Imported/updated: {$imported} tarjetons.");

        return self::SUCCESS;
    }
}
