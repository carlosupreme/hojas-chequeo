<?php

namespace Database\Seeders;

use App\Models\AnswerType;
use Illuminate\Database\Seeder;

class AnswerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AnswerType::create([
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
    }
}
