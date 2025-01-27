<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void {
        $adminRole = Role::create(['name' => 'Administrador']);
        Role::create(['name' => 'Operador']);
        $user = User::factory()->create([
            'email'    => 'admin@admin.com',
            'password' => \Hash::make('password')
        ]);

        $user->assignRole($adminRole);

        $this->call(EquiposSeeder::class);
    }
}
