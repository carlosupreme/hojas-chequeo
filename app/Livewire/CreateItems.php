<?php

namespace App\Livewire;

use App\Models\Alerta;
use App\Models\Item;
use App\Models\Simbologia;
use Filament\Notifications\Notification;
use Livewire\Attributes\On;
use Livewire\Component;

class CreateItems extends Component
{
    public $columns    = [];
    public $rows       = [];
    public $categories = [];

    public $alerts = [];

    public $editingCell   = null;
    public $newColumnName = '';

    public function mount() {
        $this->init();
    }

    private function init(): void {
        $defaultColumns = ['Item de chequeo', 'Frecuencia', 'Metodo de chequeo', 'Criterio de determinacion', 'Observaciones'];
        $this->columns = $defaultColumns;
        $this->addRow();
    }

    public function addColumn(): void {
        $this->newColumnName = 'Columna ' . (count($this->columns) + 1);
        $this->columns[] = $this->newColumnName;
        $this->updateRows();
    }

    public function addRow(): void {
        $this->rows[] = array_fill(0, count($this->columns), '');
        $this->alerts[] = [];
        $this->categories[] = 'limpieza'; // Valor por defecto
    }

    public function removeColumn($index) {
        unset($this->columns[$index]);
        $this->columns = array_values($this->columns);
        foreach ($this->rows as &$row) {
            unset($row[$index]);
            $row = array_values($row);
        }
    }

    public function removeRow($index): void {
        unset($this->rows[$index]);
        $this->rows = array_values($this->rows);
        unset($this->alerts[$index]);
        $this->alerts = array_values($this->alerts);
        unset($this->categories[$index]);
        $this->categories = array_values($this->categories);
    }

    public function updateCategory($index, $category): void {
        $this->categories[$index] = $category;
    }

    public function addAlert($index, $selectedStatus, $customText, $operador): void {
        if (is_null($selectedStatus)) {
            $this->alerts[$index] = [
                'customText' => $customText,
                'operador'   => $operador ? "$operador" : null
            ];
        } else {
            $this->alerts[$index] = [
                'selectedStatus' => $selectedStatus,
                'operador'       => $operador ? "$operador" : null
            ];
        }

        $this->dispatch('close-modal', id: 'addAlertModal');
        Notification::make()
                    ->title('Alerta establecida correctamente')
                    ->success()
                    ->send();
    }

    private function updateRows() {
        $this->rows = array_map(function ($row) {
            return array_pad($row, count($this->columns), '');
        }, $this->rows);

        $this->alerts = array_map(function ($alert) {
            return array_pad($alert, count($this->columns), '');
        }, $this->alerts);
    }

    public function updateCell($rowIndex, $colIndex, $value = null) {
        if ($value === null) {
            $this->editingCell = $this->editingCell && $this->editingCell['rowIndex'] === $rowIndex && $this->editingCell['colIndex'] === $colIndex
                ? null
                : ['rowIndex' => $rowIndex, 'colIndex' => $colIndex];
        } else {
            $this->rows[$rowIndex][$colIndex] = $value;
            $this->editingCell = null;
        }
    }

    public function startEditing($rowIndex, $colIndex) {
        $this->editingCell = ['rowIndex' => $rowIndex, 'colIndex' => $colIndex];
    }

    public function cancelEditing() {
        $this->editingCell = null;
    }

    public function updateColumnName($index, $name) {
        $this->columns[$index] = $name;
    }

    #[On('checkSheetCreated')]
    public function createItems(int $id): void {
        collect($this->convertRowsToJsons())->map(function ($properties, $i) {
            return [
                'alert'      => $this->alerts[$i],
                'properties' => $properties,
                'categoria'  => $this->categories[$i]
            ];
        })->each(function ($itemData) use ($id) {
            $checkSheetItem = Item::create([
                'hoja_chequeo_id' => $id,
                'valores'         => $itemData['properties'],
                'categoria'       => $itemData['categoria']
            ]);

            if (
                ($itemData['alert']['selectedStatus'] ?? null) === null
                &&
                ($itemData['alert']['customText'] ?? null) === null
            ) {
                return;
            }

            Alerta::create([
                'item_id'       => $checkSheetItem->id,
                'simbologia_id' => $itemData['alert']['selectedStatus'] ?? null,
                'valor'         => $itemData['alert']['customText'] ?? null,
                'operador'      => $itemData['alert']['operador'] ?? null,
                'contador'      => 0
            ]);
        });

        $this->dispatch('checkSheetItemsCreated');
    }

    private function convertRowsToJsons(): array {
        return array_map(function ($row) {
            return array_combine($this->columns, $row);
        }, $this->rows);
    }

    public function render() {
        $statuses = Simbologia::select('id', 'nombre', 'icono', 'color')->get();
        return view('livewire.create-items', compact('statuses'));
    }
}
