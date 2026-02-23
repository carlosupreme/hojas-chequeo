<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportUsers extends Command
{
    protected $signature = 'import:users
        {--host=127.0.0.1 : MySQL v1 host}
        {--port=3306 : MySQL v1 port}
        {--database= : MySQL v1 database name (required)}
        {--username=root : MySQL v1 username}
        {--password= : MySQL v1 password}
        {--dry-run : Preview records without saving}';

    protected $description = 'Import users from v1 MySQL into v2 PostgreSQL. Run import:perfils first.';

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
            $rows = DB::connection('mysql_v1')->table('users')->get();
        } catch (\Exception $e) {
            $this->error('Connection failed: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info("Found {$rows->count()} users in v1.");

        if ($this->option('dry-run')) {
            $this->table(
                ['v1 id', 'name', 'email', 'perfil_id'],
                $rows->map(fn ($r) => [$r->id, $r->name, $r->email, $r->perfil_id])
            );

            return self::SUCCESS;
        }

        // Validate all referenced perfil_ids exist in v2 before starting
        $perfilIds = DB::table('perfils')->pluck('id');
        $missing = collect($rows)->pluck('perfil_id')->unique()->diff($perfilIds);

        if ($missing->isNotEmpty()) {
            $this->error("These perfil_ids from v1 don't exist in v2: {$missing->join(', ')}");
            $this->error('Run import:perfils first.');

            return self::FAILURE;
        }

        $bar = $this->output->createProgressBar($rows->count());
        $bar->start();

        $imported = 0;

        foreach ($rows as $row) {
            // Use DB::table() to avoid Eloquent re-hashing the already-hashed password
            DB::table('users')->upsert(
                [
                    'id' => $row->id,
                    'name' => $row->name,
                    'email' => $row->email,
                    'email_verified_at' => $row->email_verified_at,
                    'password' => $row->password,   // already bcrypt — don't re-hash
                    'remember_token' => $row->remember_token,
                    'perfil_id' => $row->perfil_id,
                    'turno_id' => 1,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ],
                ['id'],
                ['name', 'email', 'email_verified_at', 'password', 'remember_token', 'perfil_id', 'turno_id', 'updated_at']
            );

            $bar->advance();
            $imported++;
        }

        $bar->finish();
        $this->newLine();

        DB::statement("SELECT setval('users_id_seq', (SELECT MAX(id) FROM users))");
        $this->line('  Postgres sequence reset.');

        $this->info("Done. Imported/updated: {$imported} users.");

        // ── Roles ────────────────────────────────────────────────────────────
        $this->info('Importing model_has_roles...');

        $roleRows = DB::connection('mysql_v1')
            ->table('model_has_roles')
            ->where('model_type', 'App\\Models\\User')
            ->get();

        $this->info("Found {$roleRows->count()} role assignments in v1.");

        $rolesImported = 0;

        foreach ($roleRows as $roleRow) {
            DB::table('model_has_roles')->insertOrIgnore([
                'role_id' => $roleRow->role_id,
                'model_type' => $roleRow->model_type,
                'model_id' => $roleRow->model_id,
            ]);
            $rolesImported++;
        }

        $this->info("Done. Imported: {$rolesImported} role assignments.");
        $this->warn('Remember: all users were assigned turno_id = 1 — adjust per user in the admin panel if needed.');

        return self::SUCCESS;
    }
}
