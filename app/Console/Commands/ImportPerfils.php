<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportPerfils extends Command
{
    protected $signature = 'import:perfils
        {--host=127.0.0.1 : MySQL v1 host}
        {--port=3306 : MySQL v1 port}
        {--database= : MySQL v1 database name (required)}
        {--username=root : MySQL v1 username}
        {--password= : MySQL v1 password}
        {--dry-run : Preview records without saving}';

    protected $description = 'Import perfils from v1 MySQL into v2 PostgreSQL';

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
            $rows = DB::connection('mysql_v1')->table('perfils')->get();
        } catch (\Exception $e) {
            $this->error('Connection failed: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info("Found {$rows->count()} perfils in v1.");

        if ($this->option('dry-run')) {
            $this->table(
                ['v1 id', 'name', 'hoja_ids'],
                $rows->map(fn ($r) => [$r->id, $r->name, $r->hoja_ids])
            );

            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($rows->count());
        $bar->start();

        $imported = 0;

        foreach ($rows as $row) {
            // Normalize hoja_ids: v1 can have mixed int/string values like [33,"3","35"]
            $hojaIds = collect(json_decode($row->hoja_ids, true) ?? [])
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->toArray();

            DB::table('perfils')->upsert(
                [
                    'id' => $row->id,
                    'nombre' => $row->name,  // v1: name → v2: nombre
                    'hoja_ids' => json_encode($hojaIds),
                    'acceso_total' => false,        // forced per migration spec
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ],
                ['id'],
                ['nombre', 'hoja_ids', 'acceso_total', 'updated_at']
            );

            $bar->advance();
            $imported++;
        }

        $bar->finish();
        $this->newLine();

        DB::statement("SELECT setval('perfils_id_seq', (SELECT MAX(id) FROM perfils))");
        $this->line('  Postgres sequence reset.');

        $this->info("Done. Imported/updated: {$imported} perfils.");

        return self::SUCCESS;
    }
}
