<?php

namespace App\Livewire\Analisis;

use App\Models\HojaChequeo;
use App\Models\HojaEjecucion;
use App\Models\HojaFilaRespuesta;
use App\Models\Turno;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AnalisisHojaChequeo extends Component
{
    public $startDate;
    public $endDate;
    public ?int $hojaChequeoId = null;

    protected $listeners = ['dateRangeUpdated' => 'handleDateRangeUpdate'];

    public function mount($startDate = null, $endDate = null): void
    {
        $this->startDate = $startDate ? Carbon::parse($startDate)->format('Y-m-d') : now()->subMonth()->format('Y-m-d');
        $this->endDate = $endDate ? Carbon::parse($endDate)->format('Y-m-d') : now()->format('Y-m-d');
    }

    public function handleDateRangeUpdate($data)
    {
        $this->startDate = Carbon::parse($data['inicio'])->format('Y-m-d');
        $this->endDate = Carbon::parse($data['final'])->format('Y-m-d');
        $this->dispatch('chartDataUpdated');
    }

    public function updatedHojaChequeoId()
    {
        $this->dispatch('chartDataUpdated');
    }

    public function getHojaChequeosProperty()
    {
        return HojaChequeo::with('equipo')->get()->mapWithKeys(function ($hoja) {
            $equipoName = $hoja->equipo?->nombre ?? 'Sin equipo';
            return [$hoja->id => "{$equipoName} (v{$hoja->version})"];
        })->toArray();
    }

    public function render()
    {
        return view('livewire.analisis.analisis-hoja-chequeo');
    }

    /**
     * Get percentage of "realizado" (answer_option_id = 1) responses by Turno
     */
    public function getTurnoCompletionStatsProperty()
    {
        $turnos = Turno::where('activo', true)->orderBy('id')->get();

        $labels = [];
        $percentages = [];
        $colors = ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'];

        foreach ($turnos as $index => $turno) {
            // Build base query for HojaEjecucion
            $ejecucionQuery = HojaEjecucion::where('turno_id', $turno->id)
                ->whereNotNull('finalizado_en')
                ->whereBetween('finalizado_en', [
                    Carbon::parse($this->startDate)->startOfDay(),
                    Carbon::parse($this->endDate)->endOfDay()
                ]);

            // Filter by HojaChequeo if specified
            if ($this->hojaChequeoId) {
                $ejecucionQuery->where('hoja_chequeo_id', $this->hojaChequeoId);
            }

            $ejecucionIds = $ejecucionQuery->pluck('id');

            if ($ejecucionIds->isEmpty()) {
                $labels[] = $turno->nombre;
                $percentages[] = 0;
                continue;
            }

            // Total responses with answer_option_id (icon type answers)
            $totalResponses = HojaFilaRespuesta::whereIn('hoja_ejecucion_id', $ejecucionIds)
                ->whereNotNull('answer_option_id')
                ->count();

            // Responses with answer_option_id = 1 (realizado/check)
            $realizadoResponses = HojaFilaRespuesta::whereIn('hoja_ejecucion_id', $ejecucionIds)
                ->where('answer_option_id', 1)
                ->count();

            $percentage = $totalResponses > 0 ? round(($realizadoResponses / $totalResponses) * 100, 1) : 0;

            $labels[] = $turno->nombre;
            $percentages[] = $percentage;
        }

        return [
            'labels' => $labels,
            'data' => $percentages,
            'colors' => array_slice($colors, 0, count($labels)),
        ];
    }

    /**
     * Get total HojaEjecucion count by Turno
     */
    public function getTurnoEjecucionCountProperty()
    {
        $turnos = Turno::where('activo', true)->orderBy('id')->get();

        $labels = [];
        $counts = [];

        foreach ($turnos as $turno) {
            $query = HojaEjecucion::where('turno_id', $turno->id)
                ->whereNotNull('finalizado_en')
                ->whereBetween('finalizado_en', [
                    Carbon::parse($this->startDate)->startOfDay(),
                    Carbon::parse($this->endDate)->endOfDay()
                ]);

            if ($this->hojaChequeoId) {
                $query->where('hoja_chequeo_id', $this->hojaChequeoId);
            }

            $labels[] = $turno->nombre;
            $counts[] = $query->count();
        }

        return [
            'labels' => $labels,
            'data' => $counts,
        ];
    }

    /**
     * Get detailed stats per Turno for the table
     */
    public function getTurnoDetailedStatsProperty()
    {
        $turnos = Turno::where('activo', true)->orderBy('id')->get();
        $stats = [];

        foreach ($turnos as $turno) {
            $ejecucionQuery = HojaEjecucion::where('turno_id', $turno->id)
                ->whereNotNull('finalizado_en')
                ->whereBetween('finalizado_en', [
                    Carbon::parse($this->startDate)->startOfDay(),
                    Carbon::parse($this->endDate)->endOfDay()
                ]);

            if ($this->hojaChequeoId) {
                $ejecucionQuery->where('hoja_chequeo_id', $this->hojaChequeoId);
            }

            $ejecucionIds = $ejecucionQuery->pluck('id');
            $totalEjecuciones = $ejecucionIds->count();

            if ($totalEjecuciones === 0) {
                $stats[] = [
                    'turno' => $turno->nombre,
                    'total_ejecuciones' => 0,
                    'total_respuestas' => 0,
                    'realizados' => 0,
                    'realizados_mal' => 0,
                    'no_realizados' => 0,
                    'no_aplica' => 0,
                    'porcentaje_ok' => 0,
                ];
                continue;
            }

            // Count by answer_option_id
            $respuestasByOption = HojaFilaRespuesta::whereIn('hoja_ejecucion_id', $ejecucionIds)
                ->whereNotNull('answer_option_id')
                ->select('answer_option_id', DB::raw('count(*) as total'))
                ->groupBy('answer_option_id')
                ->pluck('total', 'answer_option_id')
                ->toArray();

            $totalRespuestas = array_sum($respuestasByOption);
            $realizados = $respuestasByOption[1] ?? 0;

            $stats[] = [
                'turno' => $turno->nombre,
                'total_ejecuciones' => $totalEjecuciones,
                'total_respuestas' => $totalRespuestas,
                'realizados' => $realizados,
                'realizados_mal' => $respuestasByOption[2] ?? 0,
                'no_realizados' => $respuestasByOption[3] ?? 0,
                'no_aplica' => $respuestasByOption[4] ?? 0,
                'porcentaje_ok' => $totalRespuestas > 0 ? round(($realizados / $totalRespuestas) * 100, 1) : 0,
            ];
        }

        return $stats;
    }
}
