<?php

namespace App\Livewire\Analisis;

use App\Models\CentroCosto;
use App\Models\Equipo;
use App\Models\HojaChequeo;
use App\Models\HojaEjecucion;
use App\Models\HojaFila;
use App\Models\HojaFilaRespuesta;
use App\Models\Tarjeton;
use App\Models\Turno;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
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
     * Cumplimiento de HojaEjecuciones por Centro de Costo.
     *
     * For each CentroCosto:
     *   - Sum the expected ejecuciones across all its Turnos
     *   - Expected per Turno = (days configured in the turno within the range - off days) × equipos count
     *   - Actual = distinct valid finished days per equipo for that turno (max 1 per equipo por día)
     *   - % = actual / expected × 100
     */
    public function getCumplimientoPorCentroCostoProperty(): array
    {
        $startDate = Carbon::parse($this->startDate)->startOfDay();
        $endDate = Carbon::parse($this->endDate)->endOfDay();
        $selectedHojaChequeo = $this->hojaChequeoId
            ? HojaChequeo::select('id', 'equipo_id')->find($this->hojaChequeoId)
            : null;

        $centrosCosto = CentroCosto::with(['turnos.equipos.hojaChequeos', 'offDays'])->get();

        $ejecucionesAgrupadas = HojaEjecucion::query()
            ->whereBetween('finalizado_en', [$startDate, $endDate])
            ->whereNotNull('finalizado_en')
            ->when($selectedHojaChequeo, fn ($q) => $q->where('hoja_chequeo_id', $selectedHojaChequeo->id))
            ->select('hoja_chequeo_id', 'turno_id', DB::raw('DATE(finalizado_en) as fecha_str'))
            ->distinct()
            ->get();

        $ejecucionesPorTurnoYHoja = [];
        foreach ($ejecucionesAgrupadas as $row) {
            $fecha = is_string($row->fecha_str) ? substr($row->fecha_str, 0, 10) : Carbon::parse($row->fecha_str)->format('Y-m-d');
            $ejecucionesPorTurnoYHoja[$row->turno_id][$row->hoja_chequeo_id][$fecha] = true;
        }

        $result = [];

        foreach ($centrosCosto as $cc) {
            // Off day dates for this CC in the range
            $offDates = $cc->offDays
                ->filter(fn ($od) => $od->fecha->between($startDate, $endDate))
                ->map(fn ($od) => $od->fecha->format('Y-m-d'))
                ->toArray();

            $totalExpected = 0;
            $totalActual = 0;
            $turnosBreakdown = [];

            foreach ($cc->turnos as $turno) {
                if (! $turno->activo) {
                    continue;
                }

                $scheduledDays = $turno->dias ?? []; // e.g. ['monday', 'tuesday', ...]
                $equipos = $turno->equipos;

                if ($selectedHojaChequeo) {
                    $equipos = $equipos->where('id', $selectedHojaChequeo->equipo_id);
                }

                $equiposCount = $equipos->count();

                if ($equiposCount === 0 || empty($scheduledDays)) {
                    continue;
                }

                // Build valid working dates in range for this turno
                $validWorkingDates = [];
                $period = CarbonPeriod::create($startDate->copy()->startOfDay(), $endDate->copy()->startOfDay());

                foreach ($period as $day) {
                    $dayName = strtolower($day->englishDayOfWeek);
                    $dateStr = $day->format('Y-m-d');

                    // Day must be in turno schedule AND not an off day
                    if (in_array($dayName, $scheduledDays) && ! in_array($dateStr, $offDates)) {
                        $validWorkingDates[] = $dateStr;
                    }
                }

                $workingDays = count($validWorkingDates);

                if ($workingDays === 0) {
                    continue;
                }

                $expected = $workingDays * $equiposCount;

                // Actual = distinct valid dates checked per equipo (max 1 per day)
                $actual = 0;

                $totalExpected += $expected;

                $validWorkingDatesLookup = array_flip($validWorkingDates);

                // Per-equipo breakdown: count distinct valid working dates with a finished ejecución
                $equiposBreakdown = [];
                foreach ($equipos as $equipo) {
                    $hojaChequeoIds = $equipo->hojaChequeos->pluck('id');

                    if ($selectedHojaChequeo) {
                        $hojaChequeoIds = $hojaChequeoIds->intersect([$selectedHojaChequeo->id])->values();
                    }

                    if ($hojaChequeoIds->isEmpty()) {
                        $equiposBreakdown[] = [
                            'tag' => $equipo->tag,
                            'nombre' => $equipo->nombre,
                            'dias_revisados' => 0,
                            'dias_esperados' => $workingDays,
                        ];

                        continue;
                    }

                    $datesWithExecution = [];
                    foreach ($hojaChequeoIds as $hcId) {
                        if (isset($ejecucionesPorTurnoYHoja[$turno->id][$hcId])) {
                            foreach ($ejecucionesPorTurnoYHoja[$turno->id][$hcId] as $d => $_) {
                                $datesWithExecution[$d] = true;
                            }
                        }
                    }

                    $diasRevisados = count(array_intersect_key($datesWithExecution, $validWorkingDatesLookup));

                    $actual += $diasRevisados;

                    $equiposBreakdown[] = [
                        'tag' => $equipo->tag,
                        'nombre' => $equipo->nombre,
                        'dias_revisados' => $diasRevisados,
                        'dias_esperados' => $workingDays,
                    ];
                }

                $totalActual += $actual;

                $turnosBreakdown[] = [
                    'turno' => $turno->nombre,
                    'equipos' => $equiposCount,
                    'working_days' => $workingDays,
                    'expected' => $expected,
                    'actual' => $actual,
                    'percentage' => $expected > 0 ? min(100, round(($actual / $expected) * 100, 1)) : 0,
                    'equipos_detail' => $equiposBreakdown,
                ];
            }

            $result[] = [
                'centro_costo' => $cc->nombre,
                'total_expected' => $totalExpected,
                'total_actual' => $totalActual,
                'percentage' => $totalExpected > 0 ? min(100, round(($totalActual / $totalExpected) * 100, 1)) : 0,
                'off_days_count' => count($offDates),
                'turnos' => $turnosBreakdown,
            ];
        }

        return $result;
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
                    'sin_falla_vapor' => 0,
                ];

                continue;
            }

            // Get tarjetones stats
            $tarjetones = Tarjeton::where('equipo_id', $equipo->id)
                ->whereBetween('hora_encendido', [$startDate, $endDate])
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
                        ->whereRelation('valores', 'valor', 'TEMPERATURAS DE LAS PARTES A PRESIÓN')
                        ->value('id');

                    // Find filas with presion answer type
                    $presionFilaIds = HojaFila::where('hoja_chequeo_id', $hojaChequeo->id)
                        ->whereRelation('valores', 'valor', 'PRESIÓN DE VAPOR')
                        ->value('id');

                    // Get temperatura values
                    if ($temperaturaFilaIds) {
                        $temperaturaRespuestas = HojaFilaRespuesta::whereIn('hoja_ejecucion_id', $ejecucionIds)
                            ->where('hoja_fila_id', $temperaturaFilaIds)
                            ->whereNotNull('numeric_value')
                            ->pluck('numeric_value');

                        $temperaturaCount = $temperaturaRespuestas->count();
                        $temperaturaTotal = round($temperaturaRespuestas->sum(), 1);
                        $temperaturaAvg = $temperaturaCount > 0 ? round($temperaturaTotal / $temperaturaCount, 1) : 0;
                    }

                    // Get presion values
                    if ($presionFilaIds) {
                        $presionRespuestas = HojaFilaRespuesta::whereIn('hoja_ejecucion_id', $ejecucionIds)
                            ->where('hoja_fila_id', $presionFilaIds)
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
                'sin_falla_vapor' => $sinFallaVapor,
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
        $startDate = Carbon::parse($this->startDate)->startOfDay();
        $endDate = Carbon::parse($this->endDate)->endOfDay();
        $turnoIds = $turnos->pluck('id');

        $respuestasByTurnoAndOption = DB::table('hoja_fila_respuestas as hfr')
            ->join('hoja_ejecucions as he', 'hfr.hoja_ejecucion_id', '=', 'he.id')
            ->whereIn('he.turno_id', $turnoIds)
            ->whereNotNull('he.finalizado_en')
            ->whereBetween('he.finalizado_en', [$startDate, $endDate])
            ->whereNotNull('hfr.answer_option_id')
            ->when($this->hojaChequeoId, fn ($q) => $q->where('he.hoja_chequeo_id', $this->hojaChequeoId))
            ->select('he.turno_id', 'hfr.answer_option_id', DB::raw('COUNT(*) as total'))
            ->groupBy('he.turno_id', 'hfr.answer_option_id')
            ->get();

        $optionCounts = [];
        foreach ($respuestasByTurnoAndOption as $row) {
            $optionCounts[$row->turno_id][$row->answer_option_id] = (int) $row->total;
        }

        $labels = [];
        $percentages = [];
        $colors = ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'];

        foreach ($turnos as $turno) {
            $respuestasByOption = $optionCounts[$turno->id] ?? [];
            $totalResponses = array_sum($respuestasByOption);
            $realizadoResponses = $respuestasByOption[1] ?? 0;

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
     * Count HojaEjecucion records with es_ppm = true, scoped to selected HojaChequeo and date range.
     */
    public function getPpmCountProperty(): int
    {
        $query = HojaEjecucion::where('es_ppm', true)
            ->whereNotNull('finalizado_en')
            ->whereBetween('finalizado_en', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay(),
            ]);

        if ($this->hojaChequeoId) {
            $query->where('hoja_chequeo_id', $this->hojaChequeoId);
        }

        return $query->count();
    }

    /**
     * Get total HojaEjecucion count by Turno
     */
    public function getTurnoEjecucionCountProperty()
    {
        $turnos = Turno::where('activo', true)->orderBy('id')->get();
        $startDate = Carbon::parse($this->startDate)->startOfDay();
        $endDate = Carbon::parse($this->endDate)->endOfDay();
        $turnoIds = $turnos->pluck('id');

        $countsByTurno = HojaEjecucion::whereIn('turno_id', $turnoIds)
            ->whereNotNull('finalizado_en')
            ->whereBetween('finalizado_en', [$startDate, $endDate])
            ->when($this->hojaChequeoId, fn ($q) => $q->where('hoja_chequeo_id', $this->hojaChequeoId))
            ->select('turno_id', DB::raw('COUNT(*) as total'))
            ->groupBy('turno_id')
            ->pluck('total', 'turno_id');

        $labels = [];
        $counts = [];

        foreach ($turnos as $turno) {
            $labels[] = $turno->nombre;
            $counts[] = (int) ($countsByTurno[$turno->id] ?? 0);
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
        $startDate = Carbon::parse($this->startDate)->startOfDay();
        $endDate = Carbon::parse($this->endDate)->endOfDay();
        $turnoIds = $turnos->pluck('id');

        $ejecucionCounts = HojaEjecucion::whereIn('turno_id', $turnoIds)
            ->whereNotNull('finalizado_en')
            ->whereBetween('finalizado_en', [$startDate, $endDate])
            ->when($this->hojaChequeoId, fn ($q) => $q->where('hoja_chequeo_id', $this->hojaChequeoId))
            ->select('turno_id', DB::raw('COUNT(*) as total'))
            ->groupBy('turno_id')
            ->pluck('total', 'turno_id');

        $respuestasByTurnoAndOption = DB::table('hoja_fila_respuestas as hfr')
            ->join('hoja_ejecucions as he', 'hfr.hoja_ejecucion_id', '=', 'he.id')
            ->whereIn('he.turno_id', $turnoIds)
            ->whereNotNull('he.finalizado_en')
            ->whereBetween('he.finalizado_en', [$startDate, $endDate])
            ->whereNotNull('hfr.answer_option_id')
            ->when($this->hojaChequeoId, fn ($q) => $q->where('he.hoja_chequeo_id', $this->hojaChequeoId))
            ->select('he.turno_id', 'hfr.answer_option_id', DB::raw('COUNT(*) as total'))
            ->groupBy('he.turno_id', 'hfr.answer_option_id')
            ->get();

        $optionCounts = [];
        foreach ($respuestasByTurnoAndOption as $row) {
            $optionCounts[$row->turno_id][$row->answer_option_id] = (int) $row->total;
        }

        $stats = [];
        foreach ($turnos as $turno) {
            $totalEjecuciones = (int) ($ejecucionCounts[$turno->id] ?? 0);

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

            $respuestasByOption = $optionCounts[$turno->id] ?? [];
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

    /**
     * Flattened CC → equipo data for the compact print summary.
     */
    public function getPrintDataProperty(): array
    {
        $result = [];

        foreach ($this->cumplimientoPorCentroCosto as $cc) {
            $maxWorkingDays = 0;
            $equipoMap = [];

            foreach ($cc['turnos'] as $turno) {
                if ($turno['working_days'] > $maxWorkingDays) {
                    $maxWorkingDays = $turno['working_days'];
                }
                foreach ($turno['equipos_detail'] as $eq) {
                    $tag = $eq['tag'];
                    if (! isset($equipoMap[$tag])) {
                        $equipoMap[$tag] = 0;
                    }
                    $equipoMap[$tag] = max($equipoMap[$tag], $eq['dias_revisados']);
                }
            }

            $result[] = [
                'nombre' => $cc['centro_costo'],
                'dias_operacion' => $maxWorkingDays,
                'equipos' => array_values(array_map(
                    fn ($tag, $dias) => ['tag' => $tag, 'dias' => $dias],
                    array_keys($equipoMap),
                    array_values($equipoMap),
                )),
                'cumplimiento' => $cc['percentage'],
                'total_actual' => $cc['total_actual'],
                'total_expected' => $cc['total_expected'],
            ];
        }

        return $result;
    }
}
