<?php

namespace App\Livewire;

use App\Models\AnswerType;
use App\Models\HojaChequeo;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class CreateHojaChequeoItems extends Component
{
    public array $columnas = [];

    public array $filas = [];

    public array $valores = [];

    public array $answerTypes = [];

    public ?int $sourceHojaChequeoId = null;

    public bool $isEditMode = false;

    public bool $hasNewItems = false;

    public array $removedFilaDbIds = [];

    public array $removedColumnaDbIds = [];

    public function mount(?int $hojaChequeoId = null): void
    {
        $this->answerTypes = AnswerType::query()
            ->select('id', 'label', 'key')
            ->get()
            ->toArray();

        if ($hojaChequeoId) {
            // Edit mode: load existing data
            $this->sourceHojaChequeoId = $hojaChequeoId;
            $this->isEditMode = true;
            $this->loadExistingData($hojaChequeoId);
        } else {
            // Create mode: initialize with defaults
            $this->addDefaultColumns();
            $this->addFila();
        }
    }

    protected function loadExistingData(int $hojaChequeoId): void
    {
        $hojaChequeo = HojaChequeo::with(['columnas', 'filas.valores'])
            ->findOrFail($hojaChequeoId);

        foreach ($hojaChequeo->columnas as $columna) {
            $columnId = 'col_'.uniqid();
            $this->columnas[$columnId] = [
                'id' => $columnId,
                'db_id' => $columna->id,
                'key' => $columna->key,
                'label' => $columna->label,
                'is_fixed' => $columna->is_fixed,
                'order' => $columna->order,
            ];
        }

        foreach ($hojaChequeo->filas as $fila) {
            $filaId = 'row_'.uniqid();
            $this->filas[$filaId] = [
                'id' => $filaId,
                'db_id' => $fila->id,
                'answer_type_id' => $fila->answer_type_id,
                'categoria' => $fila->categoria,
                'order' => $fila->order,
            ];

            $this->valores[$filaId] = [];
            foreach ($this->columnas as $columnId => $columnaData) {
                $valor = $fila->valores
                    ->first(fn ($v) => $v->hoja_columna_id === $columnaData['db_id']);

                $this->valores[$filaId][$columnId] = $valor?->valor ?? '';
            }
        }
    }

    protected function addDefaultColumns(): void
    {
        $defaultColumns = [
            'Item',
            'Frecuencia',
            'Método',
            'Criterio',
            'Observaciones',
        ];

        foreach ($defaultColumns as $label) {
            $this->addColumnaWithLabel($label, isFixed: true);
        }
    }

    public function addColumna(): void
    {
        $this->hasNewItems = true;
        $this->addColumnaWithLabel('');
    }

    protected function addColumnaWithLabel(string $label, bool $isFixed = false): void
    {
        $order = count($this->columnas);
        $columnId = 'col_'.uniqid();

        $this->columnas[$columnId] = [
            'id' => $columnId,
            'key' => Str::slug($label),
            'label' => $label,
            'is_fixed' => $isFixed,
            'order' => $order,
        ];
    }

    public function updatedColumnas(mixed $value, string $key): void
    {
        if (str_ends_with($key, '.label')) {
            $columnId = explode('.', $key)[0];
            $label = $this->columnas[$columnId]['label'] ?? '';
            $this->columnas[$columnId]['key'] = Str::slug($label);
        }
    }

    public function removeColumna(string $columnId): void
    {
        if (isset($this->columnas[$columnId]['db_id'])) {
            $this->removedColumnaDbIds[] = $this->columnas[$columnId]['db_id'];
        }

        unset($this->columnas[$columnId]);

        foreach ($this->filas as $filaId => $fila) {
            unset($this->valores[$filaId][$columnId]);
        }

        $this->reorderColumnas();
    }

    public function addFila(): void
    {
        $this->hasNewItems = true;
        $order = count($this->filas);
        $filaId = 'row_'.uniqid();

        $this->filas[$filaId] = [
            'id' => $filaId,
            'answer_type_id' => null,
            'categoria' => 'limpieza',
            'order' => $order,
        ];

        $this->valores[$filaId] = [];
        foreach ($this->columnas as $columnId => $column) {
            $this->valores[$filaId][$columnId] = '';
        }
    }

    public function removeFila(string $filaId): void
    {
        if (isset($this->filas[$filaId]['db_id'])) {
            $this->removedFilaDbIds[] = $this->filas[$filaId]['db_id'];
        }

        unset($this->filas[$filaId]);
        unset($this->valores[$filaId]);

        $this->reorderFilas();
    }

    #[On('hoja-chequeo-created')]
    public function saveItems(int $hojaChequeoId): void
    {
        try {
            $hojaChequeo = HojaChequeo::findOrFail($hojaChequeoId);
            $this->createStructure($hojaChequeo);
            $this->dispatch('create-hoja-chequeo-items-created');
        } catch (\Exception $e) {
            $this->dispatch('create-hoja-chequeo-items-failed');
        }
    }

    protected function createStructure(HojaChequeo $hojaChequeo): void
    {
        $columnaMapping = [];
        foreach ($this->columnas as $columnId => $columnaData) {
            $columna = $hojaChequeo->columnas()->create([
                'key' => $columnaData['key'],
                'label' => $columnaData['label'],
                'is_fixed' => $columnaData['is_fixed'],
                'order' => $columnaData['order'],
            ]);
            $columnaMapping[$columnId] = $columna->id;
        }

        foreach ($this->filas as $filaId => $filaData) {
            $fila = $hojaChequeo->filas()->create([
                'answer_type_id' => $filaData['answer_type_id'],
                'categoria' => $filaData['categoria'],
                'order' => $filaData['order'],
            ]);

            foreach ($this->valores[$filaId] ?? [] as $columnId => $valor) {
                if (isset($columnaMapping[$columnId]) && ! empty($valor)) {
                    $fila->valores()->create([
                        'hoja_columna_id' => $columnaMapping[$columnId],
                        'valor' => $valor,
                    ]);
                }
            }
        }
    }

    #[On('check-has-new-items')]
    public function replyHasNewItems(): void
    {
        $this->dispatch('has-new-items-result', hasNew: $this->hasNewItems);
    }

    #[On('update-items-in-place')]
    public function updateItemsInPlace(int $hojaChequeoId): void
    {
        $this->updateStructure($hojaChequeoId);
        $this->dispatch('hoja-chequeo-simple-updated');
    }

    protected function updateStructure(int $hojaChequeoId): void
    {
        $hojaChequeo = HojaChequeo::findOrFail($hojaChequeoId);

        // Delete removed columnas (cascade will handle their valores)
        if ($this->removedColumnaDbIds) {
            $hojaChequeo->columnas()->whereIn('id', $this->removedColumnaDbIds)->delete();
        }

        // Delete removed filas (cascade will handle their valores)
        if ($this->removedFilaDbIds) {
            $hojaChequeo->filas()->whereIn('id', $this->removedFilaDbIds)->delete();
        }

        // Update existing columnas
        foreach ($this->columnas as $columnaData) {
            if (! isset($columnaData['db_id'])) {
                continue;
            }

            $hojaChequeo->columnas()->where('id', $columnaData['db_id'])->update([
                'key' => $columnaData['key'],
                'label' => $columnaData['label'],
                'order' => $columnaData['order'],
            ]);
        }

        // Update existing filas and their valores
        foreach ($this->filas as $filaId => $filaData) {
            if (! isset($filaData['db_id'])) {
                continue;
            }

            $fila = $hojaChequeo->filas()->where('id', $filaData['db_id'])->first();
            if (! $fila) {
                continue;
            }

            $fila->update([
                'answer_type_id' => $filaData['answer_type_id'],
                'categoria' => $filaData['categoria'],
                'order' => $filaData['order'],
            ]);

            foreach ($this->valores[$filaId] ?? [] as $columnId => $valor) {
                $columnaDbId = $this->columnas[$columnId]['db_id'] ?? null;
                if (! $columnaDbId) {
                    continue;
                }

                if (empty($valor)) {
                    $fila->valores()->where('hoja_columna_id', $columnaDbId)->delete();
                } else {
                    $fila->valores()->updateOrCreate(
                        ['hoja_columna_id' => $columnaDbId],
                        ['valor' => $valor]
                    );
                }
            }
        }
    }

    protected function reorderColumnas(): void
    {
        $order = 0;
        foreach ($this->columnas as $columnId => $column) {
            $this->columnas[$columnId]['order'] = $order++;
        }
    }

    protected function reorderFilas(): void
    {
        $order = 0;
        foreach ($this->filas as $filaId => $fila) {
            $this->filas[$filaId]['order'] = $order++;
        }
    }

    public function render(): View
    {
        return view('livewire.create-hoja-chequeo-items');
    }
}
