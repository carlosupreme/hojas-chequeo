<?php

namespace Database\Seeders;

use App\Models\Simbologia;
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


        Simbologia::create([
            'icono'      => 'heroicon-c-check',
            'nombre'      => 'Exito',
            'descripcion' => 'Se realizó y está bien',
            'color'       => '#27d623',
        ]);

        Simbologia::create([
            'icono'       => 'heroicon-o-x-mark',
            'nombre'      => 'Error',
            'descripcion' => 'Se realizó y está mal',
            'color'       => '#cc3434',
        ]);

        $user->assignRole($adminRole);

        $this->call(EquiposSeeder::class);
    }
}
