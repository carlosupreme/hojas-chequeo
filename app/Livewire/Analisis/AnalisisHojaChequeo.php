<?php

namespace App\Livewire\Analisis;

use Livewire\Component;

class AnalisisHojaChequeo extends Component
{
    public function render()
    {
        return view('livewire.analisis.analisis-hoja-chequeo');
    }

    public function getRegistrosStatsProperty()
    {
        // TODO: QUERY -> Count total registers vs completed registers (100% filled)
        return [
            'realizados' => [
                'tintoreria' => 45,
                'lavanderia' => 30,
                'mantenimiento' => 10,
            ],
            'completos' => [
                'tintoreria' => 40, // e.g., 5 were incomplete
                'lavanderia' => 28,
                'mantenimiento' => 10,
            ],
        ];
    }

    public function getCalderasStatsProperty()
    {
        // TODO: QUERY -> Calculate Avg of numerical columns in HojaFilaRespuesta for Calderas
        // Logic: Check if value is outside 'min' and 'max' defined in your specs to set 'warning' => true

        return [
            'caldera_1' => [
                'kg_vapor' => ['val' => 4500, 'warning' => false],
                'temperatura' => ['val' => 180, 'warning' => true], // Too hot!
                'presion' => ['val' => 8.5, 'warning' => false],
                'tiempo_check' => ['val' => '25 min', 'warning' => false], // Avg time to check
            ],
            'caldera_2' => [
                'kg_vapor' => ['val' => 4200, 'warning' => false],
                'temperatura' => ['val' => 175, 'warning' => false],
                'presion' => ['val' => 8.2, 'warning' => false],
                'tiempo_check' => ['val' => '30 min', 'warning' => false],
            ],
        ];
    }

    public function getHorasTrabajoProperty()
    {
        // TODO: QUERY -> Sum 'Horas de uso' field from specific items
        return [
            'labels' => ['Caldera 1', 'Caldera 2', 'Tómbolas', 'Lavadoras', 'Mangles'],
            'data' => [120, 100, 350, 400, 200], // Total hours in period
        ];
    }
}
