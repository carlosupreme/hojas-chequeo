<?php

namespace App\Livewire;

use App\Models\Alerta;
use App\Models\ItemChequeoDiario;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;

class ChequeoItems extends Component
{
    public $items        = [];
    public $headers      = [];
    public $checks       = [];
    public $customInputs = [];

    public function mount(Collection $items, ?array $defaultValues = []): void {
        if (!$items->isEmpty() && !is_null($items->first()->valores)) {
            $this->headers = array_keys($items->first()->valores);
            $this->items = $items->map(function ($item) {
                $arr = [];
                $arr['id'] = $item->id;

                foreach ($item->valores as $property => $value) {
                    $arr[$property] = $value;
                }

                return $arr;
            });

            foreach ($this->items as $item) {
                $this->checks[$item['id']] = $defaultValues ? $defaultValues['checks'][$item['id']] : null;
                $this->customInputs[$item['id']] = $defaultValues ? $defaultValues['custom'][$item['id']] : null;
            }
        }
    }

    #[On('dailyCheckCreated')]
    public function save(int $id): void {
        try {
            foreach ($this->items as $item) {
                $notes = is_null($this->customInputs[$item['id']]) ? null : $this->customInputs[$item['id']];
                $checkStatus = is_null($this->checks[$item['id']]) ? null : $this->checks[$item['id']];

                ItemChequeoDiario::create([
                    'chequeo_diario_id' => $id,
                    'item_id'           => $item['id'],
                    'valor'             => $notes,
                    'simbologia_id'     => $checkStatus
                ]);

                $alerta = Alerta::where('item_id', $item['id'])->first();
                unset($item['id']);

                if (empty($alerta)) {
                    continue;
                }

                if (
                    !is_null($alerta->simbologia_id)
                    && !is_null($checkStatus)
                    && $alerta->simbologia_id == $checkStatus
                    || (
                        !is_null($alerta->valor)
                        && !is_null($notes)
                        && $alerta->valor == $notes
                    )
                    || (!is_null($alerta->valor) && !is_null($notes) && $alerta->operador == "<" && intval($notes) < intval($alerta->valor))
                    || (!is_null($alerta->valor) && !is_null($notes) && $alerta->operador == '>' && intval($notes) > intval($alerta->valor))
                ) {
                    $alerta->contador = $alerta->contador + 1;
                    $alerta->save();
                }
            }

            $this->dispatch('dailyCheckItemsSaved');
        } catch (\Exception $e) {
            debug($e);
            $this->dispatch('dailyCheckItemsFailed', $id);
        }
    }

    #[On('dailyCheckItemsStatusChanged')]
    public function onStatusChange(array $data): void {
        if (is_null($data['statusId'])) {
            $this->checks[$data['itemId']] = null;
            $this->customInputs[$data['itemId']] = $data['customText'];
        } else {
            $this->customInputs[$data['itemId']] = null;
            $this->checks[$data['itemId']] = $data['statusId'];
        }
    }

    public function validateItems(): bool {
        foreach ($this->items as $item) {
            $itemId = $item['id'];

            $hasCheck = isset($this->checks[$itemId]) && !is_null($this->checks[$itemId]);
            $hasCustomInput = isset($this->customInputs[$itemId]) && !is_null($this->customInputs[$itemId]);

            if (!$hasCheck && !$hasCustomInput) {
                return false;
            }
        }

        return true;
    }

    #[On('requestForValidItems')]
    public function checkValidItems(): void {
        if ($this->validateItems()) {
            $this->dispatch('validItems', [
                "checks"       => $this->checks,
                "customInputs" => $this->customInputs
            ]);
        } else {
            $this->dispatch('invalidItems');
        }
    }

    public function render() {
        return view('livewire.chequeo-items');
    }
}
