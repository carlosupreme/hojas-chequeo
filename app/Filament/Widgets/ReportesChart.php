<?php

namespace App\Filament\Widgets;

use App\Models\Reporte;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class ReportesChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function getHeading(): string
    {
        return 'Reportes por Semana';
    }

    protected function getMaxHeight(): ?string
    {
        return '250px';
    }

    protected function getData(): array
    {
        $startDate = $this->pageFilters['startDate'] ?? now()->startOfMonth();
        $endDate = $this->pageFilters['endDate'] ?? now();

        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        $labels = [];
        $pendientes = [];
        $realizados = [];

        $currentDate = $startDate->copy()->startOfWeek();
        $weekCount = 0;

        while ($currentDate->lte($endDate) && $weekCount < 12) {
            $weekEnd = $currentDate->copy()->endOfWeek();
            if ($weekEnd->gt($endDate)) {
                $weekEnd = $endDate->copy();
            }

            $weekLabel = $currentDate->isoFormat('D MMM').' - '.$weekEnd->isoFormat('D MMM');

            $pendientesCount = Reporte::whereBetween('fecha', [$currentDate, $weekEnd])
                ->where('estado', 'pendiente')
                ->count();

            $realizadosCount = Reporte::whereBetween('fecha', [$currentDate, $weekEnd])
                ->where('estado', 'realizada')
                ->count();

            $labels[] = $weekLabel;
            $pendientes[] = $pendientesCount;
            $realizados[] = $realizadosCount;

            $currentDate->addWeek();
            $weekCount++;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pendientes',
                    'data' => $pendientes,
                    'backgroundColor' => '#ef4444',
                    'borderColor' => '#ef4444',
                ],
                [
                    'label' => 'Realizados',
                    'data' => $realizados,
                    'backgroundColor' => '#10b981',
                    'borderColor' => '#10b981',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
