<?php

namespace App\Filament\Resources\HojaChequeos;

use App\Filament\Resources\HojaChequeos\Pages\CreateHojaChequeo;
use App\Filament\Resources\HojaChequeos\Pages\EditHojaChequeo;
use App\Filament\Resources\HojaChequeos\Pages\ListHojaChequeos;
use App\Filament\Resources\HojaChequeos\Schemas\HojaChequeoForm;
use App\Filament\Resources\HojaChequeos\Schemas\HojaChequeoInfolist;
use App\Filament\Resources\HojaChequeos\Tables\HojaChequeosTable;
use App\Models\HojaChequeo;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class HojaChequeoResource extends Resource
{
    protected static ?string $model = HojaChequeo::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    public static function getNavigationGroup(): ?string
    {
        return 'Mantenimiento';
    }

    public static function form(Schema $schema): Schema
    {
        return HojaChequeoForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return HojaChequeoInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HojaChequeosTable::configure($table);
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
            'index' => ListHojaChequeos::route('/'),
            'create' => CreateHojaChequeo::route('/crear'),
            //            'view' => ViewHojaChequeo::route('/{record}'),
            'edit' => EditHojaChequeo::route('/{record}/editar'),
        ];
    }
}
