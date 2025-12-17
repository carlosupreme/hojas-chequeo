<?php

namespace App\Filament\Resources\ChequeoDiarios\Pages;

use App\Filament\Resources\ChequeoDiarios\ChequeoDiarioResource;
use App\Models\Alerta;
use App\Models\ChequeoDiario;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Livewire\Attributes\On;

class EditChequeoDiario extends Page
{
    protected static string $resource = ChequeoDiarioResource::class;

    protected static ?string $title = "Editar chequeo";

    protected string $view = 'filament.resources.chequeo-diario-resource.pages.edit-chequeo-diario';

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

            $alerta = Alerta::where('item_id', $item['item_id'])->first();

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
                || (!is_null($alerta->valor) && !is_null($notes) && $alerta->operador == '<' && intval($notes) < intval($alerta->valor))
                || (!is_null($alerta->valor) && !is_null($notes) && $alerta->operador == '>' && intval($notes) > intval($alerta->valor))
            ) {
                $alerta->contador = $alerta->contador + 1;
                $alerta->save();
            }
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
