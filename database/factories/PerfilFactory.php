<?php

namespace Database\Factories;

use App\Models\Perfil;
use Illuminate\Database\Eloquent\Factories\Factory;

class PerfilFactory extends Factory
{
    protected $model = Perfil::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->words(2, true),
            'acceso_total' => false,
            'hoja_ids' => [],
        ];
    }

    /** Gives access to every hoja without listing IDs. */
    public function accesoTotal(): static
    {
        return $this->state(['acceso_total' => true, 'hoja_ids' => []]);
    }
}
