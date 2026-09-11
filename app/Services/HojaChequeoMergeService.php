<?php

namespace App\Services;

use App\Models\HojaChequeo;
use App\Models\HojaColumna;
use App\Models\HojaEjecucion;
use App\Models\HojaFila;
use App\Models\HojaFilaRespuesta;
use App\Models\HojaFilaValor;
use App\Models\Perfil;
use Illuminate\Support\Facades\DB;

class HojaChequeoMergeService
{
    /**
     * Merge multiple HojaChequeo versions into a new v1.
     *
     * @param  int[]  $sourceHojaIds  IDs of versions to merge (must belong to equipoId)
     * @param  array  $columnSpec  Array of ['key', 'label', 'is_fixed', 'order', 'included']
     * @param  array  $rowSpec  Array of ['order', 'included', 'source_hoja_id', 'answer_type_id', 'categoria', 'valores' => [colKey => valor]]
     */
    public function merge(int $equipoId, array $sourceHojaIds, array $columnSpec, array $rowSpec): HojaChequeo
    {
        return DB::transaction(function () use ($equipoId, $sourceHojaIds, $columnSpec, $rowSpec) {
            // Step 1: Create new HojaChequeo with temp version to avoid UNIQUE(equipo_id, version) conflict
            $tempVersion = HojaChequeo::where('equipo_id', $equipoId)->max('version') + 1;

            $newHoja = HojaChequeo::create([
                'equipo_id' => $equipoId,
                'version' => $tempVersion,
                'encendido' => true,
                'observaciones' => null,
            ]);

            // Step 2: Create HojaColumnas from included columnSpec entries
            $includedColumns = array_filter($columnSpec, fn ($col) => $col['included']);
            $newColumnasByKey = [];

            foreach ($includedColumns as $colData) {
                $columna = HojaColumna::create([
                    'hoja_chequeo_id' => $newHoja->id,
                    'key' => $colData['key'],
                    'label' => $colData['label'],
                    'is_fixed' => $colData['is_fixed'],
                    'order' => $colData['order'],
                ]);
                $newColumnasByKey[$colData['key']] = $columna;
            }

            // Step 3: Build $oldFilaToNewFila map and create HojaFilas + HojaFilaValors
            // Map: old_fila_id => new_fila_id (for all versions at each order position)
            $oldFilaToNewFila = [];

            // Load all source hojas with their filas
            $sourceHojas = HojaChequeo::with(['filas'])->whereIn('id', $sourceHojaIds)->get()->keyBy('id');

            $includedRows = array_filter($rowSpec, fn ($row) => $row['included']);

            foreach ($includedRows as $rowData) {
                $newFila = HojaFila::create([
                    'hoja_chequeo_id' => $newHoja->id,
                    'answer_type_id' => $rowData['answer_type_id'],
                    'categoria' => $rowData['categoria'],
                    'order' => $rowData['order'],
                ]);

                // Create HojaFilaValors for included columns
                foreach ($rowData['valores'] ?? [] as $colKey => $valor) {
                    if (isset($newColumnasByKey[$colKey]) && $valor !== null && $valor !== '') {
                        HojaFilaValor::create([
                            'hoja_fila_id' => $newFila->id,
                            'hoja_columna_id' => $newColumnasByKey[$colKey]->id,
                            'valor' => $valor,
                        ]);
                    }
                }

                // Map ALL old fila IDs at this order position (across all versions) to the new fila ID
                foreach ($sourceHojas as $sourceHoja) {
                    $oldFila = $sourceHoja->filas->firstWhere('order', $rowData['order']);
                    if ($oldFila) {
                        $oldFilaToNewFila[$oldFila->id] = $newFila->id;
                    }
                }
            }

            // Step 4: Re-point all ejecuciones to the new hoja
            HojaEjecucion::whereIn('hoja_chequeo_id', $sourceHojaIds)
                ->update(['hoja_chequeo_id' => $newHoja->id]);

            // Step 5: Re-point HojaFilaRespuestas for included rows (mass update, no observer)
            foreach ($oldFilaToNewFila as $oldFilaId => $newFilaId) {
                HojaFilaRespuesta::where('hoja_fila_id', $oldFilaId)
                    ->update(['hoja_fila_id' => $newFilaId]);
            }

            // Step 6: Delete old versions — CASCADE cleans up old filas/columnas/valores
            // and discarded rows' respuestas (those not in $oldFilaToNewFila)
            HojaChequeo::whereIn('id', $sourceHojaIds)->delete();

            // Step 7: Reset version to 1 — safe now that old v1 is gone
            $newHoja->update(['version' => 1]);

            // Step 8: Update Perfil.hoja_ids
            $this->updatePerfilAccess($sourceHojaIds, $newHoja->id);

            return $newHoja->fresh();
        });
    }

    /**
     * Update Perfil.hoja_ids: remove old IDs, add new ID for any perfil that had access.
     */
    public function updatePerfilAccess(array $oldHojaIds, int $newHojaId): void
    {
        $oldHojaIds = array_map('intval', $oldHojaIds);
        $perfiles = Perfil::where('acceso_total', false)->get();

        foreach ($perfiles as $perfil) {
            $currentIds = array_map('intval', $perfil->hoja_ids ?? []);
            $hadAccess = count(array_intersect($currentIds, $oldHojaIds)) > 0;

            if (! $hadAccess) {
                continue;
            }

            $newIds = array_values(array_diff($currentIds, $oldHojaIds));
            if (! in_array($newHojaId, $newIds, true)) {
                $newIds[] = $newHojaId;
            }

            $perfil->update(['hoja_ids' => $newIds]);
        }
    }
}
