<?php

namespace Database\Seeders;

use App\Models\Equipo;
use App\Models\HojaChequeo;
use App\Models\Perfil;
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
    public function run(): void
    {
        $this->call(EquiposSeeder::class);

        Perfil::create([
            'name' => 'MANGLES',
            'hoja_ids' => [
                HojaChequeo::where('equipo_id', Equipo::where('tag', 'LV-MGL-01')->first()->id)->first()->id,
                HojaChequeo::where('equipo_id', Equipo::where('tag', 'LV-MGL-02')->first()->id)->first()->id,
                HojaChequeo::where('equipo_id', Equipo::where('tag', 'CM-CAL-02')->first()->id)->first()->id
            ]
        ]);

        $perfil = Perfil::create([
            'name' => 'ADMINISTRADOR',
            'hoja_ids' => HojaChequeo::pluck('id')->toArray()
        ]);

        $adminRole = Role::create(['name' => 'Administrador']);
        Role::create(['name' => 'Operador']);
        Role::create(['name' => 'Supervisor']);

        $user = User::factory()->create([
            'email' => 'admin@admin.com',
            'password' => \Hash::make('password'),
            'perfil_id' => $perfil->id
        ]);


        Simbologia::create([
            'icono' => 'heroicon-c-check',
            'nombre' => 'Exito',
            'descripcion' => 'Se realizó y está bien',
            'color' => '#27d623',
        ]);

        Simbologia::create([
            'icono' => 'heroicon-o-x-mark',
            'nombre' => 'Error',
            'descripcion' => 'Se realizó y está mal',
            'color' => '#cc3434',
        ]);

        $user->assignRole($adminRole);
        
    }
}
