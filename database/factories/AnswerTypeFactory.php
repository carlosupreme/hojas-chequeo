<?php

namespace Database\Factories;

use App\Models\AnswerType;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnswerTypeFactory extends Factory
{
    protected $model = AnswerType::class;

    public function definition(): array
    {
        return [
            'key'        => 'number',
            'label'      => 'Número',
            'behavior'   => 'numeric',
            'aggregable' => true,
        ];
    }

    public function number(): static  { return $this->state(['key' => 'number',   'label' => 'Número']); }
    public function text(): static    { return $this->state(['key' => 'text',     'label' => 'Texto']); }
    public function boolean(): static { return $this->state(['key' => 'boolean',  'label' => 'Sí/No']); }
    public function iconSet(): static { return $this->state(['key' => 'icon_set', 'label' => 'Iconos']); }
}
