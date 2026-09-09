<?php

namespace Database\Seeders;

use App\Models\CentroCosto;
use App\Models\Equipo;
use App\Models\Perfil;
use App\Models\Turno;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Centros de costo
        $centroCostoTintoreria = CentroCosto::firstOrCreate(['nombre' => 'Tintoreria']);
        $centroCostoLavanderia = CentroCosto::firstOrCreate(['nombre' => 'Lavanderia']);
        $centroCostoMantenimiento = CentroCosto::firstOrCreate(['nombre' => 'Mantenimiento']);

        // 2. Turnos
        $turnoTintoreria = Turno::firstOrCreate(
            ['nombre' => 'Tintoreria'],
            [
                'dias' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
                'hora_inicio' => '06:00:00',
                'hora_final' => '14:00:00',
                'centro_costo_id' => $centroCostoTintoreria->id,
            ]
        );

        $equiposTintoreria = Equipo::whereIn('id', [20, 21, 22, 24, 23, 25, 26, 27, 28, 29, 30, 31, 11, 10, 12, 13, 14, 15, 16, 17, 18, 19])->pluck('id');
        if ($equiposTintoreria->isNotEmpty()) {
            $turnoTintoreria->equipos()->syncWithoutDetaching($equiposTintoreria);
        }

        $turnoLavanderia = Turno::firstOrCreate(
            ['nombre' => 'Lavanderia'],
            [
                'dias' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
                'hora_inicio' => '14:00:00',
                'hora_final' => '22:00:00',
                'centro_costo_id' => $centroCostoLavanderia->id,
            ]
        );

        $equiposLavanderia = Equipo::whereIn('id', [20, 21, 22, 24, 23, 26, 27, 28, 29, 30, 31])->pluck('id');
        if ($equiposLavanderia->isNotEmpty()) {
            $turnoLavanderia->equipos()->syncWithoutDetaching($equiposLavanderia);
        }

        $turnoLavanderiaL2 = Turno::firstOrCreate(
            ['nombre' => 'Lavenderia 2'],
            [
                'dias' => ['monday', 'friday', 'saturday', 'tuesday', 'wednesday', 'sunday', 'thursday'],
                'hora_inicio' => '22:00:00',
                'hora_final' => '06:00:00',
                'activo' => true,
                'centro_costo_id' => $centroCostoLavanderia->id,
            ]
        );

        $equiposLavanderiaL2 = Equipo::whereIn('id', [21, 20, 22, 24, 23, 26, 31, 30, 28, 29, 27, 33, 34, 12, 13, 14, 15, 16, 17, 18, 19, 3])->pluck('id');
        if ($equiposLavanderiaL2->isNotEmpty()) {
            $turnoLavanderiaL2->equipos()->syncWithoutDetaching($equiposLavanderiaL2);
        }

        $turnoMantenimiento = Turno::firstOrCreate(
            ['nombre' => 'Mantenimiento'],
            [
                'dias' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
                'hora_inicio' => '08:00:00',
                'hora_final' => '17:00:00',
                'centro_costo_id' => $centroCostoMantenimiento->id,
            ]
        );

        $equiposMantenimiento = Equipo::whereIn('id', [2, 4, 5, 6, 7, 8, 9])->pluck('id');
        if ($equiposMantenimiento->isNotEmpty()) {
            $turnoMantenimiento->equipos()->syncWithoutDetaching($equiposMantenimiento);
        }

        // 3. Perfiles
        $perfilAdmin = Perfil::firstOrCreate(
            ['nombre' => 'Administrador'],
            [
                'hoja_ids' => [],
                'acceso_total' => true,
            ]
        );

        $perfilSupervisor = Perfil::firstOrCreate(
            ['nombre' => 'Supervisor'],
            [
                'hoja_ids' => [],
                'acceso_total' => true,
            ]
        );

        $perfilOperador = Perfil::firstOrCreate(
            ['nombre' => 'Operador'],
            [
                'hoja_ids' => [],
                'acceso_total' => true,
            ]
        );

        // 4. Roles y Permisos (Spatie)
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $adminRole = Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'web']);
        $operadorRole = Role::firstOrCreate(['name' => 'Operador', 'guard_name' => 'web']);
        $supervisorRole = Role::firstOrCreate(['name' => 'Supervisor', 'guard_name' => 'web']);

        $canEditDatePermission = Permission::firstOrCreate([
            'name' => User::$canEditDatesPermission,
            'guard_name' => 'web',
        ]);

        // 5. Usuario Administrador (root)
        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
                'perfil_id' => $perfilAdmin->id,
                'turno_id' => $turnoMantenimiento->id,
            ]
        );

        if (! $admin->hasRole($adminRole)) {
            $admin->assignRole($adminRole);
        }
        if (! $admin->hasPermissionTo($canEditDatePermission)) {
            $admin->givePermissionTo($canEditDatePermission);
        }

        // 6. Usuario Operador Demo
        $operador = User::firstOrCreate(
            ['email' => 'operador@admin.com'],
            [
                'name' => 'Operador Demo',
                'password' => Hash::make('password'),
                'perfil_id' => $perfilOperador->id,
                'turno_id' => $turnoTintoreria->id,
            ]
        );

        if (! $operador->hasRole($operadorRole)) {
            $operador->assignRole($operadorRole);
        }

        // 7. Usuario Supervisor Demo
        $supervisor = User::firstOrCreate(
            ['email' => 'supervisor@admin.com'],
            [
                'name' => 'Supervisor Demo',
                'password' => Hash::make('password'),
                'perfil_id' => $perfilSupervisor->id,
                'turno_id' => $turnoTintoreria->id,
            ]
        );

        if (! $supervisor->hasRole($supervisorRole)) {
            $supervisor->assignRole($supervisorRole);
        }
    }
}
