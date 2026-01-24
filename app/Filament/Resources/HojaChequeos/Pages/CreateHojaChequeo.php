<?php

namespace App\Filament\Resources\HojaChequeos\Pages;

use App\Filament\Resources\HojaChequeos\HojaChequeoResource;
use App\Filament\Resources\HojaChequeos\Schemas\HojaChequeoForm;
use App\Models\HojaChequeo;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Livewire\Attributes\On;

class CreateHojaChequeo extends Page
{
    protected static string $resource = HojaChequeoResource::class;

    protected string $view = 'filament.resources.hoja-chequeos.pages.create-hoja-chequeo';

    public ?array $data = [];

    public ?int $recordCreatedId = null;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function create(): void
    {
        $data = $this->form->getState();
        $data['observaciones'] = $data['observaciones'] === '<p></p>' ? null : $data['observaciones'];
        $record = HojaChequeo::create($data);
        $this->recordCreatedId = $record->id;
        $this->dispatch('hoja-chequeo-created', $record->id);
    }

    #[On('create-hoja-chequeo-items-created')]
    public function success(): void
    {
        Notification::make()->success()->title('Creado')->send();
        $this->redirect($this->getResource()::getUrl('index'));
    }

    #[On('create-hoja-chequeo-items-failed')]
    public function error(): void
    {
        if (! $this->recordCreatedId) {
            return;
        }

        HojaChequeo::find($this->recordCreatedId)->deleteQuietly();
        Notification::make()->danger()->title('Corrige los errores')->send();
    }

    public function form(Schema $schema): Schema
    {
        return HojaChequeoForm::configure($schema)->statePath('data');
    }

    public function getTitle(): string
    {
        return 'Crear hoja de chequeo';
    }
}
