<?php

namespace App\Filament\Resources\Reportes\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
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
                        TextInput::make('name')
                            ->label('Nombre')
                            ->default(fn() => auth()->user()->name)
                            ->required(),
                        DatePicker::make('fecha')
                            ->label('Fecha de hoy')
                            ->default(now())
                            ->readOnly()
                            ->displayFormat('\d\e F, Y')
                            ->native(false),

                        Select::make('priority')
                            ->label('Prioridad')
                            ->required()
                            ->options([
                                'Alta' => 'Alta',
                                'Media' => 'Media',
                                'Baja' => 'Baja',
                            ]),
                    ]),


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
                            ->options([
                                'Cuarto de máquinas' => 'Cuarto de máquinas',
                                'Lavandería Institucional' => 'Lavandería Institucional',
                                'Tintorería' => 'Tintorería',
                            ]),
                    ]),


                Section::make('Detalles de la falla')
                    ->description('Describe el problema que presenta')
                    ->schema([
                        TextInput::make('failure')
                            ->label('Falla')
                            ->required(),
                        Textarea::make('observations')
                        ->label('Observaciones'),

                    ]),

                Section::make('Evidencia')
                    ->description('Adjunta evidencia del equipo que presenta la falla')
                    ->schema([
                        FileUpload::make('photo')
                            ->label('Foto')

                    ]),
            ]);
    }
}
