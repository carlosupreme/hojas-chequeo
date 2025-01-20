<?php

namespace App\Filament\Resources\HojaChequeoResource\Pages;

use App\Filament\Resources\HojaChequeoResource;
use App\Models\HojaChequeo;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class EditHojaChequeo extends Page
{
    protected static string $resource = HojaChequeoResource::class;

    protected static string $view = 'filament.resources.hoja-chequeo-resource.pages.edit-hoja-chequeo';

    public HojaChequeo $record;

    public ?array $data = [];

    public function mount(HojaChequeo $record): void {
        $this->record = $record;
        $this->form->fill($this->record->attributesToArray());
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
                TextInput::make('version')->readOnly()
                         ->live()
                    ->helperText('Esta version se actualizara para no modificar la actual'),
                RichEditor::make('observaciones')->disableToolbarButtons(['codeBlock', 'attachFiles'])->maxLength(255),
            ])
            ->statePath('data')
            ->model($this->record);
    }

    public function update() {}

    public function getTitle(): string|Htmlable {
        return __('actions.named.edit', ['name' => HojaChequeoResource::getModelLabel()]);
    }
}
