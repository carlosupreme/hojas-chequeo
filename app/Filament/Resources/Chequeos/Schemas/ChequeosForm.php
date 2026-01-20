<?php

namespace App\Filament\Resources\Chequeos\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Saade\FilamentAutograph\Forms\Components\SignaturePad;

class ChequeosForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()->schema([
                    SignaturePad::make('firma_operador')
                        ->label('Firma')
                        ->required(),
                    TextInput::make('nombre_operador')
                        ->label('Nombre')
                        ->required(),
                ]),
                Textarea::make('observaciones')
                    ->label('Observaciones')
                    ->columnSpanFull(),
            ]);
    }
}
