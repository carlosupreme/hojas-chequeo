<?php

namespace App\Console\Commands;

use App\Models\CentroCosto;
use App\Models\Perfil;
use App\Models\Turno;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class CreateAdminUser extends Command
{
    protected $signature = 'make:admin
        {--name= : Name for the admin user}
        {--email= : Email address for the admin user}
        {--password= : Password for the admin user}
        {--reset : Force reset password if the user already exists}';

    protected $description = 'Create or configure an administrator (root) user with all required roles and permissions';

    protected $aliases = ['app:create-admin', 'admin:create'];

    public function handle(): int
    {
        $this->info('Configuring roles and permissions...');

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Ensure basic roles exist
        $adminRole = Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Supervisor', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Operador', 'guard_name' => 'web']);

        // 2. Ensure permissions exist
        $canEditDatePermission = Permission::firstOrCreate([
            'name' => User::$canEditDatesPermission,
            'guard_name' => 'web',
        ]);

        // 3. Ensure perfil with full access exists
        $perfil = Perfil::firstOrCreate(
            ['nombre' => 'Administrador'],
            ['hoja_ids' => [], 'acceso_total' => true]
        );

        // 4. Ensure at least one CentroCosto and Turno exist
        $centroCosto = CentroCosto::firstOrCreate(['nombre' => 'Mantenimiento']);
        $turno = Turno::firstOrCreate(
            ['nombre' => 'Mantenimiento'],
            [
                'dias' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
                'hora_inicio' => '08:00:00',
                'hora_final' => '17:00:00',
                'centro_costo_id' => $centroCosto->id,
            ]
        );

        // 5. Determine user credentials
        $name = $this->option('name');
        $email = $this->option('email');
        $password = $this->option('password');

        if (! $email) {
            $email = $this->input->isInteractive()
                ? $this->ask('Admin Email', 'admin@admin.com')
                : 'admin@admin.com';
        }

        if (! $name) {
            $name = $this->input->isInteractive()
                ? $this->ask('Admin Name', 'Administrador')
                : 'Administrador';
        }

        $user = User::where('email', $email)->first();

        if ($user && ! $password && ! $this->option('reset')) {
            $this->warn("User [{$email}] already exists.");
            if ($this->input->isInteractive()) {
                if ($this->confirm('Do you want to reset the password?', false)) {
                    $password = $this->secret('New Password');
                }
            }
        }

        if (! $password && ! $user) {
            $password = $this->input->isInteractive()
                ? ($this->secret('Password (leave empty for "password")') ?: 'password')
                : 'password';
        }

        // 6. Create or update the user
        if (! $user) {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password ?: 'password'),
                'perfil_id' => $perfil->id,
                'turno_id' => $turno->id,
            ]);
            $action = 'created';
        } else {
            $updates = [
                'name' => $name ?: $user->name,
                'perfil_id' => $user->perfil_id ?: $perfil->id,
                'turno_id' => $user->turno_id ?: $turno->id,
            ];
            if ($password) {
                $updates['password'] = Hash::make($password);
            }
            $user->update($updates);
            $action = 'updated';
        }

        // 7. Assign roles and permissions
        if (! $user->hasRole($adminRole)) {
            $user->assignRole($adminRole);
        }
        if (! $user->hasPermissionTo($canEditDatePermission)) {
            $user->givePermissionTo($canEditDatePermission);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->newLine();
        $this->info("✓ Administrator user [{$user->email}] {$action} successfully!");
        $this->table(
            ['Field', 'Value'],
            [
                ['Name', $user->name],
                ['Email', $user->email],
                ['Password', $password ? '(configured)' : '(unchanged)'],
                ['Role', $user->roles->pluck('name')->join(', ')],
                ['Perfil', $user->perfil?->nombre],
                ['Turno', $user->turno?->nombre],
                ['Admin Panel', url('/admin')],
            ]
        );

        return self::SUCCESS;
    }
}
