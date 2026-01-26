<?php

namespace App\Filament\Pages;

use App\Models\LogRecorrido;
use App\Models\Turno;
use App\Models\ValorRecorrido;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;

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
        $this->dispatch('update-charts');
        $this->dispatch('dateRangeUpdated', [
            'inicio' => $this->dateRange['inicio'],
            'final' => $this->dateRange['final'],
        ]);
    }

    public function updatedDateRangeFinal()
    {
        $this->dispatch('update-charts');
        $this->dispatch('dateRangeUpdated', [
            'inicio' => $this->dateRange['inicio'],
            'final' => $this->dateRange['final'],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | SECTION 1: RECORRIDOS (Data Provider)
    |--------------------------------------------------------------------------
    */

    /**
     * Get equipment status counts by Turno
     * √ = Funcionando, X = Falla, PPP = Paro Producción, PPM = Paro Mantenimiento
     */
    public function getRecorridosKpisProperty()
    {
        $turnos = Turno::where('activo', true)->orderBy('id')->get();

        $stats = [
            'funcionando' => [],
            'falla' => [],
            'parados_ppm' => [],
            'parados_ppp' => [],
        ];

        foreach ($turnos as $turno) {
            // Get LogRecorrido IDs for this turno in date range
            $logIds = LogRecorrido::where('turno_id', $turno->id)
                ->whereBetween('fecha', [
                    Carbon::parse($this->dateRange['inicio'])->startOfDay(),
                    Carbon::parse($this->dateRange['final'])->endOfDay(),
                ])
                ->pluck('id');

            if ($logIds->isEmpty()) {
                $stats['funcionando'][$turno->nombre] = 0;
                $stats['falla'][$turno->nombre] = 0;
                $stats['parados_ppm'][$turno->nombre] = 0;
                $stats['parados_ppp'][$turno->nombre] = 0;

                continue;
            }

            // Count by estado
            $estadoCounts = ValorRecorrido::whereIn('log_recorrido_id', $logIds)
                ->whereNotNull('estado')
                ->select('estado', DB::raw('count(*) as total'))
                ->groupBy('estado')
                ->pluck('total', 'estado')
                ->toArray();

            $stats['funcionando'][$turno->nombre] = $estadoCounts['√'] ?? 0;
            $stats['falla'][$turno->nombre] = $estadoCounts['X'] ?? 0;
            $stats['parados_ppm'][$turno->nombre] = $estadoCounts['PPM'] ?? 0;
            $stats['parados_ppp'][$turno->nombre] = $estadoCounts['PPP'] ?? 0;
        }

        return $stats;
    }

    /**
     * Get total LogRecorrido count by Turno for chart
     */
    public function getRecorridosTotalByTurnoProperty()
    {
        $turnos = Turno::where('activo', true)->orderBy('id')->get();

        $labels = [];
        $data = [];

        foreach ($turnos as $turno) {
            $count = LogRecorrido::where('turno_id', $turno->id)
                ->whereBetween('fecha', [
                    Carbon::parse($this->dateRange['inicio'])->startOfDay(),
                    Carbon::parse($this->dateRange['final'])->endOfDay(),
                ])
                ->count();

            $labels[] = $turno->nombre;
            $data[] = $count;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Get equipment status stats for chart (by turno)
     */
    public function getRecorridosEstadoChartProperty()
    {
        $turnos = Turno::where('activo', true)->orderBy('id')->get();
        $labels = $turnos->pluck('nombre')->toArray();

        $funcionando = [];
        $falla = [];
        $ppm = [];
        $ppp = [];

        foreach ($turnos as $turno) {
            $logIds = LogRecorrido::where('turno_id', $turno->id)
                ->whereBetween('fecha', [
                    Carbon::parse($this->dateRange['inicio'])->startOfDay(),
                    Carbon::parse($this->dateRange['final'])->endOfDay(),
                ])
                ->pluck('id');

            if ($logIds->isEmpty()) {
                $funcionando[] = 0;
                $falla[] = 0;
                $ppm[] = 0;
                $ppp[] = 0;

                continue;
            }

            $estadoCounts = ValorRecorrido::whereIn('log_recorrido_id', $logIds)
                ->whereNotNull('estado')
                ->select('estado', DB::raw('count(*) as total'))
                ->groupBy('estado')
                ->pluck('total', 'estado')
                ->toArray();

            $funcionando[] = $estadoCounts['√'] ?? 0;
            $falla[] = $estadoCounts['X'] ?? 0;
            $ppm[] = $estadoCounts['PPM'] ?? 0;
            $ppp[] = $estadoCounts['PPP'] ?? 0;
        }

        return [
            'labels' => $labels,
            'series' => [
                ['name' => 'Funcionando (✓)', 'data' => $funcionando],
                ['name' => 'Falla (X)', 'data' => $falla],
                ['name' => 'P. Mantenimiento (PPM)', 'data' => $ppm],
                ['name' => 'P. Producción (PPP)', 'data' => $ppp],
            ],
        ];
    }
}
