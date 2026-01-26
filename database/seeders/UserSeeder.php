<?php

namespace Database\Seeders;

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
        $turnoTintoreria = Turno::create([
            'nombre' => 'Tintoreria',
            'dias' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
            'hora_inicio' => '08:00:00',
            'hora_final' => '17:00:00',
        ]);

        $turnoLavanderia = Turno::create([
            'nombre' => 'Lavanderia',
            'dias' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
            'hora_inicio' => '00:00:00',
            'hora_final' => '23:59:59',
        ]);

        $turnos = [$turnoTintoreria->id, $turnoLavanderia->id];

        $perfil = Perfil::create([
            'nombre' => 'Administrador',
            'hoja_ids' => [],
            'acceso_total' => true,
        ]);

        $adminRole = Role::create(['name' => 'Administrador']);
        $operadorRole = Role::create(['name' => 'Operador']);
        $supervisorRole = Role::create(['name' => 'Supervisor']);

        $canEditDatePermission = Permission::create(['name' => 'chequeos.edit.date']);

        $user = User::create([
            'name' => 'Administrador',
            'password' => bcrypt('password'),
            'email' => 'admin@admin.com',
            'perfil_id' => $perfil->id,
            'turno_id' => $turnos[array_rand($turnos)],
        ]);

        $user->assignRole($adminRole);
        $user->givePermissionTo($canEditDatePermission->name);

        $operador = User::create([
            'name' => 'Operador',
            'password' => bcrypt('password'),
            'email' => 'operador@admin.com',
            'perfil_id' => $perfil->id,
            'turno_id' => $turnos[array_rand($turnos)],
        ]);

        foreach (range(1, 20) as $i) {
            $user = User::factory()->create([
                'perfil_id' => $perfil->id,
                'turno_id' => $turnos[array_rand($turnos)],
            ]);
            $user->assignRole($operadorRole);
        }

        $operador->assignRole($operadorRole);

        $supervisor = User::create([
            'name' => 'supervisor',
            'password' => bcrypt('password'),
            'email' => 'supervisor@admin.com',
            'perfil_id' => $perfil->id,
            'turno_id' => $turnos[array_rand($turnos)],
        ]);

        $supervisor->assignRole($supervisorRole);
    }
}
