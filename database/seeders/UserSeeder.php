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

        $turnoTintoreria = Turno::create([
            'nombre' => 'Tintoreria',
            'dias' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
            'hora_inicio' => '08:00:00',
            'hora_final' => '17:00:00',
            'centro_costo_id' => $centroCostoTintoreria->id,
        ]);

        $turnoLavanderia = Turno::create([
            'nombre' => 'Lavanderia',
            'dias' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
            'hora_inicio' => '00:00:00',
            'hora_final' => '23:59:59',
            'centro_costo_id' => $centroCostoLavanderia->id,
        ]);

        $turnos = [$turnoTintoreria->id, $turnoLavanderia->id];

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
                'turno_id' => $turnos[array_rand($turnos)],
            ]
        );

        $user->assignRole($adminRole);
        $user->givePermissionTo($canEditDatePermission->name);
    }
}
