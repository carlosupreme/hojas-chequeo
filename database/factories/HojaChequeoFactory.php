<?php

namespace Database\Factories;

use App\Models\Equipo;
use App\Models\HojaChequeo;
use Illuminate\Database\Eloquent\Factories\Factory;

class HojaChequeoFactory extends Factory
{
    protected $model = HojaChequeo::class;

    public function definition(): array
    {
        return [
            'equipo_id' => Equipo::factory(),
            'observaciones' => null,
            'encendido' => true,
            'version' => 1,
        ];
    }

    public function apagada(): static
    {
        return $this->state(['encendido' => false]);
    }
}
