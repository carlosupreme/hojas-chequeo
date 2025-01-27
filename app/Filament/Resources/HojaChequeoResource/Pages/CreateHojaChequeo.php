<?php

namespace App\Filament\Resources\HojaChequeoResource\Pages;

use App\Filament\Resources\HojaChequeoResource;
use App\Models\HojaChequeo;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Attributes\On;

class CreateHojaChequeo extends Page
{
    protected static string $resource = HojaChequeoResource::class;

    protected static string $view = 'filament.resources.hoja-chequeo-resource.pages.create-hoja-chequeo';

    public ?array $data = [];

    public function mount(): void {
        $this->form->fill();
    }

    public function create(): void {
        $data = $this->form->getState();
        $record = HojaChequeo::create($data);
        $this->form->model($record)->saveRelationships();
        $this->dispatch('checkSheetCreated', $record->id);
    }

    #[On('checkSheetItemsCreated')]
    public function redirectToTable() {
        Notification::make()->success()->title('Creado')->send();
        $this->redirect($this->getResource()::getUrl('index'));
    }

    public function form(Form $form): Form {
        return $form
            ->schema([
                Select::make('equipo_id')
                      ->relationship('equipo', 'tag')
                      ->reactive()
                      ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, $context): int {
                          if ($context === 'edit') {
                              return $get('version');
                          }
                          $equipoId = $get('equipo_id');
                          if (!$equipoId) {
                              return 1;
                          }
                          $ultimaVersion = HojaChequeo::where('equipo_id', $equipoId)->orderBy('version', 'desc')
                                                      ->value('version');
                          return $set('version', $ultimaVersion ? $ultimaVersion + 1 : 1);
                      })
                      ->required(),
                TextInput::make('area')->live()->required(),
                TextInput::make('version')->readOnly()->live()->default(1)
                    ->helperText('Esta version se calcula automaticamente'),
                RichEditor::make('observaciones')->disableToolbarButtons(['codeBlock', 'attachFiles'])->maxLength(255),
            ])
            ->statePath('data')
            ->model(HojaChequeo::class);
    }

    public function getTitle(): string|Htmlable {
        return __('actions.named.create', ['name' => HojaChequeoResource::getModelLabel()]);
    }

    protected function getRedirectUrl(): string {
        return HojaChequeoResource::getUrl('index');
    }
}
