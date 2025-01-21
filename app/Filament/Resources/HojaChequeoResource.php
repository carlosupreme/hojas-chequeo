<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HojaChequeoResource\Pages;
use App\Filament\Resources\HojaChequeoResource\RelationManagers;
use App\Infolists\Components\ViewItems;
use App\Models\HojaChequeo;
use Filament\Forms\Form;
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
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HojaChequeoResource extends Resource
{
    protected static ?string $model = HojaChequeo::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench';

    public static function table(Table $table): Table {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('equipo.tag')
                                         ->sortable(),
                Tables\Columns\TextColumn::make('equipo.nombre')
                                         ->sortable(),
                Tables\Columns\TextColumn::make('version')
                                         ->numeric()
                                         ->sortable(),
                Tables\Columns\ToggleColumn::make('active')
                                           ->label('Publicada')
                                           ->beforeStateUpdated(fn($record) => HojaChequeo::where('equipo_id', $record->equipo_id)
                                                                                          ->update(['active' => false])),
                Tables\Columns\TextColumn::make('observaciones')
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
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('Historial')
                                     ->url(fn(HojaChequeo $record): string => HojaChequeoResource::getUrl('history', ['record' => $record]))
                                     ->icon('heroicon-o-calendar'),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }


    public static function infolist(Infolist $infolist): Infolist {
        return $infolist
            ->schema([
                Grid::make()->schema([
                    Section::make('Datos de la hoja de chequeo')
                           ->icon('heroicon-o-document')
                           ->schema([
                               TextEntry::make('version'),
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
                                   ImageEntry::make('equipo.foto')->label('foto')
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


    public static function getRelations(): array {
        return [
            //
        ];
    }

    public static function getPages(): array {
        return [
            'index'  => Pages\ListHojaChequeos::route('/'),
            'create' => Pages\CreateHojaChequeo::route('/crear'),
            'view'   => Pages\ViewHojaChequeo::route('/{record}'),
            'edit'   => Pages\EditHojaChequeo::route('/{record}/editar'),
            'history' => Pages\HistoryHojaChequeo::route('/{record}/historial')
        ];
    }
}
