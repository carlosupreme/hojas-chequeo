<?php

namespace Database\Seeders;

use App\Models\User;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void {
        $adminRole = Role::create(['name' => 'Administrador']);
        $operadorRole = Role::create(['name' => 'Operador']);

        $user = User::factory()->create([
            'email'    => 'admin@admin.com',
            'password' => \Hash::make('password')
        ]);

        $user->assignRole($adminRole);
    }
}
