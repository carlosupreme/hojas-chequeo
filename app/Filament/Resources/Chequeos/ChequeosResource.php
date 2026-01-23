<?php

namespace App\Filament\Resources\Chequeos;

use App\Filament\Resources\Chequeos\Pages\ListChequeos;
use App\Filament\Resources\Chequeos\Schemas\ChequeosForm;
use App\Filament\Resources\Chequeos\Tables\ChequeosTable;
use App\Models\HojaEjecucion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ChequeosResource extends Resource
{
    protected static ?string $model = HojaEjecucion::class;

    protected static ?string $navigationLabel = 'Chequeos';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    public static function getNavigationGroup(): ?string
    {
        return 'Mantenimiento';
    }

    public static function form(Schema $schema): Schema
    {
        return ChequeosForm::base($schema);
    }

    public static function table(Table $table): Table
    {
        return ChequeosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChequeos::route('/'),
        ];
    }
}
