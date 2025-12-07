<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void {
        $adminRole = Role::create(['name' => 'Administrador']);
        $operadorRole = Role::create(['name' => 'Operador']);
        $supervisorRole = Role::create(['name' => 'Supervisor']);

        $user = User::create([
            'name'     => 'Administrador',
            'password' => bcrypt('password'),
            'email'    => 'admin@admin.com',
        ]);

        $user->assignRole($adminRole);

        $operador = User::create([
            'name'     => 'Operador',
            'password' => bcrypt('password'),
            'email'    => 'operador@admin.com',
        ]);

        $operador->assignRole($operadorRole);


        $supervisor = User::create([
            'name'     => 'supervisor',
            'password' => bcrypt('password'),
            'email'    => 'supervisor@admin.com',
        ]);

        $supervisor->assignRole($supervisorRole);
    }
}
