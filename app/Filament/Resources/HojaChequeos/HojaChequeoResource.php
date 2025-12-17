<?php

namespace App\Filament\Resources\HojaChequeos;

use App\Filament\Resources\HojaChequeos\HojaChequeoResource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use App\Filament\Resources\HojaChequeos\Pages\ListHojaChequeos;
use App\Filament\Resources\HojaChequeos\Pages\CreateHojaChequeo;
use App\Filament\Resources\HojaChequeos\Pages\ViewHojaChequeo;
use App\Filament\Resources\HojaChequeos\Pages\EditHojaChequeo;
use App\Filament\Resources\HojaChequeos\Pages\HistoryHojaChequeo;
use App\Filament\Resources\HojaChequeoResource\Pages;
use App\Filament\Resources\HojaChequeoResource\RelationManagers;
use App\HojaChequeoArea;
use App\Infolists\Components\ViewItems;
use App\Models\HojaChequeo;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class HojaChequeoResource extends Resource
{
    protected static ?string $model = HojaChequeo::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-wrench';


    public static function getNavigationGroup(): ?string {
        return 'Mantenimiento';
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('equipo.tag')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('equipo.nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('version')
                    ->searchable()
                    ->numeric()
                    ->sortable(),
                ToggleColumn::make('active')
                    ->label('Publicada')
                    ->beforeStateUpdated(fn($record) => HojaChequeo::
                        where('equipo_id', $record->equipo_id)
                        ->where('area', $record->area)
                        ->update(['active' => false])),
                TextColumn::make('area')->extraAttributes(['class' => 'uppercase'])
                    ->searchable()
                    ->sortable(),
                TextColumn::make('observaciones')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('area')
                    ->options([
                        HojaChequeoArea::TINTORERIA->value => HojaChequeoArea::TINTORERIA->value,
                        HojaChequeoArea::LAVANDERIA_INSTITUCIONAL->value => HojaChequeoArea::LAVANDERIA_INSTITUCIONAL->value,
                        HojaChequeoArea::CUARTO_DE_MAQUINAS->value => HojaChequeoArea::CUARTO_DE_MAQUINAS->value,
                    ]),
                TernaryFilter::make('active')
                    ->label("Publicada")
                    ->queries(
                        true: fn(Builder $query) => $query->where('active', true),
                        false: fn(Builder $query) => $query->where('active', false),
                        blank: fn(Builder $query) => $query
                    )
            ])
            ->persistSortInSession()
            ->persistFiltersInSession()
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    Action::make('Historial')
                        ->url(fn(HojaChequeo $record): string => HojaChequeoResource::getUrl('history', ['record' => $record]))
                        ->icon('heroicon-o-calendar'),
                    EditAction::make(),
                    DeleteAction::make(),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }


    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
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
            'index' => ListHojaChequeos::route('/'),
            'create' => CreateHojaChequeo::route('/crear'),
            'view' => ViewHojaChequeo::route('/{record}'),
            'edit' => EditHojaChequeo::route('/{record}/editar'),
            'history' => HistoryHojaChequeo::route('/{record}/historial')
        ];
    }
}
