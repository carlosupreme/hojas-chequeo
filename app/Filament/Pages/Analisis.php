<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class Analisis extends Page
{
    protected string $view = 'filament.pages.analisis';

    protected static ?string $title = 'Análisis de información';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = 'Análisis de información';

    public string $activeTab = 'recorridos'; // Options: recorridos, mantenimiento, reportes

    public string $period = 'monthly'; // Options: weekly, biweekly, monthly

    // UI Helpers
    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function updatedPeriod()
    {
        // When period changes, charts will re-render because they depend on these computed properties
        $this->dispatch('update-charts');
    }

    /*
    |--------------------------------------------------------------------------
    | SECTION 1: RECORRIDOS (Data Provider)
    |--------------------------------------------------------------------------
    */
    public function getRecorridosKpisProperty()
    {
        // TODO: QUERY -> Count equipments by status and area
        // Example: Equipo::where('area', 'Tintoreria')->where('status', 'active')->count();

        return [
            'funcionando' => [
                'tintoreria' => 12,
                'lavanderia' => 8,
            ],
            'parados_ppm' => [ // Programmed Maintenance
                'tintoreria' => 2,
                'lavanderia' => 1,
            ],
            'parados_ppp' => [ // Production Stoppage
                'tintoreria' => 0,
                'lavanderia' => 3,
            ],
        ];
    }

    public function getRecorridosHistoryProperty()
    {
        // TODO: QUERY -> Count completed HojaEjecucion grouped by week and area
        return [
            'labels' => ['Semana 1', 'Semana 2', 'Semana 3', 'Semana 4'],
            'series' => [
                [
                    'name' => 'Tintorería',
                    'data' => [15, 20, 18, 22],
                ],
                [
                    'name' => 'Lavandería',
                    'data' => [12, 15, 10, 14],
                ],
                [
                    'name' => 'Mantenimiento', // Generic or Engine Room
                    'data' => [5, 5, 5, 5],
                ],
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | SECTION 2: MANTENIMIENTO (Data Provider)
    |--------------------------------------------------------------------------
    */
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

    /*
    |--------------------------------------------------------------------------
    | SECTION 3: REPORTES (Data Provider)
    |--------------------------------------------------------------------------
    */
    public function getSolicitudesStatsProperty()
    {
        // TODO: QUERY -> Count maintenance requests (Solicitudes)
        return [
            'history_labels' => ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4'],
            'history_values' => [5, 8, 2, 10], // Created requests
            'status' => [
                'pendientes' => 12,
                'realizadas' => 45,
            ],
        ];
    }
}
