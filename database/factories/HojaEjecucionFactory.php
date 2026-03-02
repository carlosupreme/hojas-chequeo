<?php

namespace Database\Factories;

use App\Models\HojaChequeo;
use App\Models\HojaEjecucion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class HojaEjecucionFactory extends Factory
{
    protected $model = HojaEjecucion::class;

    public function definition(): array
    {
        return [
            'hoja_chequeo_id' => HojaChequeo::factory(),
            'user_id' => User::factory(),
            'turno_id' => null,
            'centro_costo_id' => null,
            'nombre_operador' => fake()->name(),
            'firma_operador' => null,
            'observaciones' => null,
            'finalizado_en' => null,
        ];
    }

    public function finalizado(): static
    {
        return $this->state(['finalizado_en' => now()]);
    }
}
