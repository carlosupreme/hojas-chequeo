<?php

namespace App\Filament\Resources\HojaChequeos\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class HojaChequeoInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Equipment Information Section
                Section::make('Información del Equipo')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('equipo.tag')
                            ->label('Tag')
                            ->icon('heroicon-o-tag')
                            ->badge()
                            ->color('primary')
                            ->weight(FontWeight::Bold)
                            ->copyable()
                            ->copyMessage('Tag copiado'),

                        TextEntry::make('equipo.nombre')
                            ->label('Nombre del Equipo')
                            ->icon('heroicon-o-cube'),

                        TextEntry::make('equipo.area')
                            ->label('Área')
                            ->icon('heroicon-o-building-office-2')
                            ->badge()
                            ->color('info'),
                        TextEntry::make('version')
                            ->label('Versión de Hoja')
                            ->badge()
                            ->color('success')
                            ->size(TextSize::Large),

                        TextEntry::make('encendido')
                            ->label('Estado')
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state ? 'Activo' : 'Inactivo')
                            ->color(fn ($state) => $state ? 'success' : 'danger')
                            ->icon(fn ($state) => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle'),
                    ]),

                // Observaciones Section
                Section::make('Observaciones')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        TextEntry::make('observaciones')
                            ->label('')
                            ->placeholder('Sin observaciones')
                            ->html()
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                // Table Structure Preview
                Section::make('Vista Previa de Estructura')
                    ->icon('heroicon-o-table-cells')
                    ->description('Estructura de tabla generada (Filas × Columnas)')
                    ->schema([
                        ViewEntry::make('table_preview')
                            ->label('')
                            ->view('filament.infolists.hoja-chequeo-table-preview')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()->columnSpanFull(),

                // Statistics Section
                Section::make('Estadísticas')
                    ->icon('heroicon-o-chart-bar')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('columnas_count')
                            ->label('Total Columnas')
                            ->state(fn ($record) => $record->columnas->count())
                            ->badge()
                            ->color('info')
                            ->icon('heroicon-o-view-columns')
                            ->size(TextSize::Large),

                        TextEntry::make('filas_count')
                            ->label('Total Filas')
                            ->state(fn ($record) => $record->filas->count())
                            ->badge()
                            ->color('success')
                            ->icon('heroicon-o-table-cells')
                            ->size(TextSize::Large),

                        TextEntry::make('valores_count')
                            ->label('Celdas Totales')
                            ->state(fn ($record) => $record->columnas->count() * $record->filas->count())
                            ->badge()
                            ->color('warning')
                            ->icon('heroicon-o-squares-2x2')
                            ->size(TextSize::Large),

                        TextEntry::make('chequeos_count')
                            ->label('Ejecuciones')
                            ->state(fn ($record) => $record->chequeos->count())
                            ->badge()
                            ->color('purple')
                            ->icon('heroicon-o-clipboard-document-check')
                            ->size(TextSize::Large),
                    ]),

                // Metadata Section
                Section::make('Metadatos')
                    ->icon('heroicon-o-information-circle')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Fecha de Creación')
                            ->dateTime('d/m/Y H:i:s')
                            ->icon('heroicon-o-calendar'),

                        TextEntry::make('updated_at')
                            ->label('Última Actualización')
                            ->dateTime('d/m/Y H:i:s')
                            ->icon('heroicon-o-clock')
                            ->since(),
                    ])
                    ->collapsible(),
            ]);
    }
}
