<?php

namespace App\Filament\Resources\Chequeos\Schemas;

use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Saade\FilamentAutograph\Forms\Components\SignaturePad;

class ChequeosForm
{
    public static function base(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nombre_operador')
                    ->label('Nombre del operador')
                    ->required(),
                Textarea::make('observaciones')
                    ->label('Observaciones')
                    ->rows(3)
                    ->placeholder('Anotaciones sobre el turno (opcional)'),
            ]);
    }

    public static function signature(Schema $schema): Schema
    {
        return $schema
            ->components([
                SignaturePad::make('firma_operador')
                    ->label('Firma del operador')
                    ->penColor('blue')
                    ->penColorOnDark('blue'),
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
