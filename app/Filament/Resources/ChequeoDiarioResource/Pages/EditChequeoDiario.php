<?php

namespace App\Filament\Resources\ChequeoDiarioResource\Pages;

use App\Filament\Resources\ChequeoDiarioResource;
use App\Models\ChequeoDiario;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Livewire\Attributes\On;

class EditChequeoDiario extends Page
{
    protected static string $resource = ChequeoDiarioResource::class;

    protected static ?string $title = "Editar chequeo";

    protected static string $view = 'filament.resources.chequeo-diario-resource.pages.edit-chequeo-diario';

    public ChequeoDiario $record;

    public ?array $data = [];

    public $items = [];

    public $defaultValues = [];

    public function mount(ChequeoDiario $record): void {
        $this->record = $record;
        $this->defaultValues['checks'] = [];
        $this->defaultValues['custom'] = [];
        foreach ($record->itemsChequeoDiario as $item) {
            if ($item->simbologia_id) {
                $this->defaultValues['custom'][$item['item_id']] = null;
                $this->defaultValues['checks'][$item['item_id']] = $item->simbologia_id;
            } else {
                $this->defaultValues['custom'][$item['item_id']] = $item->valor;
                $this->defaultValues['checks'][$item['item_id']] = null;
            }
        }
    }

    public function update() {
        $this->dispatch("requestForValidItems");
    }

    #[On('validItems')]
    public function finishUpdate($data) {
        foreach ($this->record->itemsChequeoDiario as $item) {
            $notes = is_null($data['customInputs'][$item['item_id']]) ? null : $data['customInputs'][$item['item_id']];
            $checkStatus = is_null($data['checks'][$item['item_id']]) ? null : $data['checks'][$item['item_id']];

            $item->valor = $notes;
            $item->simbologia_id = $checkStatus;
            $item->save();
        }

        Notification::make()
                    ->success()
                    ->icon('heroicon-o-document-text')
                    ->iconColor('success')
                    ->title('Chequeo diario actualizado')
                    ->send();

        $this->redirect($this->getResource()::getUrl('view', ["record" => $this->record]));
    }


}
