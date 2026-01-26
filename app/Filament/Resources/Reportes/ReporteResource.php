<?php

namespace App\Filament\Resources\Reportes;

use App\Filament\Resources\Reportes\Pages\CreateReporte;
use App\Filament\Resources\Reportes\Pages\EditReporte;
use App\Filament\Resources\Reportes\Pages\ListReportes;
use App\Filament\Resources\Reportes\Schemas\ReporteForm;
use App\Filament\Resources\Reportes\Tables\ReportesTable;
use App\Models\Reporte;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ReporteResource extends Resource
{
    protected static ?string $model = Reporte::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaperAirplane;

    protected static ?string $recordTitleAttribute = 'Reporte';

    public static function getNavigationGroup(): ?string
    {
        return 'Reportes';
    }

    public static function form(Schema $schema): Schema
    {
        return ReporteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReportesTable::configure($table);
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
            'create' => CreateReporte::route('/create'),
            'index' => ListReportes::route('/'),
            'edit' => EditReporte::route('/{record}/edit'),
        ];
    }
}
