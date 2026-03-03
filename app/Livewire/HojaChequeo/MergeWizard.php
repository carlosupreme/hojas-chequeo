<?php

namespace App\Livewire\HojaChequeo;

use App\Filament\Resources\HojaChequeos\HojaChequeoResource;
use App\Models\HojaChequeo;
use App\Services\HojaChequeoMergeService;
use Illuminate\View\View;
use Livewire\Component;

class MergeWizard extends Component
{
    public int $equipoId;

    public bool $isOpen = false;

    public int $currentStep = 1;

    public array $sourceVersionIds = [];

    public array $columnDiff = [];

    public array $rowDiff = [];

    public ?string $errorMessage = null;

    public function open(): void
    {
        $this->reset(['currentStep', 'sourceVersionIds', 'columnDiff', 'rowDiff', 'errorMessage']);
        $this->currentStep = 1;
        $this->isOpen = true;
    }

    public function close(): void
    {
        $this->isOpen = false;
    }

    public function getVersionsProperty(): \Illuminate\Database\Eloquent\Collection
    {
        return HojaChequeo::where('equipo_id', $this->equipoId)
            ->withCount('chequeos')
            ->orderBy('version')
            ->get();
    }

    public function loadSources(): void
    {
        if (count($this->sourceVersionIds) < 2) {
            $this->errorMessage = 'Debes seleccionar al menos 2 versiones para fusionar.';

            return;
        }

        $this->errorMessage = null;
        $this->buildColumnDiff();
        $this->buildRowDiff();
        $this->currentStep = 2;
    }

    public function buildColumnDiff(): void
    {
        $hojas = HojaChequeo::with(['columnas'])->whereIn('id', $this->sourceVersionIds)->get();

        // Collect all unique column keys across all versions
        $allColumns = [];
        foreach ($hojas as $hoja) {
            foreach ($hoja->columnas as $columna) {
                $key = $columna->key;
                if (! isset($allColumns[$key])) {
                    $allColumns[$key] = [
                        'key' => $key,
                        'included' => true,
                        'label' => $columna->label,
                        'is_fixed' => $columna->is_fixed,
                        'order' => $columna->order,
                        'versions' => [],
                    ];
                }
                $allColumns[$key]['versions'][$hoja->id] = $columna->label;
            }
        }

        // Sort by order
        uasort($allColumns, fn ($a, $b) => $a['order'] <=> $b['order']);

        $this->columnDiff = array_values($allColumns);
    }

    public function buildRowDiff(): void
    {
        $hojas = HojaChequeo::with(['filas.valores.hojaColumna', 'filas.answerType'])
            ->whereIn('id', $this->sourceVersionIds)
            ->get()
            ->keyBy('id');

        // Find all unique order positions across all versions
        $allOrders = collect();
        foreach ($hojas as $hoja) {
            $allOrders = $allOrders->merge($hoja->filas->pluck('order'));
        }
        $allOrders = $allOrders->unique()->sort()->values();

        $rows = [];
        foreach ($allOrders as $order) {
            $versions = [];
            $firstFila = null;

            foreach ($hojas as $hojaId => $hoja) {
                $fila = $hoja->filas->firstWhere('order', $order);
                if ($fila) {
                    if (! $firstFila) {
                        $firstFila = $fila;
                    }
                    $valores = [];
                    foreach ($fila->valores as $valor) {
                        $valores[$valor->hojaColumna->key] = $valor->valor;
                    }
                    $versions[$hojaId] = [
                        'fila_id' => $fila->id,
                        'answer_type_id' => $fila->answer_type_id,
                        'categoria' => $fila->categoria,
                        'answer_type_label' => $fila->answerType?->label,
                        'valores' => $valores,
                    ];
                }
            }

            // Default source: pick first available version
            $defaultSource = array_key_first($versions);

            $rows[] = [
                'order' => $order,
                'included' => true,
                'source_hoja_id' => $defaultSource,
                'answer_type_id' => $firstFila?->answer_type_id,
                'categoria' => $firstFila?->categoria,
                'versions' => $versions,
            ];
        }

        $this->rowDiff = $rows;
    }

    public function goToStep(int $step): void
    {
        if ($step === 3) {
            // Sync row data from selected source
            foreach ($this->rowDiff as $i => $row) {
                $sourceId = $row['source_hoja_id'];
                if (isset($row['versions'][$sourceId])) {
                    $this->rowDiff[$i]['answer_type_id'] = $row['versions'][$sourceId]['answer_type_id'];
                    $this->rowDiff[$i]['categoria'] = $row['versions'][$sourceId]['categoria'];
                }
            }
        }
        $this->currentStep = $step;
    }

    public function executeMerge(): void
    {
        $this->errorMessage = null;

        try {
            $includedCols = array_filter($this->columnDiff, fn ($c) => $c['included']);
            $includedColKeys = array_column(array_values($includedCols), 'key');

            // Build columnSpec
            $columnSpec = array_map(function ($col) {
                return [
                    'key' => $col['key'],
                    'label' => $col['label'],
                    'is_fixed' => $col['is_fixed'] ?? false,
                    'order' => $col['order'],
                    'included' => $col['included'],
                ];
            }, $this->columnDiff);

            // Build rowSpec with resolved valores from chosen source
            $rowSpec = array_map(function ($row) use ($includedColKeys) {
                $sourceId = $row['source_hoja_id'];
                $sourceData = $row['versions'][$sourceId] ?? [];
                $rawValores = $sourceData['valores'] ?? [];

                // Only keep valores for included columns
                $valores = [];
                foreach ($includedColKeys as $key) {
                    $valores[$key] = $rawValores[$key] ?? '';
                }

                return [
                    'order' => $row['order'],
                    'included' => $row['included'],
                    'source_hoja_id' => $sourceId,
                    'answer_type_id' => $sourceData['answer_type_id'] ?? $row['answer_type_id'],
                    'categoria' => $sourceData['categoria'] ?? $row['categoria'],
                    'valores' => $valores,
                ];
            }, $this->rowDiff);

            app(HojaChequeoMergeService::class)->merge(
                $this->equipoId,
                $this->sourceVersionIds,
                $columnSpec,
                $rowSpec
            );

            $this->redirect(HojaChequeoResource::getUrl('index'));

        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al fusionar: '.$e->getMessage();
        }
    }

    public function getTotalEjecuciones(): int
    {
        return HojaChequeo::whereIn('id', $this->sourceVersionIds)->withCount('chequeos')->get()->sum('chequeos_count');
    }

    public function render(): View
    {
        return view('livewire.hoja-chequeo.merge-wizard');
    }
}
