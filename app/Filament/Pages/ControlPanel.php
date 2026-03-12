<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Chequeos\ChequeosResource;
use App\Filament\Resources\Reportes\ReporteResource;
use App\Models\Equipo;
use App\Models\HojaChequeo;
use App\Models\HojaEjecucion;
use App\Models\LogRecorrido;
use App\Models\Reporte;
use BackedEnum;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
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

    public function getTodayStatsProperty(): array
    {
        $today = Carbon::today();

        return [
            'chequeos' => HojaEjecucion::finished()->whereDate('finalizado_en', $today)->count(),
            'recorridos' => LogRecorrido::whereDate('fecha', $today)->count(),
            'reportes' => Reporte::whereDate('fecha', $today)->count(),
            'reportes_pendientes' => Reporte::whereDate('fecha', $today)->where('estado', 'pendiente')->count(),
        ];
    }

    public function getEquiposByAreaProperty(): array
    {
        $today = Carbon::today();

        // 1 query: all equipos with 3 report counts via withCount
        $equipos = Equipo::orderBy('area')
            ->orderBy('nombre')
            ->withCount([
                'reportes as reportes_hoy' => fn ($q) => $q->whereDate('fecha', $today),
                'reportes as reportes_pendientes' => fn ($q) => $q->where('estado', 'pendiente'),
                'reportes as reportes_alta_prioridad' => fn ($q) => $q->where('estado', 'pendiente')->where('prioridad', 'alta'),
            ])
            ->with(['hojaChequeos:id,equipo_id', 'specs'])
            ->get();

        $allHojaIds = $equipos->flatMap(fn ($e) => $e->hojaChequeos->pluck('id'))->unique()->all();

        // 1 query: which hoja_chequeo_ids were executed today
        $hojasConChequeoHoy = empty($allHojaIds) ? [] : HojaEjecucion::finished()
            ->whereDate('finalizado_en', $today)
            ->whereIn('hoja_chequeo_id', $allHojaIds)
            ->pluck('hoja_chequeo_id')
            ->unique()
            ->flip()
            ->all();

        // 1 query: latest execution per hoja_chequeo_id
        $ultimosChequeos = empty($allHojaIds) ? collect() : HojaEjecucion::finished()
            ->whereIn('hoja_chequeo_id', $allHojaIds)
            ->select(['hoja_chequeo_id', 'finalizado_en'])
            ->orderByDesc('finalizado_en')
            ->get()
            ->unique('hoja_chequeo_id')
            ->keyBy('hoja_chequeo_id');

        return $equipos
            ->groupBy(fn (Equipo $e) => $e->area
                ? ucwords(mb_strtolower($e->area))
                : 'Sin Área')
            ->map(fn ($group, $area) => [
                'area' => $area,
                'equipos' => $group->map(function (Equipo $equipo) use ($hojasConChequeoHoy, $ultimosChequeos) {
                    $hojaIds = $equipo->hojaChequeos->pluck('id')->all();
                    $tieneChequeoHoy = collect($hojaIds)->some(fn ($id) => isset($hojasConChequeoHoy[$id]));

                    $ultimoChequeo = collect($hojaIds)
                        ->map(fn ($id) => $ultimosChequeos->get($id))
                        ->filter()
                        ->sortByDesc('finalizado_en')
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
                        'ultimo_chequeo_viejo' => $ultimoChequeo
                            ? $ultimoChequeo->finalizado_en->lt(Carbon::now()->subDay())
                            : false,
                    ];
                })->values()->all(),
            ])
            ->values()
            ->all();
    }

    #[On('echo:chequeos,.saved')]
    public function refreshOnChequeoSaved(): void
    {
        // Triggers a Livewire re-render — computed getters re-run automatically.
    }

    public function getAlertsProperty(): array
    {
        $today = Carbon::today();
        $alerts = [];

        $hojaChequeos = HojaChequeo::where('encendido', true)->with('equipo')->get();
        $equiposSinChequeo = [];

        foreach ($hojaChequeos as $hoja) {
            $tieneHoy = HojaEjecucion::finished()
                ->where('hoja_chequeo_id', $hoja->id)
                ->whereDate('finalizado_en', $today)
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
