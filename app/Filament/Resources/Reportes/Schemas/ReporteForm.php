<?php

namespace App\Filament\Resources\Reportes\Schemas;

use App\Area;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReporteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos del reporte')
                    ->schema([
                        TextInput::make('nombre')
                            ->label('Nombre')
                            ->default(fn () => auth()->user()->name)
                            ->required(),
                        DateTimePicker::make('fecha')
                            ->label('Fecha de hoy')
                            ->default(fn () => now())
                            ->displayFormat('D \d\e F, Y')
                            ->native(false),

                        Select::make('prioridad')
                            ->label('Prioridad')
                            ->required()
                            ->options([
                                'alta' => 'Alta',
                                'media' => 'Media',
                                'baja' => 'Baja',
                            ])
                            ->default('baja'),
                    ]),
                Hidden::make('status')
                    ->default('pendiente'),
                Hidden::make('user_id')
                    ->default(fn () => auth()->user()->id),
                Section::make('Detalles del equipo')
                    ->description('Selecciona el equipo que presenta la falla')
                    ->schema([
                        Select::make('equipo_id')
                            ->label('Seleccionar Equipo')
                            ->relationship(
                                name: 'equipo',
                                titleAttribute: 'tag'
                            )
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('area')
                            ->label('Area')
                            ->required()
                            ->options(collect(Area::cases())
                                ->mapWithKeys(fn (Area $area) => [
                                    $area->value => $area->label(),
                                ])
                                ->toArray()
                            ),
                    ]),
                Section::make('Detalles de la falla')
                    ->description('Describe el problema que presenta')
                    ->schema([
                        TextInput::make('falla')
                            ->label('Falla')
                            ->required(),
                        Textarea::make('observaciones')
                            ->label('Observaciones'),
                    ]),
                Section::make('Evidencia')
                    ->description('Adjunta evidencia del equipo que presenta la falla')
                    ->schema([
                        FileUpload::make('foto')
                            ->image()
                            ->label('Foto'),

                    ]),
            ]);
    }
}
