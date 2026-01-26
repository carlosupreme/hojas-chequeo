<?php

namespace App\Filament\Widgets;

use App\Models\LogRecorrido;
use App\Models\Turno;
use App\Models\ValorRecorrido;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class RecorridosEstadoChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    public function getHeading(): string
    {
        return 'Estado de Equipos por Turno';
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
        $funcionando = [];
        $falla = [];
        $ppm = [];
        $ppp = [];

        foreach ($turnos as $turno) {
            $logIds = LogRecorrido::where('turno_id', $turno->id)
                ->whereBetween('fecha', [$startDate, $endDate])
                ->pluck('id');

            if ($logIds->isEmpty()) {
                $labels[] = $turno->nombre;
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

            $labels[] = $turno->nombre;
            $funcionando[] = $estadoCounts['√'] ?? 0;
            $falla[] = $estadoCounts['X'] ?? 0;
            $ppm[] = $estadoCounts['PPM'] ?? 0;
            $ppp[] = $estadoCounts['PPP'] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Funcionando (✓)',
                    'data' => $funcionando,
                    'backgroundColor' => '#10b981',
                ],
                [
                    'label' => 'Falla (X)',
                    'data' => $falla,
                    'backgroundColor' => '#ef4444',
                ],
                [
                    'label' => 'P. Mantenimiento',
                    'data' => $ppm,
                    'backgroundColor' => '#3b82f6',
                ],
                [
                    'label' => 'P. Producción',
                    'data' => $ppp,
                    'backgroundColor' => '#f59e0b',
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
                'x' => [
                    'stacked' => true,
                ],
                'y' => [
                    'stacked' => true,
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
