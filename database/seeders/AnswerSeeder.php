<?php

namespace Database\Seeders;

use App\Models\AnswerOption;
use App\Models\AnswerType;
use Illuminate\Database\Seeder;

class AnswerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $iconType = AnswerType::create([
            'key' => 'icon_set',
            'label' => 'Estado visual',
            'behavior' => 'enum',
            'aggregable' => false,
        ]);

        AnswerType::create([
            'key' => 'number',
            'label' => 'Numérico',
            'behavior' => 'numeric',
            'aggregable' => true,
        ]);

        AnswerType::create([
            'key' => 'text',
            'label' => 'Texto',
            'behavior' => 'text',
            'aggregable' => false,
        ]);

        AnswerType::create([
            'key' => 'boolean',
            'label' => 'Si/No',
            'behavior' => 'boolean',
            'aggregable' => false,
        ]);

        $realizado = AnswerOption::create([
            'answer_type_id' => $iconType->id,
            'key' => 'realizado',
            'label' => 'REALIZADO Y ESTA BIEN',
            'icon' => 'heroicon-o-check',
            'color' => 'green',
        ]);

        $realizadoMal = AnswerOption::create([
            'answer_type_id' => $iconType->id,
            'key' => 'realizado_mal',
            'label' => 'REALIZADO Y ESTA MAL',
            'icon' => 'heroicon-o-x-mark',
            'color' => 'red',
        ]);

        $noRealizado = AnswerOption::create([
            'answer_type_id' => $iconType->id,
            'key' => 'no_realizado',
            'label' => 'NO REALIZADO',
            'icon' => 'heroicon-o-no-symbol',
            'color' => 'yellow',
        ]);

        $noAplica = AnswerOption::create([
            'answer_type_id' => $iconType->id,
            'key' => 'no_aplica',
            'label' => 'NO APLICA',
            'icon' => 'heroicon-o-minus-circle',
            'color' => 'gray',
        ]);

    }
}
