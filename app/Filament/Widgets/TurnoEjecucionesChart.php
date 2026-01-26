<?php

namespace App\Filament\Widgets;

use App\Models\HojaEjecucion;
use App\Models\HojaFilaRespuesta;
use App\Models\Turno;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class TurnoEjecucionesChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    public function getHeading(): string
    {
        return 'Chequeos y % Completado por Turno';
    }

    protected function getMaxHeight(): ?string
    {
        return '300px';
    }

    protected function getData(): array
    {
        $startDate = $this->pageFilters['startDate'] ?? now()->startOfMonth();
        $endDate = $this->pageFilters['endDate'] ?? now();

        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        $turnos = Turno::where('activo', true)->orderBy('id')->get();

        $labels = [];
        $ejecuciones = [];
        $porcentajes = [];

        foreach ($turnos as $turno) {
            $ejecucionQuery = HojaEjecucion::where('turno_id', $turno->id)
                ->whereNotNull('finalizado_en')
                ->whereBetween('finalizado_en', [$startDate, $endDate]);

            $totalEjecuciones = $ejecucionQuery->count();
            $ejecucionIds = $ejecucionQuery->pluck('id');

            // Calculate completion percentage (answer_option_id = 1 means completed/check)
            $totalResponses = HojaFilaRespuesta::whereIn('hoja_ejecucion_id', $ejecucionIds)
                ->whereNotNull('answer_option_id')
                ->count();

            $completedResponses = HojaFilaRespuesta::whereIn('hoja_ejecucion_id', $ejecucionIds)
                ->where('answer_option_id', 1)
                ->count();

            $percentage = $totalResponses > 0 ? round(($completedResponses / $totalResponses) * 100, 1) : 0;

            $labels[] = $turno->nombre;
            $ejecuciones[] = $totalEjecuciones;
            $porcentajes[] = $percentage;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Chequeos',
                    'data' => $ejecuciones,
                    'backgroundColor' => '#3b82f6',
                    'yAxisID' => 'y',
                ],
                [
                    'label' => '% Completado',
                    'data' => $porcentajes,
                    'backgroundColor' => '#10b981',
                    'type' => 'line',
                    'borderColor' => '#10b981',
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'title' => [
                        'display' => true,
                        'text' => 'Chequeos',
                    ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'max' => 100,
                    'title' => [
                        'display' => true,
                        'text' => '% Completado',
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
