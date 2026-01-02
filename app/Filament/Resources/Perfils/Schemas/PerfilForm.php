<?php

namespace App\Filament\Resources\Perfils\Schemas;

use App\Filament\Forms\Components\SelectHojas;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PerfilForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make("nombre")->label("Nombre")->required(),
                SelectHojas::make("hoja_ids")
                    ->columnSpanFull()
                    ->label('Hojas de Chequeo')
                    ->required(),
            ]);
    }
}
