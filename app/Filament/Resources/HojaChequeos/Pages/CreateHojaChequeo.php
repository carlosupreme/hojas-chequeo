<?php

namespace App\Filament\Resources\HojaChequeos\Pages;

use App\Filament\Resources\HojaChequeos\HojaChequeoResource;
use App\Models\Equipo;
use App\Models\HojaChequeo;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
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
        return $schema
            ->components([
                Select::make('equipo_id')
                    ->label('Equipo')
                    ->options(Equipo::query()->pluck('tag', 'id'))
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->live()
                    ->partiallyRenderComponentsAfterStateUpdated(['version'])
                    ->afterStateUpdated(function (Get $get, Set $set, $operation): int {
                        if ($operation === 'edit') {
                            return $get('version');
                        }

                        $equipoId = $get('equipo_id');

                        if (! $equipoId) {
                            return 1;
                        }

                        return $set('version', HojaChequeo::getCurrentVersion($equipoId));
                    })
                    ->required(),
                TextInput::make('version')
                    ->readOnly()
                    ->live()
                    ->default(1)
                    ->helperText('Esta version se calcula automaticamente'),
                RichEditor::make('observaciones')->disableToolbarButtons(['codeBlock', 'attachFiles'])->maxLength(255),
            ])
            ->statePath('data');
    }

    public function getTitle(): string
    {
        return 'Crear hoja de chequeo';
    }
}
