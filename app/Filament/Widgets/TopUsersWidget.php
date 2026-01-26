<?php

namespace App\Filament\Widgets;

use App\Models\HojaEjecucion;
use App\Models\LogRecorrido;
use App\Models\Reporte;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class TopUsersWidget extends Widget
{
    use InteractsWithPageFilters;

    protected string $view = 'filament.widgets.top-users-widget';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public function getTopEjecuciones(): array
    {
        $startDate = $this->pageFilters['startDate'] ?? now()->startOfMonth();
        $endDate = $this->pageFilters['endDate'] ?? now();

        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        return HojaEjecucion::whereNotNull('finalizado_en')
            ->whereBetween('finalizado_en', [$startDate, $endDate])
            ->whereNotNull('nombre_operador')
            ->select('nombre_operador', DB::raw('count(*) as total'))
            ->groupBy('nombre_operador')
            ->orderByDesc('total')
            ->limit(3)
            ->get()
            ->toArray();
    }

    public function getTopReportes(): array
    {
        $startDate = $this->pageFilters['startDate'] ?? now()->startOfMonth();
        $endDate = $this->pageFilters['endDate'] ?? now();

        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        return Reporte::whereBetween('fecha', [$startDate, $endDate])
            ->whereNotNull('nombre')
            ->select('nombre', DB::raw('count(*) as total'))
            ->groupBy('nombre')
            ->orderByDesc('total')
            ->limit(3)
            ->get()
            ->toArray();
    }

    public function getTopRecorridos(): array
    {
        $startDate = $this->pageFilters['startDate'] ?? now()->startOfMonth();
        $endDate = $this->pageFilters['endDate'] ?? now();

        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        return LogRecorrido::with('operador')
            ->whereBetween('fecha', [$startDate, $endDate])
            ->whereNotNull('user_id')
            ->select('user_id', DB::raw('count(*) as total'))
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->limit(3)
            ->get()
            ->map(function ($item) {
                return [
                    'nombre' => $item->operador?->name ?? 'Sin nombre',
                    'total' => $item->total,
                ];
            })
            ->toArray();
    }
}
