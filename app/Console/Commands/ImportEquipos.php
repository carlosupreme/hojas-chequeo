<?php

namespace App\Console\Commands;

use App\Models\Equipo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportEquipos extends Command
{
    protected $signature = 'import:equipos
        {--host=127.0.0.1 : MySQL v1 host}
        {--port=3306 : MySQL v1 port}
        {--database= : MySQL v1 database name (required)}
        {--username=root : MySQL v1 username}
        {--password= : MySQL v1 password}
        {--dry-run : Preview records without saving}';

    protected $description = 'Import equipos from v1 MySQL into v2 PostgreSQL';

    public function handle(): int
    {
        if (! $this->option('database')) {
            $this->error('--database is required.');

            return self::FAILURE;
        }

        // Register a dynamic MySQL connection from the CLI options
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
            $rows = DB::connection('mysql_v1')->table('equipos')->get();
        } catch (\Exception $e) {
            $this->error('Connection failed: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info("Found {$rows->count()} equipos in v1.");

        if ($this->option('dry-run')) {
            $this->table(
                ['v1 id', 'tag', 'nombre', 'area', 'numeroControl', 'revision'],
                $rows->map(fn ($r) => [$r->id, $r->tag, $r->nombre, $r->area, $r->numeroControl ?? '-', $r->revision ?? '-'])
            );

            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($rows->count());
        $bar->start();

        $imported = 0;

        foreach ($rows as $row) {
            Equipo::updateOrCreate(
                // Match on tag (unique in v2)
                ['tag' => $row->tag],
                [
                    'nombre' => $row->nombre,
                    'area' => $row->area,
                    'foto' => $row->foto ?? null,
                    'numeroControl' => $row->numeroControl ?? null,
                    'revision' => $row->revision ?? null,
                ]
            );

            $bar->advance();
            $imported++;
        }

        $bar->finish();
        $this->newLine();
        $this->info("Done. Imported/updated: {$imported} equipos.");

        return self::SUCCESS;
    }
}
