<?php

namespace App\Filament\Widgets;

use App\Models\HojaEjecucion;
use App\Models\LogRecorrido;
use App\Models\Reporte;
use App\Models\ValorRecorrido;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $startDate = $this->pageFilters['startDate'] ?? now()->startOfMonth();
        $endDate = $this->pageFilters['endDate'] ?? now();

        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        // Total Recorridos
        $totalRecorridos = LogRecorrido::whereBetween('fecha', [$startDate, $endDate])->count();

        // Total Ejecuciones de Hojas de Chequeo
        $totalEjecuciones = HojaEjecucion::whereNotNull('finalizado_en')
            ->whereBetween('finalizado_en', [$startDate, $endDate])
            ->count();

        // Total Reportes
        $totalReportes = Reporte::whereBetween('fecha', [$startDate, $endDate])->count();
        $reportesPendientes = Reporte::whereBetween('fecha', [$startDate, $endDate])
            ->where('estado', 'pendiente')
            ->count();

        // Equipos Funcionando (from ValorRecorrido)
        $logIds = LogRecorrido::whereBetween('fecha', [$startDate, $endDate])->pluck('id');
        $funcionando = ValorRecorrido::whereIn('log_recorrido_id', $logIds)
            ->where('estado', '√')
            ->count();
        $conFalla = ValorRecorrido::whereIn('log_recorrido_id', $logIds)
            ->where('estado', 'X')
            ->count();

        // Weekly trend for recorridos
        $weeklyRecorridos = LogRecorrido::whereBetween('fecha', [now()->subWeeks(8), now()])
            ->selectRaw('EXTRACT(WEEK FROM fecha) as week, COUNT(*) as count')
            ->groupBy('week')
            ->orderBy('week')
            ->pluck('count')
            ->toArray();

        return [
            Stat::make('Total Recorridos', $totalRecorridos)
                ->description('En el período seleccionado')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('primary')
                ->chart($weeklyRecorridos ?: [0]),

            Stat::make('Ejecuciones Chequeo', $totalEjecuciones)
                ->description('Hojas de chequeo completadas')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color('success'),

            Stat::make('Equipos Funcionando', $funcionando)
                ->description($conFalla.' con fallas reportadas')
                ->descriptionIcon($conFalla > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($conFalla > 0 ? 'warning' : 'success'),

            Stat::make('Reportes', $totalReportes)
                ->description($reportesPendientes.' pendientes')
                ->descriptionIcon($reportesPendientes > 0 ? 'heroicon-m-clock' : 'heroicon-m-check-circle')
                ->color($reportesPendientes > 0 ? 'danger' : 'success'),
        ];
    }
}
