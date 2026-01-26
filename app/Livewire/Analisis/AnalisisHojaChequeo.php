<?php

namespace App\Livewire\Analisis;

use App\Models\Equipo;
use App\Models\HojaChequeo;
use App\Models\HojaEjecucion;
use App\Models\HojaFila;
use App\Models\HojaFilaRespuesta;
use App\Models\Tarjeton;
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
     * Get Calderas stats (Caldera 1 and Caldera 2)
     */
    public function getCalderasStatsProperty()
    {
        $startDate = Carbon::parse($this->startDate)->startOfDay();
        $endDate = Carbon::parse($this->endDate)->endOfDay();

        $calderas = [
            ['tag' => 'CM-CAL-01', 'nombre' => 'Caldera 1'],
            ['tag' => 'CM-CAL-02', 'nombre' => 'Caldera 2'],
        ];

        $stats = [];

        foreach ($calderas as $calderaInfo) {
            $equipo = Equipo::where('tag', $calderaInfo['tag'])->first();

            if (! $equipo) {
                $stats[] = [
                    'nombre' => $calderaInfo['nombre'],
                    'tag' => $calderaInfo['tag'],
                    'totals' => [
                        'horas_trabajo' => 0,
                        'efectividad_vapor' => 0,
                        'temperatura' => 0,
                        'presion' => 0,
                    ],
                    'averages' => [
                        'horas_trabajo' => 0,
                        'efectividad_vapor' => 0,
                        'temperatura' => 0,
                        'presion' => 0,
                    ],
                    'tarjetones_count' => 0,
                ];

                continue;
            }

            // Get tarjetones stats
            $tarjetones = Tarjeton::where('equipo_id', $equipo->id)
                ->whereBetween('fecha', [$startDate, $endDate])
                ->get();

            $tarjetonesCount = $tarjetones->count();
            $totalMinutos = $tarjetones->sum('tiempo_operacion_minutos') ?? 0;
            $totalHoras = round($totalMinutos / 60, 1);
            $avgHoras = $tarjetonesCount > 0 ? round($totalHoras / $tarjetonesCount, 1) : 0;

            // Efectividad de vapor (% without falla_vapor)
            $sinFallaVapor = $tarjetones->where('falla_vapor', false)->count();
            $efectividadVapor = $tarjetonesCount > 0 ? round(($sinFallaVapor / $tarjetonesCount) * 100, 1) : 0;

            // Get HojaChequeo for this equipo to find temperatura and presion
            $hojaChequeo = HojaChequeo::where('equipo_id', $equipo->id)->first();

            $temperaturaTotal = 0;
            $temperaturaAvg = 0;
            $presionTotal = 0;
            $presionAvg = 0;
            $temperaturaCount = 0;
            $presionCount = 0;

            if ($hojaChequeo) {
                // Get ejecuciones in date range
                $ejecucionIds = HojaEjecucion::where('hoja_chequeo_id', $hojaChequeo->id)
                    ->whereNotNull('finalizado_en')
                    ->whereBetween('finalizado_en', [$startDate, $endDate])
                    ->pluck('id');

                if ($ejecucionIds->isNotEmpty()) {
                    // Find filas with temperatura answer type
                    $temperaturaFilaIds = HojaFila::where('hoja_chequeo_id', $hojaChequeo->id)
                        ->whereHas('answerType', fn ($q) => $q->where('key', 'temperatura'))
                        ->pluck('id');

                    // Find filas with presion answer type
                    $presionFilaIds = HojaFila::where('hoja_chequeo_id', $hojaChequeo->id)
                        ->whereHas('answerType', fn ($q) => $q->where('key', 'presion'))
                        ->pluck('id');

                    // Get temperatura values
                    if ($temperaturaFilaIds->isNotEmpty()) {
                        $temperaturaRespuestas = HojaFilaRespuesta::whereIn('hoja_ejecucion_id', $ejecucionIds)
                            ->whereIn('hoja_fila_id', $temperaturaFilaIds)
                            ->whereNotNull('numeric_value')
                            ->pluck('numeric_value');

                        $temperaturaCount = $temperaturaRespuestas->count();
                        $temperaturaTotal = round($temperaturaRespuestas->sum(), 1);
                        $temperaturaAvg = $temperaturaCount > 0 ? round($temperaturaTotal / $temperaturaCount, 1) : 0;
                    }

                    // Get presion values
                    if ($presionFilaIds->isNotEmpty()) {
                        $presionRespuestas = HojaFilaRespuesta::whereIn('hoja_ejecucion_id', $ejecucionIds)
                            ->whereIn('hoja_fila_id', $presionFilaIds)
                            ->whereNotNull('numeric_value')
                            ->pluck('numeric_value');

                        $presionCount = $presionRespuestas->count();
                        $presionTotal = round($presionRespuestas->sum(), 1);
                        $presionAvg = $presionCount > 0 ? round($presionTotal / $presionCount, 1) : 0;
                    }
                }
            }

            $stats[] = [
                'nombre' => $calderaInfo['nombre'],
                'tag' => $calderaInfo['tag'],
                'totals' => [
                    'horas_trabajo' => $totalHoras,
                    'efectividad_vapor' => $efectividadVapor,
                    'temperatura' => $temperaturaTotal,
                    'presion' => $presionTotal,
                ],
                'averages' => [
                    'horas_trabajo' => $avgHoras,
                    'efectividad_vapor' => $efectividadVapor,
                    'temperatura' => $temperaturaAvg,
                    'presion' => $presionAvg,
                ],
                'tarjetones_count' => $tarjetonesCount,
            ];
        }

        return $stats;
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
                    Carbon::parse($this->endDate)->endOfDay(),
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
                    Carbon::parse($this->endDate)->endOfDay(),
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
                    Carbon::parse($this->endDate)->endOfDay(),
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
