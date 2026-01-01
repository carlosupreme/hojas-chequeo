<?php

namespace App\Filament\Resources\HojaChequeos\Pages;

use App\Filament\Resources\HojaChequeos\HojaChequeoResource;
use App\Filament\Resources\HojaChequeos\Schemas\HojaChequeoForm;
use App\Models\HojaChequeo;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Livewire\Attributes\On;

class EditHojaChequeo extends Page
{
    use InteractsWithRecord;

    protected static string $resource = HojaChequeoResource::class;

    protected string $view = 'filament.resources.hoja-chequeos.pages.edit-hoja-chequeo';

    public ?array $data = [];

    public int $hojaChequeoId;

    public function mount(int|string $record): void
    {
        $this->record = HojaChequeo::findOrFail($record);
        $this->hojaChequeoId = $this->record->id;
        $this->form->fill([
            ...$this->record->attributesToArray(),
            'version' => HojaChequeo::getCurrentVersion($this->record->equipo_id),
        ]);
    }

    public function update(): void
    {
        $data = $this->form->getState();
        $record = HojaChequeo::create($data);
        $this->dispatch('hoja-chequeo-created', $record->id);
        $this->record->update([
            'encendido' => false,
        ]);
    }

    #[On('create-hoja-chequeo-items-created')]
    public function success(): void
    {
        Notification::make()->success()->title('Nueva version creada')->send();
        $this->redirect($this->getResource()::getUrl('index'));
    }

    public function form(Schema $schema): Schema
    {
        return HojaChequeoForm::configure($schema)->statePath('data');
    }

    public function getTitle(): string
    {
        return 'Editar hoja de chequeo';
    }
}
