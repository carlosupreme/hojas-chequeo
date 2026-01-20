<?php

namespace App\Livewire;

use App\Models\HojaChequeo;
use App\Models\HojaEjecucion;
use Livewire\Attributes\On;
use Livewire\Component;

class ChequeoItems extends Component
{
    public $items = [];

    public $columnas = [];

    public $form = [];

    public $hojaId;

    public function mount(HojaChequeo $hoja, ?HojaEjecucion $ejecucion = null): void
    {
        $this->hojaId = $hoja->id;
        $hoja->load([
            'columnas',
            'filas.answerType.answerOptions',
            'filas.valores.hojaColumna',
        ]);

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

    #[On('dailyCheckCreated')]
    public function save(int $id): void {}

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
