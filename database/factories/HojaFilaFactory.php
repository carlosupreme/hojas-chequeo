<?php

namespace Database\Factories;

use App\Models\AnswerType;
use App\Models\HojaChequeo;
use App\Models\HojaFila;
use Illuminate\Database\Eloquent\Factories\Factory;

class HojaFilaFactory extends Factory
{
    protected $model = HojaFila::class;

    public function definition(): array
    {
        return [
            'hoja_chequeo_id' => HojaChequeo::factory(),
            'answer_type_id' => AnswerType::factory(),
            'order' => 1,
            'categoria' => 'limpieza',
        ];
    }
}
