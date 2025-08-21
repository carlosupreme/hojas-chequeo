<?php

namespace App\Filament\Pages;

use App\HojaChequeoArea;
use App\Models\Reporte;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions;
use Filament\Pages\Page;
use Livewire\WithFileUploads;

class Reportar extends Page
{
    use WithFileUploads;

    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';

    protected static string $view = 'filament.pages.reportar';

    public static function canAccess(): bool
    {
        return \Auth::user()->hasRole(['Operador', 'Supervisor']);
    }

    public static function getNavigationGroup(): ?string {
        return 'Reportes';
    }


    public ?array $data = [];

    public function mount()
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Datos del reporte')
                    ->collapsible()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nombre')
                                    ->required()
                                    ->default(fn() => \Auth::user()->name),
                                DatePicker::make('fecha')
                                    ->label('Fecha')
                                    ->default(Carbon::now())
                                    ->readOnly()
                                    ->native(false)
                                    ->closeOnDateSelection(),

                                Select::make('priority')->label('Prioridad')
                                    ->default("baja")
                                    ->options([
                                        'alta' => 'Alta',
                                        'media' => 'Media',
                                        'baja' => 'Baja'
                                    ])
                                    ->required()
                                    ->native(false)
                            ]),
                    ]),

                Section::make('Detalles del equipo')
                    ->description('Selecciona el equipo que presenta la falla')
                    ->collapsible()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('equipo_id')
                                    ->label('Tag del Equipo')
                                    ->relationship('equipo', 'tag')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Select::make('area')->label('Area')
                                    ->options(fn() => array_combine(
                                        array_map(fn(HojaChequeoArea $area) => $area->value, HojaChequeoArea::cases()),
                                        array_map(fn(HojaChequeoArea $area) => $area->value, HojaChequeoArea::cases())
                                    ))
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                            ]),
                    ]),

                Section::make('Detalles de la falla')
                    ->description('Describe el problema que presenta')
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                TextInput::make('failure')
                                    ->label('Falla')
                                    ->required()
                                    ->columnSpanFull(),
                                Textarea::make('observations')
                                    ->label('Observaciones')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Section::make('Evidencia')
                    ->schema([
                        FileUpload::make('photo')
                            ->label('Foto')
                            ->image()
                            ->imageEditor()
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data')
            ->model(Reporte::class);
    }

    public function submit(): void
    {
        $data = [...$this->form->getState(), 'user_id' => auth()->user()->id];
        $reporte = Reporte::create($data);
        $this->form->model($reporte)->saveRelationships();
        Notification::make()
            ->success()
            ->icon('heroicon-o-inbox-arrow-down')
            ->iconColor('success')
            ->title('Reporte creado correctamente')
            ->send();

        Notification::make()
            ->title('Nuevo reporte de falla')
            ->danger()
            ->icon('heroicon-o-inbox-arrow-down')
            ->iconColor('danger')
            ->body(auth()->user()->name . ' ha reportado una falla de ' . $reporte->equipo->tag)
            ->actions([
                Actions\Action::make('Ver')
                    ->button()
                    ->url("/admin/reportes/")
            ])
            ->sendToDatabase(User::role('Administrador')->get(), isEventDispatched: true);
        $this->form->fill();
        $this->redirect("reporte-historico");
    }

}
