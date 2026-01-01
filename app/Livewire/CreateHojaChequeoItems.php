<?php

namespace App\Livewire;

use App\Models\AnswerType;
use App\Models\HojaChequeo;
use Livewire\Attributes\On;
use Livewire\Component;

class CreateHojaChequeoItems extends Component
{
    public array $columnas = [];

    public array $filas = [];

    public array $valores = [];

    public array $answerTypes = [];

    public function mount(): void
    {
        $this->answerTypes = AnswerType::query()
            ->select('id', 'label', 'key')
            ->get()
            ->toArray();

        // Initialize with default fixed columns
        $this->addDefaultColumns();

        // Initialize with one row
        $this->addFila();
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
        $this->addColumnaWithLabel('', isFixed: false);
    }

    protected function addColumnaWithLabel(string $label, bool $isFixed = false): void
    {
        $order = count($this->columnas);
        $columnId = 'col_'.uniqid();

        $this->columnas[$columnId] = [
            'id' => $columnId,
            'key' => \Illuminate\Support\Str::slug($label),
            'label' => $label,
            'is_fixed' => $isFixed,
            'order' => $order,
        ];
    }

    public function updatedColumnas(mixed $value, string $key): void
    {
        // Auto-generate key from label when label changes
        if (str_ends_with($key, '.label')) {
            $columnId = explode('.', $key)[0];
            $label = $this->columnas[$columnId]['label'] ?? '';
            $this->columnas[$columnId]['key'] = \Illuminate\Support\Str::slug($label);
        }
    }

    public function removeColumna(string $columnId): void
    {
        unset($this->columnas[$columnId]);

        // Remove valores for this column
        foreach ($this->filas as $filaId => $fila) {
            unset($this->valores[$filaId][$columnId]);
        }

        // Reorder remaining columns
        $this->reorderColumnas();
    }

    public function addFila(): void
    {
        $order = count($this->filas);
        $filaId = 'row_'.uniqid();

        $this->filas[$filaId] = [
            'id' => $filaId,
            'answer_type_id' => null,
            'categoria' => 'limpieza',
            'order' => $order,
        ];

        // Initialize valores for this row
        $this->valores[$filaId] = [];
        foreach ($this->columnas as $columnId => $column) {
            $this->valores[$filaId][$columnId] = '';
        }
    }

    public function removeFila(string $filaId): void
    {
        unset($this->filas[$filaId]);
        unset($this->valores[$filaId]);

        // Reorder remaining filas
        $this->reorderFilas();
    }

    #[On('hoja-chequeo-created')]
    public function saveItems(int $hojaChequeoId): void
    {
        try {
            $hojaChequeo = HojaChequeo::findOrFail($hojaChequeoId);

            // Create columnas
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

            // Create filas and valores
            foreach ($this->filas as $filaId => $filaData) {
                $fila = $hojaChequeo->filas()->create([
                    'answer_type_id' => $filaData['answer_type_id'],
                    'categoria' => $filaData['categoria'],
                    'order' => $filaData['order'],
                ]);

                // Create valores for this fila
                foreach ($this->valores[$filaId] ?? [] as $columnId => $valor) {
                    if (isset($columnaMapping[$columnId]) && ! empty($valor)) {
                        $fila->valores()->create([
                            'hoja_columna_id' => $columnaMapping[$columnId],
                            'valor' => $valor,
                        ]);
                    }
                }
            }

            $this->dispatch('create-hoja-chequeo-items-created');
        } catch (\Exception $e) {
            $this->dispatch('create-hoja-chequeo-items-failed');
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

    public function render()
    {
        return view('livewire.create-hoja-chequeo-items');
    }
}
