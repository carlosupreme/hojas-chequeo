<?php

namespace App\Services;

use App\Models\HojaChequeo;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class HojaChequeoHistoryService
{
    public function getEjecucionesByDateAndTurno(HojaChequeo $record, string $startDate, string $endDate): array
    {
        $ejecuciones = $this->getEjecuciones($record, $startDate, $endDate);
        $grouped = [];

        foreach ($ejecuciones as $ejecucion) {
            $date = $ejecucion->finalizado_en->format('Y-m-d');
            $turnoId = $ejecucion->turno_id;

            $grouped[$date][$turnoId] = $ejecucion;
        }

        return $grouped;
    }

    public function getEjecuciones(HojaChequeo $record, string $startDate, string $endDate): Collection
    {
        $endDate = Carbon::parse($endDate)->addDay()->format('Y-m-d');

        return $record->chequeos()
            ->with(['turno', 'user', 'respuestas.hojaFila', 'respuestas.answerOption'])
            ->whereBetween('finalizado_en', [$startDate, $endDate])
            ->whereNotNull('finalizado_en')
            ->orderBy('finalizado_en')
            ->get();
    }

    /**
     * Calculate sum and average of numeric values per fila using SQL aggregation
     * Returns array indexed by fila_id with ['suma' => float, 'promedio' => float]
     */
    public function getFilaAggregates(HojaChequeo $record, string $startDate, string $endDate, string $activeTab): array
    {
        // Build query based on active tab
        $query = \DB::table('hoja_ejecucions as he')
            ->join('hoja_fila_respuestas as hfr', 'he.id', '=', 'hfr.hoja_ejecucion_id')
            ->where('he.hoja_chequeo_id', $record->id)
            ->whereBetween('he.finalizado_en', [$startDate, $endDate])
            ->whereNotNull('he.finalizado_en')
            ->whereNotNull('hfr.numeric_value');

        // Filter by turno if not in compare mode
        if ($activeTab !== 'compare') {
            $query->where('he.turno_id', $activeTab);
        }

        // Aggregate by fila_id
        $results = $query
            ->select('hfr.hoja_fila_id')
            ->selectRaw('SUM(hfr.numeric_value) as suma')
            ->selectRaw('AVG(hfr.numeric_value) as promedio')
            ->selectRaw('COUNT(hfr.numeric_value) as count')
            ->groupBy('hfr.hoja_fila_id')
            ->get();

        // Convert to array indexed by fila_id
        return $results->mapWithKeys(function ($result) {
            return [
                $result->hoja_fila_id => [
                    'suma' => (float) $result->suma,
                    'promedio' => (float) $result->promedio,
                    'count' => (int) $result->count,
                ],
            ];
        })->toArray();
    }
}
