<?php

namespace Database\Seeders;

use App\Models\Perfil;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        Perfil::create([
            'nombre' => 'Administrador',
            'hoja_ids' => [1, 2, 3],
        ]);

        $adminRole = Role::create(['name' => 'Administrador']);
        $operadorRole = Role::create(['name' => 'Operador']);
        $supervisorRole = Role::create(['name' => 'Supervisor']);

        $user = User::create([
            'name' => 'Administrador',
            'password' => bcrypt('password'),
            'email' => 'admin@admin.com',
            'perfil_id' => 1,
        ]);

        $user->assignRole($adminRole);

        $operador = User::create([
            'name' => 'Operador',
            'password' => bcrypt('password'),
            'email' => 'operador@admin.com',
            'perfil_id' => 1,
        ]);

        User::factory(20)->create()->each(function ($user) use ($operadorRole) {
            $user->assignRole($operadorRole);
        });

        $operador->assignRole($operadorRole);

        $supervisor = User::create([
            'name' => 'supervisor',
            'password' => bcrypt('password'),
            'email' => 'supervisor@admin.com',
            'perfil_id' => 1,
        ]);

        $supervisor->assignRole($supervisorRole);
    }
}
