<?php

namespace App\Livewire\Analisis;

use App\Models\CentroCosto;
use App\Models\Equipo;
use App\Models\HojaChequeo;
use App\Models\HojaEjecucion;
use App\Models\HojaFila;
use App\Models\HojaFilaRespuesta;
use App\Models\RegistroCarga;
use App\Models\Turno;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
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
        return Equipo::tombolas()
            ->orderBy('tag')
            ->get(['id', 'nombre', 'tag']);
    }

    public function getCentrosCostoProperty()
    {
        return CentroCosto::orderBy('nombre')->get(['id', 'nombre']);
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
            ->whereHas('equipo', fn ($eq) => $eq->tombolas());

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

    public function getByEquipoProperty(): Collection
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

    public function getByCentroCostoProperty(): Collection
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
                'nombre' => CentroCosto::find($row->centro_costo_id)?->nombre ?? 'Sin centro',
                'total' => $row->total,
                'avg' => round($row->total / $days, 1),
                'pct' => $grandTotal > 0 ? round(($row->total / $grandTotal) * 100) : 0,
            ];
        });
    }

    // -------------------------------------------------------------------------
    // Breakdown by operator
    // -------------------------------------------------------------------------

    public function getByUserProperty(): Collection
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

    // -------------------------------------------------------------------------
    // Horas de trabajo (via HojaFilaRespuesta "HORAS AL FINAL DEL TURNO")
    // -------------------------------------------------------------------------

    /**
     * Average hours worked per tombola equipo over the selected range.
     * Mirrors getCalderasStatsProperty pattern from AnalisisHojaChequeo.
     */
    public function getHorasPorEquipoProperty(): array
    {
        $startDate = Carbon::parse($this->startDate)->startOfDay();
        $endDate = Carbon::parse($this->endDate)->endOfDay();

        $equipos = Equipo::tombolas()->orderBy('tag')->get();
        $stats = [];

        foreach ($equipos as $equipo) {
            $hojaChequeo = HojaChequeo::where('equipo_id', $equipo->id)->latest()->first();

            $totalHoras = 0;
            $avgHoras = 0;
            $count = 0;

            if ($hojaChequeo) {
                $ejecucionIds = HojaEjecucion::where('hoja_chequeo_id', $hojaChequeo->id)
                    ->whereNotNull('finalizado_en')
                    ->whereBetween('finalizado_en', [$startDate, $endDate])
                    ->pluck('id');

                if ($ejecucionIds->isNotEmpty()) {
                    $filaId = HojaFila::where('hoja_chequeo_id', $hojaChequeo->id)
                        ->whereRelation('valores', 'valor', 'like', '%HORAS%')
                        ->value('id');

                    if ($filaId) {
                        $valores = HojaFilaRespuesta::whereIn('hoja_ejecucion_id', $ejecucionIds)
                            ->where('hoja_fila_id', $filaId)
                            ->whereNotNull('numeric_value')
                            ->pluck('numeric_value');

                        $count = $valores->count();
                        $totalHoras = round($valores->sum(), 1);
                        $avgHoras = $count > 0 ? round($totalHoras / $count, 1) : 0;
                    }
                }
            }

            $stats[] = [
                'tag' => $equipo->tag,
                'nombre' => $equipo->nombre,
                'total_horas' => $totalHoras,
                'avg_horas' => $avgHoras,
                'sesiones' => $count,
            ];
        }

        usort($stats, fn ($a, $b) => $a['avg_horas'] <=> $b['avg_horas']);

        return $stats;
    }

    /**
     * Daily average hours per turno, for the month-view table.
     * Columns = active turnos; rows = each day in range.
     */
    public function getHorasPorDiaTurnoProperty(): array
    {
        $startDate = Carbon::parse($this->startDate)->startOfDay();
        $endDate = Carbon::parse($this->endDate)->endOfDay();

        $turnos = Turno::where('activo', true)->orderBy('id')->get();

        // For each turno, collect equipo hoja_chequeo_ids that belong to tombolas
        $turnoFilaMap = []; // turno_id => [hoja_fila_id => ...]

        foreach ($turnos as $turno) {
            $equipoIds = $turno->equipos()
                ->tombolas()
                ->pluck('equipos.id');

            if ($equipoIds->isEmpty()) {
                continue;
            }

            $hojaChequeoIds = HojaChequeo::whereIn('equipo_id', $equipoIds)->pluck('id');

            if ($hojaChequeoIds->isEmpty()) {
                continue;
            }

            $filaIds = HojaFila::whereIn('hoja_chequeo_id', $hojaChequeoIds)
                ->whereRelation('valores', 'valor', 'like', '%HORAS%')
                ->pluck('id');

            $turnoFilaMap[$turno->id] = [
                'turno' => $turno,
                'hoja_chequeo_ids' => $hojaChequeoIds,
                'fila_ids' => $filaIds,
            ];
        }

        if (empty($turnoFilaMap)) {
            return ['turnos' => [], 'days' => []];
        }

        // Build day rows
        $period = CarbonPeriod::create($startDate->copy()->startOfDay(), $endDate->copy()->startOfDay());
        $days = [];

        foreach ($period as $day) {
            $dayStart = $day->copy()->startOfDay();
            $dayEnd = $day->copy()->endOfDay();
            $turnoData = [];

            foreach ($turnoFilaMap as $turnoId => $meta) {
                $ejecucionIds = HojaEjecucion::whereIn('hoja_chequeo_id', $meta['hoja_chequeo_ids'])
                    ->where('turno_id', $turnoId)
                    ->whereNotNull('finalizado_en')
                    ->whereBetween('finalizado_en', [$dayStart, $dayEnd])
                    ->pluck('id');

                $avg = 0;
                if ($ejecucionIds->isNotEmpty() && $meta['fila_ids']->isNotEmpty()) {
                    $valores = HojaFilaRespuesta::whereIn('hoja_ejecucion_id', $ejecucionIds)
                        ->whereIn('hoja_fila_id', $meta['fila_ids'])
                        ->whereNotNull('numeric_value')
                        ->pluck('numeric_value');

                    $avg = $valores->count() > 0 ? round($valores->avg(), 1) : 0;
                }

                $turnoData[$turnoId] = $avg;
            }

            $days[] = [
                'day' => $day->day,
                'date' => $day->format('Y-m-d'),
                'turno_data' => $turnoData,
            ];
        }

        $turnosList = collect($turnoFilaMap)->map(fn ($meta) => [
            'id' => $meta['turno']->id,
            'nombre' => $meta['turno']->nombre,
        ])->values()->toArray();

        return ['turnos' => $turnosList, 'days' => $days];
    }

    // -------------------------------------------------------------------------
    // Matriz horas por día y turno (T = Tintoreria, L = Lavanderia)
    // -------------------------------------------------------------------------

    public function getMatrizHorasProperty(): array
    {
        $startDate = Carbon::parse($this->startDate)->startOfDay();
        $endDate = Carbon::parse($this->endDate)->endOfDay();

        $period = CarbonPeriod::create($startDate->copy(), $endDate->copy()->startOfDay());
        $days = collect($period)->map(fn ($d) => ['day' => $d->day, 'date' => $d->format('Y-m-d')]);

        $turnoTId = Turno::where('nombre', 'like', '%Tintoreria%')->value('id');
        $turnoLIds = Turno::where('nombre', 'like', '%Lavanderia%')->pluck('id');
        $allTurnoIds = collect([$turnoTId])->merge($turnoLIds)->filter()->unique()->values();

        $equiposQuery = Equipo::tombolas()->orderBy('tag');
        if ($this->equipoId) {
            $equiposQuery->where('id', $this->equipoId);
        }
        $equipos = $equiposQuery->get();

        $sumPerDay = $days->mapWithKeys(fn ($d) => [$d['date'] => ['T' => 0.0, 'L' => 0.0]])->toArray();
        $rows = [];

        foreach ($equipos as $equipo) {
            $hcIds = HojaChequeo::where('equipo_id', $equipo->id)->pluck('id');

            $data = $days->mapWithKeys(fn ($d) => [$d['date'] => ['T' => 0.0, 'L' => 0.0]])->toArray();

            if ($hcIds->isNotEmpty()) {
                $filaIds = HojaFila::whereIn('hoja_chequeo_id', $hcIds)
                    ->whereRelation('valores', 'valor', 'like', '%HORAS%')
                    ->pluck('id');

                $ejecuciones = HojaEjecucion::whereIn('hoja_chequeo_id', $hcIds)
                    ->whereIn('turno_id', $allTurnoIds)
                    ->whereNotNull('finalizado_en')
                    ->whereBetween('finalizado_en', [$startDate, $endDate])
                    ->get(['id', 'finalizado_en', 'turno_id']);

                $ejIds = $ejecuciones->pluck('id');

                $respuestasByEj = ($filaIds->isNotEmpty() && $ejIds->isNotEmpty())
                    ? HojaFilaRespuesta::whereIn('hoja_ejecucion_id', $ejIds)
                        ->whereIn('hoja_fila_id', $filaIds)
                        ->whereNotNull('numeric_value')
                        ->pluck('numeric_value', 'hoja_ejecucion_id')
                    : collect();

                $ejByDay = $ejecuciones->groupBy(fn ($e) => Carbon::parse($e->finalizado_en)->format('Y-m-d'));

                foreach ($days as $d) {
                    $date = $d['date'];
                    $tVal = 0.0;
                    $lVal = 0.0;

                    foreach ($ejByDay[$date] ?? [] as $ej) {
                        $hrs = (float) ($respuestasByEj[$ej->id] ?? 0);
                        if ($ej->turno_id == $turnoTId) {
                            $tVal += $hrs;
                        } else {
                            $lVal += $hrs;
                        }
                    }

                    $data[$date] = ['T' => $tVal, 'L' => $lVal];
                    $sumPerDay[$date]['T'] += $tVal;
                    $sumPerDay[$date]['L'] += $lVal;
                }
            }

            $sumaT = array_sum(array_column($data, 'T'));
            $sumaL = array_sum(array_column($data, 'L'));
            $total = $sumaT + $sumaL;

            $rows[] = [
                'tag' => $equipo->tag,
                'data' => $data,
                'suma_T' => $sumaT,
                'suma_L' => $sumaL,
                'suma' => $total,
                'promedio' => $days->count() > 0 ? round($total / max(1, $days->count() * 2), 1) : 0,
            ];
        }

        $numEquipos = max(1, count($rows));
        $avgPerDay = [];
        foreach ($sumPerDay as $date => $vals) {
            $avgPerDay[$date] = [
                'T' => round($vals['T'] / $numEquipos, 0),
                'L' => round($vals['L'] / $numEquipos, 0),
            ];
        }

        $grandTotal = array_sum(array_column($rows, 'suma'));

        return [
            'days' => $days->toArray(),
            'rows' => $rows,
            'sum_per_day' => $sumPerDay,
            'avg_per_day' => $avgPerDay,
            'suma' => $grandTotal,
            'promedio' => $days->count() > 0 ? round($grandTotal / max(1, $days->count() * 2), 1) : 0,
        ];
    }

    public function render()
    {
        return view('livewire.analisis.analisis-tombolas');
    }
}
