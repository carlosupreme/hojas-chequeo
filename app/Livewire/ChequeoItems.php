<?php

namespace App\Livewire;

use Exception;
use App\Models\Alerta;
use App\Models\ItemChequeoDiario;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;

class ChequeoItems extends Component
{
    public $items = [];

    public $headers = [];

    public $checks = [];

    public $customInputs = [];

    public function mount(Collection $items, ?array $defaultValues = []): void
    {
        if ($items->isEmpty()) {
            return;
        }

        $firstItem = $items->first();
        if (is_null($firstItem->valores)) {
            return;
        }

        $this->headers = array_keys($firstItem->valores);

        // Process items more efficiently to reduce memory usage
        $this->items = $items->map(function ($item) {
            return array_merge(['id' => $item->id], $item->valores);
        })->toArray();

        // Initialize checks and customInputs more efficiently
        if ($defaultValues) {
            $this->checks = $defaultValues['checks'] ?? [];
            $this->customInputs = $defaultValues['custom'] ?? [];
        } else {
            foreach ($this->items as $item) {
                $this->checks[$item['id']] = null;
                $this->customInputs[$item['id']] = null;
            }
        }
    }

    #[On('dailyCheckCreated')]
    public function save(int $id): void
    {
        try {
            // Batch insert for better performance
            $itemsToInsert = [];
            $itemIds = array_column($this->items, 'id');

            // Get all alerts at once to prevent N+1 queries
            $alertas = Alerta::whereIn('item_id', $itemIds)
                ->get()
                ->keyBy('item_id');

            foreach ($this->items as $item) {
                $itemId = $item['id'];
                $notes = $this->customInputs[$itemId] ?? null;
                $checkStatus = $this->checks[$itemId] ?? null;

                $itemsToInsert[] = [
                    'chequeo_diario_id' => $id,
                    'item_id' => $itemId,
                    'valor' => $notes,
                    'simbologia_id' => $checkStatus,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Process alerts efficiently
                $alerta = $alertas->get($itemId);
                if (! $alerta) {
                    continue;
                }

                $shouldIncrement = $this->shouldIncrementAlert($alerta, $checkStatus, $notes);
                if ($shouldIncrement) {
                    $alerta->increment('contador');
                }
            }

            // Batch insert all items
            ItemChequeoDiario::insert($itemsToInsert);

            $this->dispatch('dailyCheckItemsSaved');
        } catch (Exception $e) {
            $this->dispatch('dailyCheckItemsFailed', $id);
        }
    }

    private function shouldIncrementAlert(Alerta $alerta, ?int $checkStatus, ?string $notes): bool
    {
        return
            (! is_null($alerta->simbologia_id) && ! is_null($checkStatus) && $alerta->simbologia_id == $checkStatus) ||
            (! is_null($alerta->valor) && ! is_null($notes) && $alerta->valor == $notes) ||
            (! is_null($alerta->valor) && ! is_null($notes) && $alerta->operador == '<' && intval($notes) < intval($alerta->valor)) ||
            (! is_null($alerta->valor) && ! is_null($notes) && $alerta->operador == '>' && intval($notes) > intval($alerta->valor)) ||
            ($checkStatus == 2);
    }

    #[On('dailyCheckItemsStatusChanged')]
    public function onStatusChange(array $data): void
    {
        if (is_null($data['statusId'])) {
            $this->checks[$data['itemId']] = null;
            $this->customInputs[$data['itemId']] = $data['customText'];
        } else {
            $this->customInputs[$data['itemId']] = null;
            $this->checks[$data['itemId']] = $data['statusId'];
        }
    }

    public function validateItems(): bool
    {
        foreach ($this->items as $item) {
            $itemId = $item['id'];

            $hasCheck = isset($this->checks[$itemId]) && ! is_null($this->checks[$itemId]);
            $hasCustomInput = isset($this->customInputs[$itemId]) && ! is_null($this->customInputs[$itemId]);

            if (! $hasCheck && ! $hasCustomInput) {
                return false;
            }
        }

        return true;
    }

    #[On('requestForValidItems')]
    public function checkValidItems(): void
    {
        $this->dispatch('validItems', [
            'checks' => $this->checks,
            'customInputs' => $this->customInputs,
        ]);
    }

    public function render()
    {
        return view('livewire.chequeo-items');
    }
}
