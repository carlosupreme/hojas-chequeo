<?php

namespace App\Livewire\Analisis;

use App\Area;
use App\Models\Equipo;
use App\Models\Reporte;
use Carbon\Carbon;
use Livewire\Component;

class AnalisisReportes extends Component
{
    public $startDate;

    public $endDate;

    public ?int $equipoId = null;

    public ?string $area = null;

    protected $listeners = ['dateRangeUpdated' => 'handleDateRangeUpdate'];

    public function mount($startDate = null, $endDate = null): void
    {
        $this->startDate = $startDate ? Carbon::parse($startDate)->format('Y-m-d') : now()->subMonth()->format('Y-m-d');
        $this->endDate = $endDate ? Carbon::parse($endDate)->format('Y-m-d') : now()->format('Y-m-d');
    }

    public function handleDateRangeUpdate($data)
    {
        logger('Date range updated:', $data);

        $this->startDate = Carbon::parse($data['inicio'])->format('Y-m-d');
        $this->endDate = Carbon::parse($data['final'])->format('Y-m-d');

        logger('New dates:', ['start' => $this->startDate, 'end' => $this->endDate]);

        // Trigger chart update
        $this->dispatch('chartDataUpdated');
    }

    public function updatedEquipoId()
    {
        $this->dispatch('chartDataUpdated');
    }

    public function updatedArea()
    {
        $this->dispatch('chartDataUpdated');
    }

    public function updatedStartDate()
    {
        $this->dispatch('chartDataUpdated');
    }

    public function updatedEndDate()
    {
        $this->dispatch('chartDataUpdated');
    }

    public function clearFilters()
    {
        $this->equipoId = null;
        $this->area = null;
        $this->dispatch('chartDataUpdated');
    }

    public function getEquiposProperty()
    {
        return Equipo::orderBy('nombre')->get();
    }

    public function getAreasProperty()
    {
        return collect(Area::cases())->mapWithKeys(function (Area $area) {
            return [$area->value => $area->label()];
        })->toArray();
    }

    public function render()
    {
        return view('livewire.analisis.analisis-reportes');
    }

    public function getSolicitudesStatsProperty()
    {
        // Build base query with date range filter
        $query = Reporte::query();

        // ...existing code...

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('fecha', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay(),
            ]);
        }

        // Filter by equipo if specified
        if ($this->equipoId) {
            $query->where('equipo_id', $this->equipoId);
        }

        // Filter by area if specified
        if ($this->area) {
            $query->where('area', $this->area);
        }

        // Generate weekly data for history using database queries
        $startDate = Carbon::parse($this->startDate);
        $endDate = Carbon::parse($this->endDate);

        $history_labels = [];
        $history_values = [];

        // Generate weekly buckets
        $currentDate = $startDate->copy()->startOfWeek();
        $weekCount = 0;

        while ($currentDate->lte($endDate) && $weekCount < 20) { // Allow up to 20 weeks
            $weekEnd = $currentDate->copy()->endOfWeek();
            if ($weekEnd->gt($endDate)) {
                $weekEnd = $endDate->copy()->endOfDay();
            }

            // Create a proper week label in Spanish using isoFormat for localization
            $weekLabel = $currentDate->isoFormat('D MMM').' - '.$weekEnd->isoFormat('D MMM');
            $weekCount++;

            // Count reports for this week using a fresh query
            $weekQuery = clone $query;
            $weekReports = $weekQuery->whereBetween('fecha', [
                $currentDate->startOfDay(),
                $weekEnd->endOfDay(),
            ])->count();

            $history_labels[] = $weekLabel;
            $history_values[] = $weekReports;

            $currentDate->addWeek();
        }

        // Get all filtered reports for status calculation
        $reportes = $query->get();

        // Calculate status counts
        $pendientes = $reportes->where('estado', 'pendiente')->count();
        $realizadas = $reportes->where('estado', 'realizada')->count();

        return [
            'history_labels' => $history_labels,
            'history_values' => $history_values,
            'status' => [
                'pendientes' => $pendientes,
                'realizadas' => $realizadas,
            ],
            'total_reports' => $reportes->count(),
        ];
    }

    public function getFilteredReportesProperty()
    {
        $query = Reporte::with(['equipo', 'user']);

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('fecha', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay(),
            ]);
        }

        if ($this->equipoId) {
            $query->where('equipo_id', $this->equipoId);
        }

        if ($this->area) {
            $query->where('area', $this->area);
        }

        return $query->orderBy('fecha', 'desc')->get();
    }

    public function getEquipoStatsProperty()
    {
        if (! $this->equipoId) {
            return null;
        }

        $equipo = Equipo::find($this->equipoId);

        if (! $equipo) {
            return null;
        }

        $reportesCount = $this->getFilteredReportesProperty()->count();

        return [
            'equipo' => $equipo,
            'total_reportes' => $reportesCount,
            'reportes_pendientes' => $this->getFilteredReportesProperty()->where('estado', 'pendiente')->count(),
            'reportes_realizadas' => $this->getFilteredReportesProperty()->where('estado', 'realizada')->count(),
        ];
    }

    public function getAreaStatsProperty()
    {
        if (! $this->area) {
            return null;
        }

        $reportes = $this->getFilteredReportesProperty();

        return [
            'area_name' => $this->area,
            'total_reportes' => $reportes->count(),
            'equipos_involucrados' => $reportes->pluck('equipo_id')->unique()->count(),
            'reportes_by_priority' => [
                'alta' => $reportes->where('prioridad', 'alta')->count(),
                'media' => $reportes->where('prioridad', 'media')->count(),
                'baja' => $reportes->where('prioridad', 'baja')->count(),
            ],
        ];
    }

    public function getPriorityStatsProperty()
    {
        $reportes = $this->getFilteredReportesProperty();

        return [
            'alta' => $reportes->where('prioridad', 'alta')->count(),
            'media' => $reportes->where('prioridad', 'media')->count(),
            'baja' => $reportes->where('prioridad', 'baja')->count(),
        ];
    }
}
