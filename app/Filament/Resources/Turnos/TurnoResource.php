<?php

namespace App\Filament\Resources\Turnos;

use App\Filament\Resources\Turnos\Pages\ManageTurnos;
use App\Models\Equipo;
use App\Models\Turno;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TurnoResource extends Resource
{
    protected static ?string $model = Turno::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?string $recordTitleAttribute = 'nombre';

    public static function getNavigationGroup(): ?string
    {
        return 'Administración';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make('Información General')
                        ->icon('heroicon-o-building-office')
                        ->columns(2)
                        ->schema([
                            Select::make('centro_costo_id')
                                ->label('Centro de Costo')
                                ->relationship('centroCosto', 'nombre')
                                ->required()
                                ->searchable()
                                ->preload(),

                            TextInput::make('nombre')
                                ->label('Nombre del Turno')
                                ->required()
                                ->placeholder('Ej: Tintorería Mañana')
                                ->maxLength(255),
                        ]),

                    Step::make('Programación')
                        ->icon('heroicon-o-clock')
                        ->schema([
                            CheckboxList::make('dias')
                                ->label('Días de trabajo')
                                ->required()
                                ->options([
                                    'monday' => 'Lunes',
                                    'tuesday' => 'Martes',
                                    'wednesday' => 'Miércoles',
                                    'thursday' => 'Jueves',
                                    'friday' => 'Viernes',
                                    'saturday' => 'Sábado',
                                    'sunday' => 'Domingo',
                                ])
                                ->columns(4)
                                ->gridDirection('row')
                                ->columnSpanFull(),

                            Grid::make(2)
                                ->schema([
                                    TimePicker::make('hora_inicio')
                                        ->label('Hora de Entrada')
                                        ->native(false)
                                        ->format('H:i')
                                        ->displayFormat('H:i')
                                        ->prefixIcon('heroicon-o-sun')
                                        ->helperText('Hora en que inicia el turno'),

                                    TimePicker::make('hora_final')
                                        ->label('Hora de Salida')
                                        ->native(false)
                                        ->format('H:i')
                                        ->displayFormat('H:i')
                                        ->prefixIcon('heroicon-o-moon')
                                        ->helperText('Hora en que termina el turno'),
                                ]),
                        ]),

                    Step::make('Equipos')
                        ->icon('heroicon-o-wrench-screwdriver')
                        ->schema([
                            Select::make('equipos')
                                ->label('Equipos requeridos')
                                ->relationship('equipos', 'nombre')
                                ->multiple()
                                ->preload()
                                ->searchable()
                                ->getOptionLabelFromRecordUsing(fn (Equipo $record) => "{$record->tag} — {$record->nombre}")
                                ->placeholder('Seleccionar equipos...')
                                ->columnSpanFull(),
                        ]),
                ])
                    ->skippable()
                    ->columnSpanFull(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información General')
                    ->icon('heroicon-o-building-office')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('nombre')
                            ->label('Nombre del Turno')
                            ->size('lg')
                            ->weight('bold'),

                        TextEntry::make('centroCosto.nombre')
                            ->label('Centro de Costo')
                            ->badge()
                            ->color('primary'),
                    ])
                    ->columnSpanFull(),

                Section::make('Equipos Requeridos')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('equipos')
                            ->label('')
                            ->schema([
                                TextEntry::make('tag')
                                    ->label('TAG')
                                    ->badge()
                                    ->color('gray'),
                                TextEntry::make('nombre')
                                    ->label('Nombre'),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ]),

                Section::make('Programación')
                    ->icon('heroicon-o-clock')
                    ->columns(2)->columnSpanFull()
                    ->schema([
                        TextEntry::make('dias')
                            ->label('Días de Trabajo')
                            ->formatStateUsing(function ($state) {
                                if (is_string($state)) {
                                    $days = explode(',', $state);
                                } elseif (is_array($state)) {
                                    $days = $state;
                                } else {
                                    return 'No especificado';
                                }

                                $dayMap = [
                                    'monday' => 'Lunes',
                                    'tuesday' => 'Martes',
                                    'wednesday' => 'Miércoles',
                                    'thursday' => 'Jueves',
                                    'friday' => 'Viernes',
                                    'saturday' => 'Sábado',
                                    'sunday' => 'Domingo',
                                ];

                                return collect($days)
                                    ->map(fn ($day) => $dayMap[trim($day)] ?? ucfirst($day))
                                    ->filter()
                                    ->join(', ') ?: 'No especificado';
                            })
                            ->badge()
                            ->separator()
                            ->columnSpanFull(),

                        TextEntry::make('hora_inicio')
                            ->label('Hora de Entrada')
                            ->time('H:i')
                            ->placeholder('No especificada')
                            ->icon('heroicon-o-sun')
                            ->color('success'),

                        TextEntry::make('hora_final')
                            ->label('Hora de Salida')
                            ->time('H:i')
                            ->placeholder('No especificada')
                            ->icon('heroicon-o-moon')
                            ->color('danger'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nombre')
            ->columns([
                TextColumn::make('centroCosto.nombre')->label('Centro de costo')
                    ->searchable(),
                TextColumn::make('nombre')->label('Turno')
                    ->searchable(),
                TextColumn::make('hora_inicio')->label('Hora entrada')
                    ->time()
                    ->sortable(),
                TextColumn::make('hora_final')->label('Hora salida')
                    ->time()
                    ->sortable(),
                TextColumn::make('dias')->formatStateUsing(function ($state) {
                    if (is_string($state)) {
                        $days = explode(',', $state);
                    } elseif (is_array($state)) {
                        $days = $state;
                    } else {
                        return 'No especificado';
                    }

                    $dayMap = [
                        'monday' => 'Lun',
                        'tuesday' => 'Mar',
                        'wednesday' => 'Mié',
                        'thursday' => 'Jue',
                        'friday' => 'Vie',
                        'saturday' => 'Sáb',
                        'sunday' => 'Dom',
                    ];

                    return collect($days)
                        ->map(fn ($day) => $dayMap[trim($day)] ?? ucfirst($day))
                        ->filter()
                        ->join(', ') ?: 'No especificado';
                })->label('Días')->badge()->separator(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageTurnos::route('/'),
        ];
    }
}
