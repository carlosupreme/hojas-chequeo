<?php

namespace App\Filament\Resources\Chequeos\Schemas;

use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Saade\FilamentAutograph\Forms\Components\SignaturePad;

class ChequeosForm
{
    public static function base(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()->schema([
                    SignaturePad::make('firma_operador')
                        ->label('Firma')
                        ->penColor('blue')
                        ->penColorOnDark('blue')
                        ->live(),
                    TextInput::make('nombre_operador')
                        ->label('Nombre')
                        ->required(),
                ]),
                Textarea::make('observaciones')
                    ->label('Observaciones')
                    ->columnSpanFull(),
            ]);
    }

    public static function date(Schema $schema): Schema
    {
        return $schema->components([
            DatePicker::make('dateSelected')
                ->disabled(fn () => ! auth()->user()->can(User::$canEditDatesPermission))
                ->hiddenLabel()
                ->displayFormat('D d/m/Y')
                ->native(false)
                ->locale('es')
                ->closeOnDateSelection()
                ->required()
                ->maxDate(now()),
        ]);
    }
}
