<?php

namespace App\Filament\Resources\HojaChequeoResource\Pages;

use App\Filament\Resources\HojaChequeoResource;
use App\HojaChequeoArea;
use App\Models\HojaChequeo;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Attributes\On;

class EditHojaChequeo extends Page
{
    protected static string $resource = HojaChequeoResource::class;

    protected static string $view = 'filament.resources.hoja-chequeo-resource.pages.edit-hoja-chequeo';

    public HojaChequeo $record;

    public ?array $data = [];

    public function mount(HojaChequeo $record): void {
        $this->record = $record;
        $this->form->fill([
            ...$this->record->attributesToArray(),
            'version' => HojaChequeo::where('equipo_id', $record->equipo_id)
                                    ->orderBy('version', 'desc')
                                    ->value('version') + 1
        ]);
    }

    public function form(Form $form): Form {
        return $form
            ->schema([
                Select::make('equipo_id')
                      ->relationship('equipo', 'tag')
                      ->reactive()
                      ->afterStateUpdated(function (Get $get, Set $set): int {
                          if (!$get('equipo_id')) {
                              return 1;
                          }

                          $ultimaVersion = HojaChequeo::where('equipo_id', $get('equipo_id'))
                                                      ->orderBy('version', 'desc')
                                                      ->value('version');
                          return $set('version', $ultimaVersion + 1);
                      })
                      ->required(),
                Select::make('area')
                      ->default(fn($record) => $record?->area)
                      ->options(fn() => array_map(fn(HojaChequeoArea $area) => $area->value, HojaChequeoArea::cases()))
                      ->required(),
                TextInput::make('version')
                         ->readOnly()
                         ->live()
                         ->helperText('Esta version se creará para no modificar la actual'),
                RichEditor::make('observaciones')->disableToolbarButtons(['codeBlock', 'attachFiles'])->maxLength(255),
            ])
            ->statePath('data')
            ->model($this->record);
    }

    public function update(): void {
        $data = $this->form->getState();
        $record = HojaChequeo::create($data);
        $this->form->model($record)->saveRelationships();
        $this->dispatch('checkSheetUpdated', $record->id);
    }

    #[On('checkSheetItemsUpdated')]
    public function redirectToTable(): void {
        Notification::make()->success()->title('Nueva version creada')->send();
        $this->redirect($this->getResource()::getUrl('index'));
    }

    public function getTitle(): string|Htmlable {
        return __('actions.named.edit', ['name' => HojaChequeoResource::getModelLabel()]);
    }
}
