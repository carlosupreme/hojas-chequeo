<?php

namespace App\Filament\Resources\HojaChequeos\Tables;

use App\Filament\Resources\HojaChequeos\HojaChequeoResource;
use App\Models\HojaChequeo;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class HojaChequeosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('equipo.tag')
                    ->label('Tag Equipo')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->copyable()
                    ->copyMessage('Tag copiado')
                    ->icon('heroicon-o-tag'),

                TextColumn::make('equipo.nombre')
                    ->label('Equipo')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->equipo->area ?? null)
                    ->limit(30),

                TextColumn::make('version')
                    ->label('Versión')
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                ToggleColumn::make('encendido')
                    ->label('Publicada')
                    ->beforeStateUpdated(fn ($record) => HojaChequeo::where('equipo_id', $record->equipo_id)
                        ->update(['encendido' => false])),

                TextColumn::make('chequeos_count')
                    ->label('Ejecuciones')
                    ->counts('chequeos')
                    ->badge()
                    ->color('warning')
                    ->icon('heroicon-o-clipboard-document-check'),

                TextColumn::make('observaciones')
                    ->label('Observaciones')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->observaciones)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('equipo_id')
                    ->label('Equipo')
                    ->relationship('equipo', 'tag')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                Action::make('Historial')
                    ->url(fn (HojaChequeo $record): string => HojaChequeoResource::getUrl('history', ['record' => $record]))
                    ->icon('heroicon-o-calendar'),
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->persistSortInSession()
            ->persistFiltersInSession()
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
