<?php

namespace Database\Seeders;

use App\Models\Perfil;
use App\Models\Turno;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $turnoTintoreria = Turno::create([
            'nombre' => 'Tintoreria',
            'dias' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
            'hora_inicio' => '08:00:00',
            'hora_final' => '17:00:00',
        ]);

        $perfil = Perfil::create([
            'nombre' => 'Administrador',
            'hoja_ids' => ['*'],
        ]);

        $adminRole = Role::create(['name' => 'Administrador']);
        $operadorRole = Role::create(['name' => 'Operador']);
        $supervisorRole = Role::create(['name' => 'Supervisor']);

        $user = User::create([
            'name' => 'Administrador',
            'password' => bcrypt('password'),
            'email' => 'admin@admin.com',
            'perfil_id' => $perfil->id,
            'turno_id' => $turnoTintoreria->id,
        ]);

        $user->assignRole($adminRole);

        $operador = User::create([
            'name' => 'Operador',
            'password' => bcrypt('password'),
            'email' => 'operador@admin.com',
            'perfil_id' => $perfil->id,
            'turno_id' => $turnoTintoreria->id,
        ]);

        User::factory(20)->create([
            'perfil_id' => $perfil->id,
            'turno_id' => $turnoTintoreria->id,
        ])->each(function ($user) use ($operadorRole) {
            $user->assignRole($operadorRole);
        });

        $operador->assignRole($operadorRole);

        $supervisor = User::create([
            'name' => 'supervisor',
            'password' => bcrypt('password'),
            'email' => 'supervisor@admin.com',
            'perfil_id' => $perfil->id,
            'turno_id' => $turnoTintoreria->id,
        ]);

        $supervisor->assignRole($supervisorRole);
    }
}
