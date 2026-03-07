<?php

namespace App\Livewire\Analisis;

use App\Models\CentroCosto;
use App\Models\Equipo;
use App\Models\RegistroCarga;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;

class AnalisisTombolas extends Component
{
    public $startDate;

    public $endDate;

    public ?int $equipoId = null;

    public ?int $centroCostoId = null;

    protected $listeners = ['dateRangeUpdated' => 'handleDateRangeUpdate'];

    public function mount($startDate = null, $endDate = null): void
    {
        $this->startDate = $startDate
            ? Carbon::parse($startDate)->format('Y-m-d')
            : now()->startOfMonth()->format('Y-m-d');

        $this->endDate = $endDate
            ? Carbon::parse($endDate)->format('Y-m-d')
            : now()->format('Y-m-d');
    }

    public function handleDateRangeUpdate(array $data): void
    {
        $this->startDate = Carbon::parse($data['inicio'])->format('Y-m-d');
        $this->endDate = Carbon::parse($data['final'])->format('Y-m-d');
    }

    public function clearFilters(): void
    {
        $this->equipoId = null;
        $this->centroCostoId = null;
    }

    // -------------------------------------------------------------------------
    // Filter options
    // -------------------------------------------------------------------------

    public function getTombolasProperty()
    {
        return Equipo::where('tag', 'like', '%-TOM-%')
            ->orderBy('tag')
            ->get(['id', 'nombre', 'tag']);
    }

    public function getCentrosCostoProperty()
    {
        return \App\Models\CentroCosto::orderBy('nombre')->get(['id', 'nombre']);
    }

    // -------------------------------------------------------------------------
    // Base query (shared, scoped by filters)
    // -------------------------------------------------------------------------

    private function baseQuery()
    {
        $q = RegistroCarga::query()
            ->whereBetween('registrado_en', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay(),
            ])
            ->whereHas('equipo', fn ($eq) => $eq->where('tag', 'like', '%-TOM-%'));

        if ($this->equipoId) {
            $q->where('equipo_id', $this->equipoId);
        }

        if ($this->centroCostoId) {
            $q->where('centro_costo_id', $this->centroCostoId);
        }

        return $q;
    }

    // -------------------------------------------------------------------------
    // KPI summary
    // -------------------------------------------------------------------------

    public function getKpisProperty(): array
    {
        $total = $this->baseQuery()->count();

        $days = max(1, Carbon::parse($this->startDate)->diffInDays(Carbon::parse($this->endDate)) + 1);

        $topEquipoRow = (clone $this->baseQuery())
            ->selectRaw('equipo_id, count(*) as total')
            ->groupBy('equipo_id')
            ->orderByDesc('total')
            ->first();

        $topEquipo = $topEquipoRow
            ? Equipo::find($topEquipoRow->equipo_id)?->tag
            : '—';

        $topCCRow = (clone $this->baseQuery())
            ->selectRaw('centro_costo_id, count(*) as total')
            ->groupBy('centro_costo_id')
            ->orderByDesc('total')
            ->first();

        $topCentroCosto = $topCCRow
            ? (CentroCosto::find($topCCRow->centro_costo_id)?->nombre ?? '—')
            : '—';

        return [
            'total' => $total,
            'avg_per_day' => $days > 0 ? round($total / $days, 1) : 0,
            'days' => $days,
            'top_equipo' => $topEquipo,
            'top_centro_costo' => $topCentroCosto,
        ];
    }

    // -------------------------------------------------------------------------
    // Breakdown by equipo
    // -------------------------------------------------------------------------

    public function getByEquipoProperty(): \Illuminate\Support\Collection
    {
        $days = max(1, Carbon::parse($this->startDate)->diffInDays(Carbon::parse($this->endDate)) + 1);

        $rows = (clone $this->baseQuery())
            ->selectRaw('equipo_id, count(*) as total')
            ->groupBy('equipo_id')
            ->orderByDesc('total')
            ->get();

        $grandTotal = $rows->sum('total');

        return $rows->map(function ($row) use ($days, $grandTotal) {
            $equipo = Equipo::find($row->equipo_id);

            return [
                'nombre' => $equipo?->nombre ?? '—',
                'tag' => $equipo?->tag ?? '—',
                'total' => $row->total,
                'avg' => round($row->total / $days, 1),
                'pct' => $grandTotal > 0 ? round(($row->total / $grandTotal) * 100) : 0,
            ];
        });
    }

    // -------------------------------------------------------------------------
    // Breakdown by centro de costo
    // -------------------------------------------------------------------------

    public function getByCentroCostoProperty(): \Illuminate\Support\Collection
    {
        $days = max(1, Carbon::parse($this->startDate)->diffInDays(Carbon::parse($this->endDate)) + 1);

        $rows = (clone $this->baseQuery())
            ->selectRaw('centro_costo_id, count(*) as total')
            ->groupBy('centro_costo_id')
            ->orderByDesc('total')
            ->get();

        $grandTotal = $rows->sum('total');

        return $rows->map(function ($row) use ($days, $grandTotal) {
            return [
                'nombre' => \App\Models\CentroCosto::find($row->centro_costo_id)?->nombre ?? 'Sin centro',
                'total' => $row->total,
                'avg' => round($row->total / $days, 1),
                'pct' => $grandTotal > 0 ? round(($row->total / $grandTotal) * 100) : 0,
            ];
        });
    }

    // -------------------------------------------------------------------------
    // Breakdown by operator
    // -------------------------------------------------------------------------

    public function getByUserProperty(): \Illuminate\Support\Collection
    {
        $rows = (clone $this->baseQuery())
            ->selectRaw('user_id, count(*) as total')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->get();

        $grandTotal = $rows->sum('total');

        return $rows->map(fn ($row) => [
            'nombre' => User::find($row->user_id)?->name ?? '—',
            'total' => $row->total,
            'pct' => $grandTotal > 0 ? round(($row->total / $grandTotal) * 100) : 0,
        ]);
    }

    // -------------------------------------------------------------------------
    // Daily trend (for sparkline bar chart)
    // -------------------------------------------------------------------------

    public function getDailyTrendProperty(): array
    {
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();

        $counts = (clone $this->baseQuery())
            ->selectRaw('DATE(registrado_en) as day, count(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day')
            ->toArray();

        $labels = [];
        $data = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $key = $cursor->format('Y-m-d');
            $labels[] = $cursor->isoFormat('D MMM');
            $data[] = $counts[$key] ?? 0;
            $cursor->addDay();
        }

        return ['labels' => $labels, 'data' => $data];
    }

    public function render()
    {
        return view('livewire.analisis.analisis-tombolas');
    }
}
