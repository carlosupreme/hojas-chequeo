<?php

namespace Database\Factories;

use App\Models\Equipo;
use Illuminate\Database\Eloquent\Factories\Factory;

class EquipoFactory extends Factory
{
    protected $model = Equipo::class;

    public function definition(): array
    {
        return [
            'nombre'        => fake()->words(2, true),
            'tag'           => strtoupper(fake()->lexify('???-###')),
            'area'          => 'LAVADO EN AGUA',
            'foto'          => null,
            'numeroControl' => null,
            'revision'      => null,
        ];
    }
}
