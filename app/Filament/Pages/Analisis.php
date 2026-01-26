<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class Analisis extends Page
{
    protected string $view = 'filament.pages.analisis';

    protected static ?string $title = 'Análisis de información';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    public $dateRange = [];

    public function mount()
    {
        $this->dateRange['inicio'] = Carbon::now()->startOfMonth();
        $this->dateRange['final'] = Carbon::now();
    }

    public function dateRangeForm(Schema $schema): Schema
    {
        return $schema->components([
            DatePicker::make('inicio')
                ->inlineLabel()
                ->displayFormat('D d/m/Y')
                ->native(false)
                ->locale('es')
                ->closeOnDateSelection()
                ->required()
                ->live()
                ->maxDate(now()),
            DatePicker::make('final')
                ->inlineLabel()
                ->displayFormat('D d/m/Y')
                ->native(false)
                ->locale('es')
                ->closeOnDateSelection()
                ->required()
                ->live()
                ->maxDate(now()),
        ])->statePath('dateRange');
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Administración';
    }

    protected static ?string $navigationLabel = 'Análisis de información';

    public string $activeTab = 'recorridos'; // Options: recorridos, mantenimiento, reportes

    // UI Helpers
    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function updatedDateRangeInicio()
    {
        // When period changes, charts will re-render because they depend on these computed properties
        $this->dispatch('update-charts');

        // Also notify reportes component about date changes
        $this->dispatch('dateRangeUpdated', [
            'inicio' => $this->dateRange['inicio'],
            'final' => $this->dateRange['final']
        ]);
    }

    public function updatedDateRangeFinal()
    {
        // When period changes, charts will re-render because they depend on these computed properties
        $this->dispatch('update-charts');

        // Also notify reportes component about date changes
        $this->dispatch('dateRangeUpdated', [
            'inicio' => $this->dateRange['inicio'],
            'final' => $this->dateRange['final']
        ]);
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
}
