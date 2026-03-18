<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Chequeos\ChequeosResource;
use App\Filament\Resources\Recorridos\RecorridoResource;
use App\Filament\Resources\Reportes\ReporteResource;
use App\Models\Equipo;
use App\Models\HojaChequeo;
use App\Models\HojaEjecucion;
use App\Models\LogRecorrido;
use App\Models\Reporte;
use App\Models\Turno;
use BackedEnum;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;

class ControlPanel extends Page
{
    protected string $view = 'filament.pages.control-panel';

    protected static ?string $title = 'Panel de Control';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $navigationLabel = 'Panel de Control';

    protected static ?int $navigationSort = -3;

    protected static ?string $slug = 'control-panel';

    public ?int $selectedTurnoId = null;

    public string $cumplimientoStart = '';

    public string $cumplimientoEnd = '';

    public bool $showCentroCosto = false;

    public function mount(): void
    {
        $this->cumplimientoStart = now()->startOfWeek()->format('Y-m-d');
        $this->cumplimientoEnd = now()->format('Y-m-d');
    }

    public function setCumplimientoPreset(string $preset): void
    {
        [$this->cumplimientoStart, $this->cumplimientoEnd] = match ($preset) {
            'today' => [now()->format('Y-m-d'), now()->format('Y-m-d')],
            'week' => [now()->startOfWeek()->format('Y-m-d'), now()->format('Y-m-d')],
            'month' => [now()->startOfMonth()->format('Y-m-d'), now()->format('Y-m-d')],
            default => [$this->cumplimientoStart, $this->cumplimientoEnd],
        };
    }

    public function getTurnosProperty()
    {
        return Turno::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']);
    }

    public function getTodayStatsProperty(): array
    {
        $today = Carbon::today();
        $turnoId = $this->selectedTurnoId;

        return [
            'chequeos' => HojaEjecucion::finished()
                ->whereDate('finalizado_en', $today)
                ->when($turnoId, fn ($q) => $q->where('turno_id', $turnoId))
                ->count(),
            'recorridos' => LogRecorrido::whereDate('fecha', $today)
                ->when($turnoId, fn ($q) => $q->where('turno_id', $turnoId))
                ->count(),
            'reportes' => Reporte::whereDate('fecha', $today)->count(),
            'reportes_pendientes' => Reporte::where('estado', 'pendiente')->count(),
        ];
    }

    public function getEquiposByAreaProperty(): array
    {
        $today = Carbon::today();
        $turnoId = $this->selectedTurnoId;

        $equipos = Equipo::orderBy('area')
            ->orderBy('nombre')
            ->when($turnoId, fn ($q) => $q->whereHas('turnos', fn ($tq) => $tq->where('turnos.id', $turnoId)))
            ->withCount([
                'reportes as reportes_hoy' => fn ($q) => $q->whereDate('fecha', $today),
                'reportes as reportes_pendientes' => fn ($q) => $q->where('estado', 'pendiente'),
                'reportes as reportes_alta_prioridad' => fn ($q) => $q->where('estado', 'pendiente')->where('prioridad', 'alta'),
            ])
            ->with(['hojaChequeos:id,equipo_id', 'specs'])
            ->get();

        $allHojaIds = $equipos->flatMap(fn ($e) => $e->hojaChequeos->pluck('id'))->unique()->all();

        $hojasConChequeoHoy = empty($allHojaIds) ? [] : HojaEjecucion::finished()
            ->whereDate('finalizado_en', $today)
            ->whereIn('hoja_chequeo_id', $allHojaIds)
            ->when($turnoId, fn ($q) => $q->where('turno_id', $turnoId))
            ->pluck('hoja_chequeo_id')
            ->unique()->flip()->all();

        $ultimosChequeos = empty($allHojaIds) ? collect() : HojaEjecucion::finished()
            ->whereIn('hoja_chequeo_id', $allHojaIds)
            ->select(['id', 'hoja_chequeo_id', 'finalizado_en', 'nombre_operador', 'turno_id'])
            ->with('turno:id,nombre')
            ->when($turnoId, fn ($q) => $q->where('turno_id', $turnoId))
            ->orderByDesc('finalizado_en')
            ->get()
            ->unique('hoja_chequeo_id')
            ->keyBy('hoja_chequeo_id');

        $pendingEjecuciones = empty($allHojaIds) ? collect() : HojaEjecucion::pending()
            ->whereIn('hoja_chequeo_id', $allHojaIds)
            ->select(['id', 'hoja_chequeo_id', 'nombre_operador', 'created_at'])
            ->when($turnoId, fn ($q) => $q->where('turno_id', $turnoId))
            ->get()
            ->keyBy('hoja_chequeo_id');

        return $equipos
            ->groupBy(fn (Equipo $e) => $e->area ? ucwords(mb_strtolower($e->area)) : 'Sin Área')
            ->map(fn ($group, $area) => [
                'area' => $area,
                'equipos' => $group->map(function (Equipo $equipo) use ($hojasConChequeoHoy, $ultimosChequeos, $pendingEjecuciones) {
                    $hojaIds = $equipo->hojaChequeos->pluck('id')->all();
                    $tieneChequeoHoy = collect($hojaIds)->some(fn ($id) => isset($hojasConChequeoHoy[$id]));

                    $ultimoChequeo = collect($hojaIds)
                        ->map(fn ($id) => $ultimosChequeos->get($id))
                        ->filter()
                        ->sortByDesc('finalizado_en')
                        ->first();

                    $pendingEj = collect($hojaIds)
                        ->map(fn ($id) => $pendingEjecuciones->get($id))
                        ->filter()
                        ->first();

                    $capacidadSpec = $equipo->specs->first(
                        fn ($s) => str_contains(strtolower($s->tipo), 'capacidad')
                    );

                    return [
                        'id' => $equipo->id,
                        'nombre' => $equipo->nombre,
                        'tag' => $equipo->tag,
                        'foto_url' => $equipo->foto ? Storage::url($equipo->foto) : null,
                        'area' => $equipo->area ? ucwords(mb_strtolower($equipo->area)) : 'Sin Área',
                        'capacidad' => $capacidadSpec ? ($capacidadSpec->optimo.$capacidadSpec->unidad) : '',
                        'reportes_hoy' => $equipo->reportes_hoy,
                        'reportes_pendientes' => $equipo->reportes_pendientes,
                        'reportes_alta_prioridad' => $equipo->reportes_alta_prioridad,
                        'tiene_chequeo_hoy' => $tieneChequeoHoy,
                        'ultimo_chequeo' => $ultimoChequeo?->finalizado_en->diffForHumans(),
                        'ultimo_chequeo_operador' => $ultimoChequeo?->nombre_operador,
                        'ultimo_chequeo_viejo' => $ultimoChequeo
                            ? $ultimoChequeo->finalizado_en->lt(Carbon::now()->subDay())
                            : false,
                        'en_progreso' => $pendingEj !== null,
                        'en_progreso_operador' => $pendingEj?->nombre_operador,
                        'en_progreso_desde' => $pendingEj?->created_at->diffForHumans(),
                        'continuar_url' => $pendingEj
                            ? CreateChequeo::getUrl()."?h={$pendingEj->hoja_chequeo_id}&e={$pendingEj->id}&b=".urlencode(static::getUrl())
                            : null,
                        'ultimo_chequeo_turno' => $ultimoChequeo?->turno?->nombre,
                        'chequeos_hoy_count' => collect($hojaIds)->filter(fn ($id) => isset($hojasConChequeoHoy[$id]))->count(),
                        'chequeos_url' => ChequeosResource::getUrl('index'),
                        'reportes_pendientes_url' => ReporteResource::getUrl('index').'?'.http_build_query([
                            'tableFilters' => ['estado' => ['value' => 'pendiente'], 'equipo' => ['value' => $equipo->id]],
                        ]),
                    ];
                })->values()->all(),
            ])
            ->values()
            ->all();
    }

    public function getActivityFeedProperty(): array
    {
        $today = Carbon::today();
        $since = Carbon::now()->subDay();
        $turnoId = $this->selectedTurnoId;

        // ── In-progress chequeos started today ──
        $live = HojaEjecucion::pending()
            ->with(['hojaChequeo.equipo:id,nombre,tag', 'turno:id,nombre'])
            ->whereDate('created_at', $today)
            ->when($turnoId, fn ($q) => $q->where('turno_id', $turnoId))
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($e) => [
                'type' => 'chequeo',
                'status' => 'live',
                'time_raw' => $e->created_at->timestamp,
                'time_fmt' => $e->created_at->format('H:i'),
                'time_human' => $e->created_at->diffForHumans(),
                'elapsed' => $e->created_at->diffForHumans(now(), true),
                'operador' => $e->nombre_operador ?? '—',
                'equipo' => $e->hojaChequeo?->equipo?->nombre ?? '—',
                'equipo_tag' => $e->hojaChequeo?->equipo?->tag ?? '—',
                'turno_nombre' => $e->turno?->nombre,
                'url' => CreateChequeo::getUrl()."?h={$e->hoja_chequeo_id}&e={$e->id}&b=".urlencode(static::getUrl()),
            ])
            ->toArray();

        // ── Finished chequeos last 24 h ──
        $chequeos = HojaEjecucion::finished()
            ->with(['hojaChequeo.equipo:id,nombre,tag', 'turno:id,nombre'])
            ->where('finalizado_en', '>=', $since)
            ->when($turnoId, fn ($q) => $q->where('turno_id', $turnoId))
            ->orderByDesc('finalizado_en')
            ->limit(40)
            ->get()
            ->map(fn ($e) => [
                'type' => 'chequeo',
                'status' => 'done',
                'time_raw' => $e->finalizado_en->timestamp,
                'time_fmt' => $e->finalizado_en->format('H:i'),
                'time_human' => $e->finalizado_en->diffForHumans(),
                'elapsed' => null,
                'operador' => $e->nombre_operador ?? '—',
                'equipo' => $e->hojaChequeo?->equipo?->nombre ?? '—',
                'equipo_tag' => $e->hojaChequeo?->equipo?->tag ?? '—',
                'turno_nombre' => $e->turno?->nombre,
                'url' => null,
            ]);

        // ── Recorridos last 24 h ──
        $recorridos = LogRecorrido::where('created_at', '>=', $since)
            ->with(['operador:id,name', 'formularioRecorrido:id,nombre', 'turno:id,nombre'])
            ->when($turnoId, fn ($q) => $q->where('turno_id', $turnoId))
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(fn ($r) => [
                'type' => 'recorrido',
                'status' => 'done',
                'time_raw' => $r->created_at->timestamp,
                'time_fmt' => $r->created_at->format('H:i'),
                'time_human' => $r->created_at->diffForHumans(),
                'elapsed' => null,
                'operador' => $r->operador?->name ?? '—',
                'equipo' => $r->formularioRecorrido?->nombre ?? 'Recorrido',
                'equipo_tag' => null,
                'turno_nombre' => $r->turno?->nombre,
                'url' => CreateRecorrido::getUrl()."?f={$r->formulario_recorrido_id}&e={$r->id}&b=".urlencode(RecorridoResource::getUrl('index')),
                'falla' => null,
                'prioridad' => null,
            ]);

        // ── Reportes last 24 h (no turno filter — Reporte has no turno_id) ──
        $reportes = Reporte::where('fecha', '>=', $since)
            ->with(['equipo:id,nombre,tag', 'user:id,name'])
            ->orderByDesc('fecha')
            ->limit(20)
            ->get()
            ->map(fn ($r) => [
                'type' => 'reporte',
                'status' => $r->prioridad === 'alta' ? 'danger' : 'warning',
                'time_raw' => $r->fecha->timestamp,
                'time_fmt' => $r->fecha->format('H:i'),
                'time_human' => $r->fecha->diffForHumans(),
                'elapsed' => null,
                'operador' => $r->user?->name ?? '—',
                'equipo' => $r->equipo?->nombre ?? '—',
                'equipo_tag' => $r->equipo?->tag ?? null,
                'turno_nombre' => null,
                'url' => ReporteResource::getUrl('index').'?'.http_build_query([
                    'tableFilters' => array_filter([
                        'equipo' => $r->equipo_id ? ['value' => $r->equipo_id] : null,
                    ]),
                ]),
                'falla' => $r->falla ?? $r->nombre ?? null,
                'prioridad' => $r->prioridad,
            ]);

        $done = collect([...$chequeos->toArray(), ...$recorridos->toArray(), ...$reportes->toArray()])
            ->sortByDesc('time_raw')
            ->values()
            ->toArray();

        return compact('live', 'done');
    }

    public function getCumplimientoTurnoProperty(): array
    {
        if (! $this->selectedTurnoId) {
            return [];
        }

        $turno = Turno::with([
            'centroCosto.offDays',
            'centroCosto.turnos' => fn ($q) => $q->where('activo', true)
                ->with(['equipos.hojaChequeos:id,equipo_id']),
            'equipos.hojaChequeos:id,equipo_id',
        ])->find($this->selectedTurnoId);

        if (! $turno?->centroCosto) {
            return [];
        }

        $startDate = Carbon::parse($this->cumplimientoStart)->startOfDay();
        $endDate = Carbon::parse($this->cumplimientoEnd)->endOfDay();
        $cc = $turno->centroCosto;

        $offDates = $cc->offDays
            ->filter(fn ($od) => $od->fecha->between($startDate, $endDate))
            ->map(fn ($od) => $od->fecha->format('Y-m-d'))
            ->toArray();

        $turnos = $this->showCentroCosto
            ? $cc->turnos->where('activo', true)
            : collect([$turno]);

        $result = [];

        foreach ($turnos as $t) {
            $scheduledDays = $t->dias ?? [];
            $equipos = $this->showCentroCosto ? $t->equipos : $turno->equipos;
            $equiposCount = $equipos->count();

            if ($equiposCount === 0 || empty($scheduledDays)) {
                continue;
            }

            $period = CarbonPeriod::create($startDate->copy()->startOfDay(), $endDate->copy()->startOfDay());
            $validWorkingDates = [];

            foreach ($period as $day) {
                $dayName = strtolower($day->englishDayOfWeek);
                $dateStr = $day->format('Y-m-d');
                if (in_array($dayName, $scheduledDays) && ! in_array($dateStr, $offDates)) {
                    $validWorkingDates[] = $dateStr;
                }
            }

            $workingDays = count($validWorkingDates);
            if ($workingDays === 0) {
                continue;
            }

            $expected = $workingDays * $equiposCount;
            $actual = 0;

            foreach ($equipos as $equipo) {
                $hojaIds = $equipo->hojaChequeos->pluck('id');
                if ($hojaIds->isEmpty()) {
                    continue;
                }

                $diasRevisados = HojaEjecucion::whereIn('hoja_chequeo_id', $hojaIds)
                    ->where('turno_id', $t->id)
                    ->whereNotNull('finalizado_en')
                    ->whereBetween('finalizado_en', [$startDate, $endDate])
                    ->whereIn(DB::raw('DATE(finalizado_en)'), $validWorkingDates)
                    ->selectRaw('COUNT(DISTINCT DATE(finalizado_en)) as total')
                    ->value('total') ?? 0;

                $actual += $diasRevisados;
            }

            $percentage = $expected > 0 ? min(100, round(($actual / $expected) * 100, 1)) : 0;

            $result[] = [
                'turno' => $t->nombre,
                'is_selected' => $t->id === $this->selectedTurnoId,
                'centro_costo' => $cc->nombre,
                'date_range' => $startDate->format('d/m').' – '.$endDate->format('d/m/Y'),
                'expected' => $expected,
                'actual' => $actual,
                'percentage' => $percentage,
                'working_days' => $workingDays,
                'equipos' => $equiposCount,
            ];
        }

        return $result;
    }

    #[On('echo:chequeos,.saved')]
    public function refreshOnChequeoSaved(): void
    {
        // Triggers Livewire re-render — computed getters re-run automatically.
    }

    public function getAlertsProperty(): array
    {
        $today = Carbon::today();
        $turnoId = $this->selectedTurnoId;
        $alerts = [];
        $hojaChequeos = HojaChequeo::where('encendido', true)
            ->when($turnoId, fn ($q) => $q->whereHas('equipo.turnos', fn ($tq) => $tq->where('turnos.id', $turnoId)))
            ->with('equipo')
            ->get();
        $equiposSinChequeo = [];

        foreach ($hojaChequeos as $hoja) {
            $tieneHoy = HojaEjecucion::finished()
                ->where('hoja_chequeo_id', $hoja->id)
                ->whereDate('finalizado_en', $today)
                ->when($turnoId, fn ($q) => $q->where('turno_id', $turnoId))
                ->exists();

            if (! $tieneHoy && $hoja->equipo) {
                $equiposSinChequeo[$hoja->equipo->id] = $hoja->equipo->nombre;
            }
        }

        if (! empty($equiposSinChequeo)) {
            $alerts[] = [
                'type' => 'warning',
                'title' => count($equiposSinChequeo).' equipo(s) sin chequeo hoy',
                'description' => implode(', ', array_slice(array_values($equiposSinChequeo), 0, 5))
                    .(count($equiposSinChequeo) > 5 ? ' y '.(count($equiposSinChequeo) - 5).' más...' : ''),
                'link' => ChequeosResource::getUrl('index'),
            ];
        }

        $reportesAlta = Reporte::where('estado', 'pendiente')->where('prioridad', 'alta')->with('equipo')->get();

        if ($reportesAlta->isNotEmpty()) {
            $nombres = $reportesAlta->map(fn ($r) => $r->equipo?->nombre ?? 'N/A')->unique()->take(5)->implode(', ');
            $alerts[] = [
                'type' => 'danger',
                'title' => $reportesAlta->count().' reporte(s) de ALTA prioridad pendientes',
                'description' => 'Equipos: '.$nombres.($reportesAlta->count() > 5 ? ' y más...' : ''),
                'link' => ReporteResource::getUrl('index'),
            ];
        }

        return $alerts;
    }
}
