<?php

namespace App\Livewire;

use App\Models\HojaChequeo;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;

class SelectHojaChequeo extends Component implements HasForms
{
    use InteractsWithForms;

    public HojaChequeo $checkSheet;

    public ?array $data = [];

    public function mount(): void {
        $this->form->fill();
    }

    public function form(Form $form): Form {
        return $form
            ->schema(array(
                Select::make('hoja_chequeo_id')
                      ->label('Hoja de chequeo')
                      ->relationship(name            : 'hojaChequeo',
                                     modifyQueryUsing: fn($query) => $query->where('hoja_chequeos.active', true))
                      ->getOptionLabelFromRecordUsing(fn(Model $record) => $record->equipo->tag)
                      ->required(),
            ))
            ->statePath('data')
            ->model(\App\Models\ChequeoDiario::class);
    }

    public function nextPage(): void {
        $data = $this->form->getState();
        $this->dispatch('checkSheetSelected', $data['hoja_chequeo_id']);
    }

    public function render() {
        return view('livewire.select-hoja-chequeo');
    }
}
