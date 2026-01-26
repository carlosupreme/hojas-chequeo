<?php

namespace App\Filament\Resources\Turnos;

use App\Filament\Resources\Turnos\Pages\ManageTurnos;
use App\Models\Turno;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
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
                TextInput::make('nombre')
                    ->label('Nombre del Turno')
                    ->required()
                    ->placeholder('Ej: Tintoreria')
                    ->maxLength(255),

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
                    ->columns(3)
                    ->gridDirection('row'),

                TimePicker::make('hora_inicio')
                    ->label('Hora de Entrada')
                    ->native(false)
                    ->format('H:i')
                    ->displayFormat('H:i')
                    ->helperText('Hora en que inicia el turno'),

                TimePicker::make('hora_final')
                    ->label('Hora de Salida')
                    ->native(false)
                    ->format('H:i')
                    ->displayFormat('H:i')
                    ->after('hora_inicio')
                    ->helperText('Hora en que termina el turno'),

                Toggle::make('activo')
                    ->label('Turno Activo')->visibleOn('edit')
                    ->helperText('Desactivar si el turno no está en uso')
                    ->default(true)
                    ->required(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('nombre')
                    ->label('Nombre del Turno')
                    ->size('lg')
                    ->weight('bold'),

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

                        $dayNames = collect($days)
                            ->map(fn ($day) => trim($day))
                            ->map(fn ($day) => $dayMap[$day] ?? ucfirst($day))
                            ->filter()
                            ->join(', ');

                        return $dayNames ?: 'No especificado';
                    })
                    ->badge()
                    ->separator(),

                TextEntry::make('hora_inicio')
                    ->label('Hora de Entrada')
                    ->time('H:i')
                    ->placeholder('No especificada')
                    ->icon('heroicon-o-clock')
                    ->color('success'),

                TextEntry::make('hora_final')
                    ->label('Hora de Salida')
                    ->time('H:i')
                    ->placeholder('No especificada')
                    ->icon('heroicon-o-clock')
                    ->color('danger'),

                IconEntry::make('activo')
                    ->label('Estado')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextEntry::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('No disponible')
                    ->icon('heroicon-o-calendar'),

                TextEntry::make('updated_at')
                    ->label('Última Modificación')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('No disponible')
                    ->icon('heroicon-o-calendar')
                    ->since(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nombre')
            ->columns([
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

                    $dayNames = collect($days)
                        ->map(fn ($day) => trim($day))
                        ->map(fn ($day) => $dayMap[$day] ?? ucfirst($day))
                        ->filter()
                        ->join(', ');

                    return $dayNames ?: 'No especificado';
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
