<?php

namespace App\Filament\Widgets;

use App\Models\Equipo;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class EquipmentAlertsChart extends ChartWidget
{
    protected static ?string   $heading    = 'Top 5 Equipos con Más Alertas';
    protected static ?string   $maxHeight  = '400px';

    protected function getData(): array {
        $activeFilter = $this->filter;
        $dateRange = $this->getDateRange($activeFilter);

        $query = Equipo::query()
                       ->select([
                           'equipos.tag',
                           DB::raw('COALESCE(SUM(alertas.contador), 0) as total_alertas')
                       ])
                       ->leftJoin('hoja_chequeos', 'equipos.id', '=', 'hoja_chequeos.equipo_id')
                       ->leftJoin('items', 'hoja_chequeos.id', '=', 'items.hoja_chequeo_id')
                       ->leftJoin('alertas', function ($join) use ($dateRange) {
                           $join->on('items.id', '=', 'alertas.item_id');
                           if ($dateRange) {
                               $join->whereBetween('alertas.updated_at', [
                                   $dateRange['start']->startOfDay(),
                                   $dateRange['end']->endOfDay()
                               ]);
                           }
                       })
                       ->groupBy('equipos.tag')
                       ->orderByDesc('total_alertas')
                       ->limit(5);

        $data = $query->get();

        return [
            'datasets' => [
                [
                    'label'           => 'Alertas',
                    'data'            => $data->pluck('total_alertas')->map(fn($v) => (int)$v)->toArray(),
                    'backgroundColor' => ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
                    'borderWidth'     => 0,
                ],
            ],
            'labels'   => $data->pluck('tag')->toArray(),
        ];
    }

    protected function getFilters(): array {
        return [
            null         => 'Todo el histórico',
            'last_week'  => 'Última semana',
            'last_month' => 'Último mes',
            'last_year'  => 'Último año',
        ];
    }

    private function getDateRange(?string $filter): ?array {
        return match ($filter) {
            'last_week'  => [
                'start' => Carbon::now()->subWeek(),
                'end'   => Carbon::now(),
            ],
            'last_month' => [
                'start' => Carbon::now()->subMonth(),
                'end'   => Carbon::now(),
            ],
            'last_year'  => [
                'start' => Carbon::now()->subYear(),
                'end'   => Carbon::now(),
            ],
            default      => null,
        };
    }

    protected function getOptions(): array {
        return [
            'responsive' => true,
            'scales'     => [
                'y' => [
                    'title' => [
                        'display' => true,
                        'text'    => 'Cantidad de Alertas',
                        'color'   => '#6b7280',
                    ],
                    'ticks' => [
                        'color' => '#6b7280',
                    ],
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text'    => 'Equipos',
                        'color'   => '#6b7280',
                    ],
                    'ticks' => [
                        'color' => '#6b7280',
                    ],
                ],
            ],
            'plugins'    => [
                'legend'  => [
                    'display' => false,
                ],
                'tooltip' => [
                    'backgroundColor' => '#1f2937',
                    'titleColor'      => '#f3f4f6',
                    'bodyColor'       => '#f3f4f6',
                ],
            ],
        ];
    }

    protected function getType(): string {
        return "bar";
    }
}
