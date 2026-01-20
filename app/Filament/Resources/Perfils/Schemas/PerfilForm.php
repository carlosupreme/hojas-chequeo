<?php

namespace App\Filament\Resources\Perfils\Schemas;

use App\Filament\Forms\Components\SelectHojas;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class PerfilForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nombre')->label('Nombre')->required(),
                Checkbox::make('acceso_total')
                    ->label('Acceso Total')
                    ->default(false)
                    ->afterStateUpdated(function (bool $state, callable $set) {
                        if ($state === true) {
                            $set('hoja_ids', []);
                        }
                    })
                    ->live(),
                SelectHojas::make('hoja_ids')
                    ->visible(fn (Get $get) => ! $get('acceso_total'))
                    ->columnSpanFull()
                    ->label('Hojas de Chequeo')
                    ->default([])
                    ->dehydrateStateUsing(fn ($state) => $state ?? [])
                    ->required(fn (Get $get) => ! $get('acceso_total')),
            ]);
    }
}
