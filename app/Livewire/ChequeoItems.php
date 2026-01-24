<?php

namespace App\Livewire;

use App\Models\HojaChequeo;
use App\Models\HojaEjecucion;
use App\Models\HojaFilaRespuesta;
use Livewire\Attributes\On;
use Livewire\Component;

class ChequeoItems extends Component
{
    public $items = [];

    public $columnas = [];

    public $form = [];

    public $hojaId;

    public $filas;

    public bool $readOnly = false;

    public function mount(HojaChequeo $hoja, ?HojaEjecucion $ejecucion = null, bool $readOnly = false): void
    {
        $this->readOnly = $readOnly;
        $this->hojaId = $hoja->id;
        $hoja->load([
            'columnas',
            'filas.answerType.answerOptions',
            'filas.valores.hojaColumna',
        ]);

        $this->filas = $hoja->filas;

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
    }

    #[On('hoja-ejecucion-saved')]
    public function save($hojaEjecucionId): void
    {
        $filaIds = array_keys($this->form);
        $markAsCompleted = true;

        foreach ($filaIds as $filaId) {
            $fila = $this->filas->find($filaId);
            $type = $fila->answerType?->key;
            $value = $this->form[$filaId];

            if (is_null($value)) {
                $markAsCompleted = false;
            }

            HojaFilaRespuesta::updateOrCreate([
                'hoja_ejecucion_id' => $hojaEjecucionId,
                'hoja_fila_id' => $filaId,
            ], [
                'answer_option_id' => $type === 'icon_set' ? $value : null,
                'numeric_value' => $type === 'number' && is_numeric($value) ? floatval($value) : null,
                'text_value' => $type === 'text' ? $value : null,
                'boolean_value' => $type === 'boolean' && is_bool($value) ? $value : null,
            ]);
        }

        if ($markAsCompleted) {
            HojaEjecucion::find($hojaEjecucionId)->update([
                'finalizado_en' => now(),
            ]);
        }

        $this->dispatch('hoja-fila-respuesta-items-created');
    }

    /**
     * Whenever any nested key in `form` changes (e.g. form.123), dispatch a browser event
     * so the Blade/JS side can show feedback + animations.
     */
    public function updatedForm($value, $key): void
    {
        $this->dispatch('chequeo-form-updated', key: $key, value: $value);
    }

    public function render()
    {
        return view('livewire.chequeo-items');
    }
}
