<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportReportes extends Command
{
    protected $signature = 'import:reportes
        {--host=127.0.0.1 : MySQL v1 host}
        {--port=3306 : MySQL v1 port}
        {--database= : MySQL v1 database name (required)}
        {--username=root : MySQL v1 username}
        {--password= : MySQL v1 password}
        {--dry-run : Preview records without saving}';

    protected $description = 'Import reportes from v1 MySQL into v2 PostgreSQL. Run import:equipos and import:users first.';

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
            $rows = DB::connection('mysql_v1')->table('reportes')->get();
        } catch (\Exception $e) {
            $this->error('Connection failed: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info("Found {$rows->count()} reportes in v1.");

        if ($this->option('dry-run')) {
            $this->table(
                ['v1 id', 'equipo_id', 'user_id', 'fecha', 'priority', 'estado'],
                $rows->map(fn ($r) => [$r->id, $r->equipo_id, $r->user_id, $r->fecha, $r->priority, $r->estado])
            );

            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($rows->count());
        $bar->start();

        $imported = 0;

        foreach ($rows as $row) {
            // Use DB::table() to bypass ReporteObserver::created() which sends
            // a Filament database notification to all admins on every insert
            DB::table('reportes')->upsert(
                [
                    'id' => $row->id,
                    'equipo_id' => $row->equipo_id,
                    'user_id' => $row->user_id,
                    'hoja_chequeo_id' => $row->hoja_chequeo_id,
                    'fecha' => $row->fecha,
                    'nombre' => $row->name,          // v1: name       → v2: nombre
                    'area' => $row->area,
                    'prioridad' => $row->priority,      // v1: priority   → v2: prioridad
                    'observaciones' => $row->observations,  // v1: observations → v2: observaciones
                    'falla' => $row->failure,       // v1: failure    → v2: falla
                    'foto' => $row->photo,         // v1: photo      → v2: foto
                    'estado' => $row->estado,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ],
                ['id'],
                ['equipo_id', 'user_id', 'hoja_chequeo_id', 'fecha', 'nombre', 'area', 'prioridad', 'observaciones', 'falla', 'foto', 'estado', 'updated_at']
            );

            $bar->advance();
            $imported++;
        }

        $bar->finish();
        $this->newLine();

        DB::statement("SELECT setval('reportes_id_seq', (SELECT MAX(id) FROM reportes))");
        $this->line('  Postgres sequence reset.');

        $this->info("Done. Imported/updated: {$imported} reportes.");

        return self::SUCCESS;
    }
}
