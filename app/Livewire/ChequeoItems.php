<?php

namespace App\Livewire;

use App\Models\AnswerOption;
use App\Models\HojaChequeo;
use App\Models\HojaEjecucion;
use App\Models\HojaFilaRespuesta;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class ChequeoItems extends Component
{
    public $items = [];

    public $columnas = [];

    public $form = [];

    public $hojaId;

    public $filas;

    public array $filaTypes = [];

    public bool $readOnly = false;

    public ?int $ejecucionId = null;

    public int $answeredCount = 0;

    public int $totalCount = 0;

    public function mount(HojaChequeo $hoja, ?HojaEjecucion $ejecucion = null, bool $readOnly = false): void
    {
        $this->readOnly = $readOnly;
        $this->hojaId = $hoja->id;
        $this->ejecucionId = $ejecucion?->id;
        $hoja->load([
            'columnas',
            'filas.answerType.answerOptions',
            'filas.valores.hojaColumna',
        ]);

        $this->filas = $hoja->filas;
        $this->filaTypes = $hoja->filas->pluck('answerType.key', 'id')->toArray();

        $existingResponses = $ejecucion
            ? $ejecucion->respuestas()->get()->keyBy('hoja_fila_id')
            : collect();

        $this->columnas = $hoja->columnas->map(fn ($col) => [
            'key' => $col->key,
            'label' => $col->label,
        ]);

        $this->items = $hoja->filas->map(function ($fila) use ($existingResponses) {
            $cells = [];

            foreach ($fila->valores as $valor) {
                $cells[$valor->hojaColumna->key] = $valor->valor;
            }

            // 2. Extract the value based on the AnswerType
            $resp = $existingResponses->get($fila->id);
            $type = $fila->answerType?->key;

            $initialValue = match ($type) {
                'icon_set' => $resp?->answer_option_id,
                'number' => $resp?->numeric_value,
                'text' => $resp?->text_value,
                'boolean' => (bool) $resp?->boolean_value,
                default => null
            };

            // Initialize the form state
            $this->form[$fila->id] = $initialValue;

            return [
                'id' => $fila->id,
                'type_key' => $fila->answerType?->key,
                'options' => $fila->answerType?->answerOptions->map(fn ($o) => [
                    'id' => $o->id,
                    'label' => $o->label,
                    'icon' => $o->icon,
                    'color' => $o->color,
                ]) ?? [],
                'cells' => $cells,
            ];
        })->toArray();

        $this->recomputeProgress();
        $this->dispatch('progress-updated', answered: $this->answeredCount, total: $this->totalCount);
    }

    #[On('hoja-ejecucion-saved')]
    public function save(int $hojaEjecucionId, ?string $forcedFinalizadoEn = null): void
    {
        DB::transaction(function () use ($hojaEjecucionId, $forcedFinalizadoEn) {
            foreach (array_keys($this->form) as $filaId) {
                $type = $this->filaTypes[$filaId] ?? $this->filas?->find($filaId)?->answerType?->key;
                $value = $this->form[$filaId];

                // Skip nulls — real-time saves already persisted these values.
                // Overwriting with null would corrupt the autosaved state.
                if ($value === null) {
                    continue;
                }

                HojaFilaRespuesta::updateOrCreate([
                    'hoja_ejecucion_id' => $hojaEjecucionId,
                    'hoja_fila_id' => $filaId,
                ], [
                    'answer_option_id' => $type === 'icon_set' ? $value : null,
                    'numeric_value' => $type === 'number' && is_numeric($value) ? floatval($value) : null,
                    'text_value' => $type === 'text' ? $value : null,
                    'boolean_value' => $type === 'boolean' ? (bool) $value : null,
                ]);
            }

            // Use the DB as source of truth — $this->form can have stale nulls
            // for wire:model.blur inputs that weren't synced before submit.
            $totalFilas = count($this->items);
            $answeredCount = HojaFilaRespuesta::where('hoja_ejecucion_id', $hojaEjecucionId)
                ->where(function ($q) {
                    $q->whereNotNull('answer_option_id')
                        ->orWhereNotNull('numeric_value')
                        ->orWhereNotNull('text_value')
                        ->orWhereNotNull('boolean_value');
                })
                ->count();

            if ($totalFilas > 0 && $answeredCount >= $totalFilas) {
                $finalizadoEn = $forcedFinalizadoEn ? Carbon::parse($forcedFinalizadoEn) : now();
                HojaEjecucion::where('id', $hojaEjecucionId)->update(['finalizado_en' => $finalizadoEn]);
            }
        });

        $this->dispatch('hoja-fila-respuesta-items-created');
    }

    /**
     * Whenever a checklist item changes, notify the parent CreateChequeo component
     * so it can ensure a HojaEjecucion exists (autoSave) and get back the ID
     * to persist this specific respuesta.
     */
    public function updatedForm($value, $key): void
    {
        $this->recomputeProgress();
        $this->dispatch('progress-updated', answered: $this->answeredCount, total: $this->totalCount);
        $this->dispatch('chequeo-item-changed', filaId: (int) $key, value: $value);
    }

    #[On('ppm-activated')]
    public function bulkFillPpm(): void
    {
        $realizadoId = AnswerOption::where('key', 'realizado')->value('id');

        foreach ($this->items as $fila) {
            $filaId = $fila['id'];
            $this->form[$filaId] = match ($fila['type_key']) {
                'icon_set' => $realizadoId,
                'number' => 0,
                'text' => ' ',
                'boolean' => true,
                default => null,
            };
        }

        $this->recomputeProgress();
        $this->dispatch('progress-updated', answered: $this->answeredCount, total: $this->totalCount);

        if ($this->ejecucionId) {
            DB::transaction(function () {
                foreach ($this->items as $fila) {
                    $this->saveFilaRespuesta($this->ejecucionId, $fila['id'], $this->form[$fila['id']]);
                }
            });
        }
    }

    #[On('ppm-deactivated')]
    public function clearPpmFill(): void
    {
        foreach ($this->items as $fila) {
            $this->form[$fila['id']] = null;
        }

        $this->recomputeProgress();
        $this->dispatch('progress-updated', answered: $this->answeredCount, total: $this->totalCount);
    }

    private function recomputeProgress(): void
    {
        $this->totalCount = count($this->items);
        $this->answeredCount = collect($this->form)
            ->filter(fn ($v) => ! is_null($v))
            ->count();
    }

    /**
     * Called by the parent after it has autoSaved and confirmed the HojaEjecucion ID.
     * Saves (or updates) the specific fila respuesta.
     */
    #[On('chequeo-ejecucion-ensured')]
    public function onEjecucionEnsured(int $ejecucionId, int $filaId, mixed $value): void
    {
        $this->ejecucionId = $ejecucionId;
        $this->saveFilaRespuesta($ejecucionId, $filaId, $value);
    }

    private function saveFilaRespuesta(int $ejecucionId, int $filaId, mixed $value): void
    {
        $type = $this->filaTypes[$filaId] ?? $this->filas?->find($filaId)?->answerType?->key;

        HojaFilaRespuesta::updateOrCreate(
            [
                'hoja_ejecucion_id' => $ejecucionId,
                'hoja_fila_id' => $filaId,
            ],
            [
                'answer_option_id' => $type === 'icon_set' ? $value : null,
                'numeric_value' => $type === 'number' && is_numeric($value) ? floatval($value) : null,
                'text_value' => $type === 'text' ? $value : null,
                'boolean_value' => $type === 'boolean' ? (bool) $value : null,
            ]
        );
    }

    public function render()
    {
        return view('livewire.chequeo-items');
    }
}
