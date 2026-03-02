<?php

namespace Database\Factories;

use App\Models\HojaChequeo;
use App\Models\HojaColumna;
use Illuminate\Database\Eloquent\Factories\Factory;

class HojaColumnaFactory extends Factory
{
    protected $model = HojaColumna::class;

    public function definition(): array
    {
        return [
            'hoja_chequeo_id' => HojaChequeo::factory(),
            'key' => 'descripcion',
            'label' => 'Descripción',
            'is_fixed' => true,
            'order' => 1,
        ];
    }
}
