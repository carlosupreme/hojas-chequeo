<?php

namespace App\Filament\Resources\Perfils\Schemas;

use App\Filament\Forms\Components\SelectHojas;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class PerfilForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nombre')
                    ->label('Nombre del Perfil')
                    ->placeholder('Ej: Operador de Tintorería')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->autofocus()
                    ->columnSpanFull(),
                Checkbox::make('acceso_total')
                    ->label('Acceso Total')
                    ->helperText('Si se activa, este perfil tendrá acceso a todas las hojas de chequeo actuales y futuras.')
                    ->default(false)
                    ->afterStateUpdated(function (bool $state, callable $set) {
                        if ($state === true) {
                            $set('hoja_ids', []);
                        }
                    })
                    ->live()
                    ->columnSpanFull(),

                Section::make('Hojas de Chequeo Asignadas')
                    ->description('Selecciona las hojas de chequeo a las que este perfil tendrá acceso. Solo se muestran las hojas activas.')
                    ->visible(fn (Get $get) => ! $get('acceso_total'))
                    ->columnSpanFull()
                    ->schema([
                        SelectHojas::make('hoja_ids')
                            ->columnSpanFull()
                            ->label('')
                            ->default([])
                            ->dehydrateStateUsing(fn ($state) => $state ?? [])
                            ->required(fn (Get $get) => ! $get('acceso_total')),
                    ]),
            ]);
    }
}
