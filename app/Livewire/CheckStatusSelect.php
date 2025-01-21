<?php

namespace App\Livewire;

use App\Models\Simbologia;
use Livewire\Component;

class CheckStatusSelect extends Component
{
    public $itemId;
    public $selectedStatus;
    public $customText      = '';
    public $showCustomInput = false;
    public $open            = false;
    public $selectedName    = '';

    public function mount($itemId, $initialStatus) {
        $this->itemId = $itemId;
        $this->selectedStatus = $initialStatus;
        $this->selectedName = Simbologia::find($initialStatus)?->nombre ?? '';
    }

    public function updatedCustomText(): void {
        if (!empty($this->customText)) {
            $this->selectedName = 'Personalizado';
            $this->dispatch('dailyCheckItemsStatusChanged', [
                'itemId'     => $this->itemId,
                'customText' => $this->customText,
                'statusId'   => null
            ]);
        }
    }

    public function close(): void {
        $this->open = false;
    }

    public function choose($value): void {
        $this->open = false;
        if ($value === 'custom') {
            $this->showCustomInput = true;
            $this->selectedName = 'Personalizado';
        } else {
            $this->showCustomInput = false;
            $this->selectedName = Simbologia::find($value)?->nombre ?? '';
            $this->dispatch('dailyCheckItemsStatusChanged', [
                'itemId'     => $this->itemId,
                'statusId'   => $value,
                'customText' => null
            ]);
        }
    }

    public function render() {
        $statuses = Simbologia::select('id', 'nombre', 'icono', 'color')->get();
        return view('livewire.check-status-select', compact('statuses'));
    }
}
