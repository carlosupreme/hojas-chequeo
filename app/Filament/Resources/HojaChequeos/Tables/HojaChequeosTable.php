<?php

namespace App\Filament\Resources\HojaChequeos\Tables;

use App\Filament\Resources\HojaChequeos\HojaChequeoResource;
use App\Models\Equipo;
use App\Models\HojaChequeo;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                SelectFilter::make('area')
                    ->label('Área')
                    ->placeholder('Todas')
                    ->options(fn () => Equipo::distinct()->orderBy('area')->pluck('area')
                        ->filter()
                        ->map(fn (string $a) => ucwords(mb_strtolower($a)))
                        ->unique()
                        ->sort()
                        ->values()
                        ->mapWithKeys(fn (string $a) => [$a => $a])
                        ->toArray()
                    )
                    ->query(fn (Builder $query, array $data) => $query->when(
                        $data['value'],
                        fn ($q) => $q->whereHas('equipo', fn ($eq) => $eq->whereRaw('LOWER(area) = ?', [strtolower($data['value'])]))
                    )),
            ])
            ->recordActions([
                Action::make('Versiones')
                    ->url(fn (HojaChequeo $record): string => HojaChequeoResource::getUrl('versions', ['record' => $record]))
                    ->icon('heroicon-o-document-duplicate')
                    ->visible(fn (HojaChequeo $record): bool => HojaChequeo::where('equipo_id', $record->equipo_id)->count() > 1),
                Action::make('Historial')
                    ->url(fn (HojaChequeo $record): string => HojaChequeoResource::getUrl('history', ['record' => $record]))
                    ->icon('heroicon-o-calendar'),
                ViewAction::make()->modalWidth(Width::SevenExtraLarge),
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
