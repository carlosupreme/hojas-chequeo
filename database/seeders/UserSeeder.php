<?php

namespace Database\Seeders;

use App\Models\CentroCosto;
use App\Models\Perfil;
use App\Models\Turno;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $centroCostoTintoreria = CentroCosto::create(['nombre' => 'Tintoreria']);
        $centroCostoLavanderia = CentroCosto::create(['nombre' => 'Lavanderia']);
        $centroCostoMantenimiento = CentroCosto::create(['nombre' => 'Mantenimiento']);

        $turnoTintoreria = Turno::create([
            'nombre' => 'Tintoreria',
            'dias' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
            'hora_inicio' => '06:00:00',
            'hora_final' => '14:00:00',
            'centro_costo_id' => $centroCostoTintoreria->id,
        ]);

        $turnoTintoreria->equipos()->sync([20, 21, 22, 24, 23, 25, 26, 27, 28, 29, 30, 31, 11, 10, 12, 13, 14, 15, 16, 17, 18, 19]);

        $turnoLavanderia = Turno::create([
            'nombre' => 'Lavanderia',
            'dias' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
            'hora_inicio' => '14:00:00',
            'hora_final' => '22:00:00',
            'centro_costo_id' => $centroCostoLavanderia->id,
        ]);

        $turnoLavanderia->equipos()->sync([20, 21, 22, 24, 23, 26, 27, 28, 29, 30, 31]);

        $turnoLavanderiaL2 = Turno::create([
            'centro_costo_id' => 1,
            'nombre' => 'Lavenderia 2',
            'dias' => ['monday', 'friday', 'saturday', 'tuesday', 'wednesday', 'sunday', 'thursday'],
            'hora_inicio' => '22:00:00',
            'hora_final' => '06:00:00',
            'activo' => true,
        ]);

        $turnoLavanderiaL2->equipos()->sync([21, 20, 22, 24, 23, 26, 31, 30, 28, 29, 27, 33, 34, 12, 13, 14, 15, 16, 17, 18, 19, 3]);

        $turnoMantenimiento = Turno::create([
            'nombre' => 'Mantenimiento',
            'dias' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
            'hora_inicio' => '08:00:00',
            'hora_final' => '17:00:00',
            'centro_costo_id' => $centroCostoMantenimiento->id,
        ]);

        $turnoMantenimiento->equipos()->sync([2, 4, 5, 6, 7, 8, 9]);

        $perfil = Perfil::firstOrCreate(
            [
                'nombre' => 'Administrador',
            ],
            [
                'hoja_ids' => [],
                'acceso_total' => true,
            ]
        );

        $adminRole = Role::create(['name' => 'Administrador']);
        Role::create(['name' => 'Operador']);
        Role::create(['name' => 'Supervisor']);

        $canEditDatePermission = Permission::create(['name' => User::$canEditDatesPermission]);

        $user = User::firstOrCreate(
            [
                'email' => 'admin@admin.com',
            ],
            [
                'name' => 'Administrador',
                'password' => bcrypt('password'),
                'perfil_id' => $perfil->id,
                'turno_id' => $turnoMantenimiento->id,
            ]
        );

        $user->assignRole($adminRole);
        $user->givePermissionTo($canEditDatePermission->name);
    }
}
