<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HojaChequeoResource\Pages;
use App\Filament\Resources\HojaChequeoResource\RelationManagers;
use App\HojaChequeoArea;
use App\Infolists\Components\ViewItems;
use App\Models\HojaChequeo;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class HojaChequeoResource extends Resource
{
    protected static ?string $model = HojaChequeo::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('equipo.tag')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('equipo.nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('version')
                    ->searchable()
                    ->numeric()
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('active')
                    ->label('Publicada')
                    ->beforeStateUpdated(fn($record) => HojaChequeo::
                        where('equipo_id', $record->equipo_id)
                        ->where('area', $record->area)
                        ->update(['active' => false])),
                Tables\Columns\TextColumn::make('area')->extraAttributes(['class' => 'uppercase'])
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('observaciones')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('area')
                    ->options([
                        HojaChequeoArea::TINTORERIA->value => HojaChequeoArea::TINTORERIA->value,
                        HojaChequeoArea::LAVANDERIA_INSTITUCIONAL->value => HojaChequeoArea::LAVANDERIA_INSTITUCIONAL->value,
                        HojaChequeoArea::CUARTO_DE_MAQUINAS->value => HojaChequeoArea::CUARTO_DE_MAQUINAS->value,
                    ]),
                Tables\Filters\TernaryFilter::make('active')
                    ->label("Publicada")
                    ->queries(
                        true: fn(Builder $query) => $query->where('active', true),
                        false: fn(Builder $query) => $query->where('active', false),
                        blank: fn(Builder $query) => $query
                    )
            ])
            ->persistSortInSession()
            ->persistFiltersInSession()
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\Action::make('Historial')
                        ->url(fn(HojaChequeo $record): string => HojaChequeoResource::getUrl('history', ['record' => $record]))
                        ->icon('heroicon-o-calendar'),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }


    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Grid::make()->schema([
                    Section::make('Datos de la hoja de chequeo')
                        ->icon('heroicon-o-document')
                        ->schema([
                            Grid::make()->schema([
                                TextEntry::make('version'),
                                TextEntry::make('area')
                            ]),
                            TextEntry::make('observaciones')->html(),
                            Grid::make(3)->schema([
                                TextEntry::make('created_at')->label('Creado')
                                    ->since()
                                    ->dateTimeTooltip()
                                    ->columnSpan(1)->badge(),
                                TextEntry::make('updated_at')->label('Actualizado')
                                    ->columnSpan(1)->since()
                                    ->dateTimeTooltip()->badge(),
                                IconEntry::make('active')
                                    ->label('Activa')
                                    ->boolean()
                                    ->columnSpan(1),
                            ])
                        ])->columnSpan(1),
                    Section::make('Datos del equipo')->columnSpan(1)
                        ->icon('heroicon-o-wrench-screwdriver')
                        ->schema([
                            Grid::make()->schema([
                                TextEntry::make('equipo.nombre')
                                    ->label('Equipo'),
                                ImageEntry::make('equipo.foto')->label('Foto')
                                    ->extraImgAttributes(['class' => 'rounded-lg'])
                            ]),
                            Grid::make()->schema([
                                TextEntry::make('equipo.tag')
                                    ->label('Tag')
                                    ->columnSpan(1),
                                TextEntry::make('equipo.area')
                                    ->label('Area')
                            ]),
                        ]),
                    Section::make('Items')->columnSpanFull()
                        ->icon('heroicon-o-table-cells')
                        ->schema([
                            ViewItems::make('items')->hiddenLabel()
                        ])
                ])
            ]);
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
            'index' => Pages\ListHojaChequeos::route('/'),
            'create' => Pages\CreateHojaChequeo::route('/crear'),
            'view' => Pages\ViewHojaChequeo::route('/{record}'),
            'edit' => Pages\EditHojaChequeo::route('/{record}/editar'),
            'history' => Pages\HistoryHojaChequeo::route('/{record}/historial')
        ];
    }
}
