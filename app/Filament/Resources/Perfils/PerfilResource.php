<?php

namespace App\Filament\Resources\Perfils;

use App\Filament\Resources\Perfils\Pages\ListPerfils;
use App\Filament\Resources\Perfils\Schemas\PerfilForm;
use App\Filament\Resources\Perfils\Schemas\PerfilInfolist;
use App\Filament\Resources\Perfils\Tables\PerfilsTable;
use App\Models\Perfil;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PerfilResource extends Resource
{
    protected static ?string $model = Perfil::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static ?string $recordTitleAttribute = 'nombre';

    protected static ?string $pluralModelLabel = 'Perfiles';

    public static function getNavigationGroup(): ?string
    {
        return 'Administración';
    }

    public static function form(Schema $schema): Schema
    {
        return PerfilForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PerfilInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PerfilsTable::configure($table);
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
            'index' => ListPerfils::route('/'),
        ];
    }
}
