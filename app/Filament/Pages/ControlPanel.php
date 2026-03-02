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

        $totalChequeos = HojaEjecucion::finished()
            ->whereDate('finalizado_en', $today)
            ->count();

        $totalRecorridos = LogRecorrido::whereDate('fecha', $today)->count();

        $totalReportes = Reporte::whereDate('fecha', $today)->count();
        $reportesPendientes = Reporte::whereDate('fecha', $today)
            ->where('estado', 'pendiente')
            ->count();

        return [
            'chequeos' => $totalChequeos,
            'recorridos' => $totalRecorridos,
            'reportes' => $totalReportes,
            'reportes_pendientes' => $reportesPendientes,
        ];
    }

    public function getEquiposByAreaProperty(): array
    {
        $today = Carbon::today();

        $mapEquipo = function (Equipo $equipo) use ($today) {
            $hojaChequeoIds = $equipo->hojaChequeos()->pluck('id');

            $chequeosHoy = HojaEjecucion::finished()
                ->whereIn('hoja_chequeo_id', $hojaChequeoIds)
                ->whereDate('finalizado_en', $today)
                ->count();

            $reportesHoy = $equipo->reportes()
                ->whereDate('fecha', $today)
                ->count();

            $reportesPendientes = $equipo->reportes()
                ->where('estado', 'pendiente')
                ->count();

            $ultimoChequeo = HojaEjecucion::finished()
                ->whereIn('hoja_chequeo_id', $hojaChequeoIds)
                ->orderByDesc('finalizado_en')
                ->first();

            return [
                'id' => $equipo->id,
                'nombre' => $equipo->nombre,
                'tag' => $equipo->tag,
                'foto' => $equipo->foto,
                'capacidad' => $equipo->capacidad(),
                'chequeos_hoy' => $chequeosHoy,
                'reportes_hoy' => $reportesHoy,
                'reportes_pendientes' => $reportesPendientes,
                'ultimo_chequeo' => $ultimoChequeo?->finalizado_en?->diffForHumans(),
                'tiene_chequeo_hoy' => $chequeosHoy > 0,
                'ultimo_chequeo_viejo' => $ultimoChequeo
                    ? $ultimoChequeo->finalizado_en->lt(Carbon::now()->subDay())
                    : false,
            ];
        };

        return Equipo::orderBy('area')
            ->orderBy('nombre')
            ->get()
            ->groupBy(fn (Equipo $e) => $e->area
                ? ucwords(mb_strtolower($e->area))
                : 'Sin Área')
            ->map(fn ($equipos, $area) => [
                'area' => $area,
                'equipos' => $equipos->map($mapEquipo)->toArray(),
            ])
            ->values()
            ->toArray();
    }

    public function getRecorridosHoyProperty(): int
    {
        return LogRecorrido::whereDate('fecha', Carbon::today())->count();
    }

    /**
     * Alertas visuales: equipos sin chequeo hoy, reportes pendientes de alta prioridad,
     * y equipos con reportes sin resolver.
     */
    public function getAlertsProperty(): array
    {
        $today = Carbon::today();
        $alerts = [];

        // 1. Equipos con hoja de chequeo activa que NO tienen chequeo hoy
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
                'icon' => 'clipboard-document-check',
                'title' => count($equiposSinChequeo).' equipo(s) sin chequeo hoy',
                'description' => implode(', ', array_slice(array_values($equiposSinChequeo), 0, 5))
                    .(count($equiposSinChequeo) > 5 ? ' y '.(count($equiposSinChequeo) - 5).' más...' : ''),
                'link' => ChequeosResource::getUrl('index'),
                'link_label' => 'Ver chequeos',
            ];
        }

        // 2. Reportes pendientes de alta prioridad
        $reportesAlta = Reporte::where('estado', 'pendiente')
            ->where('prioridad', 'alta')
            ->with('equipo')
            ->get();

        if ($reportesAlta->isNotEmpty()) {
            $nombres = $reportesAlta->map(fn ($r) => $r->equipo?->nombre ?? 'N/A')->unique()->take(5)->implode(', ');
            $alerts[] = [
                'type' => 'danger',
                'icon' => 'fire',
                'title' => $reportesAlta->count().' reporte(s) ALTA prioridad pendientes',
                'description' => 'Equipos: '.$nombres
                    .($reportesAlta->count() > 5 ? ' y más...' : ''),
                'link' => ReporteResource::getUrl('index'),
                'link_label' => 'Ver reportes',
            ];
        }

        // 3. Reportes pendientes de media prioridad
        $reportesMedia = Reporte::where('estado', 'pendiente')
            ->where('prioridad', 'media')
            ->count();

        if ($reportesMedia > 0) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'exclamation-triangle',
                'title' => $reportesMedia.' reporte(s) de prioridad MEDIA pendientes',
                'description' => 'Requieren atención pronto.',
                'link' => ReporteResource::getUrl('index'),
                'link_label' => 'Ver reportes',
            ];
        }

        // 4. Reportes creados hoy
        $reportesHoy = Reporte::whereDate('fecha', $today)->count();
        if ($reportesHoy > 0) {
            $alerts[] = [
                'type' => 'info',
                'icon' => 'information-circle',
                'title' => $reportesHoy.' reporte(s) nuevo(s) hoy',
                'description' => 'Se generaron nuevos reportes durante el día.',
                'link' => ReporteResource::getUrl('index'),
                'link_label' => 'Ver reportes',
            ];
        }

        // 5. All good
        if (empty($alerts)) {
            $alerts[] = [
                'type' => 'success',
                'icon' => 'check-circle',
                'title' => 'Todo en orden',
                'description' => 'No hay alertas pendientes. Todos los equipos están al día.',
                'link' => null,
                'link_label' => null,
            ];
        }

        return $alerts;
    }
}
