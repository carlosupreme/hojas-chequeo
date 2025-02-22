<?php

namespace App\Filament\Pages;

use App\Infolists\Components\ViewChequeoDiarioItems;
use App\Models\ChequeoDiario;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class ChequeoHistorico extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string $view = 'filament.pages.chequeo-historico';

    protected static ?string $title = 'Mis chequeos';

    public static function canAccess(): bool {
        return \Auth::user()->hasRole('Operador');
    }

    public function table(Table $table): Table {
        return $table
            ->query(ChequeoDiario::where('operador_id', auth()->id()))
            ->columns([
                TextColumn::make('hojaChequeo.equipo.tag')->label('Tag')
                          ->searchable()
                          ->sortable(),
                TextColumn::make('hojaChequeo.equipo.nombre')
                          ->searchable()
                          ->sortable(),
                TextColumn::make('hojaChequeo.area')->label('Area')
                          ->searchable()
                          ->sortable(),
                TextColumn::make('hojaChequeo.version')->label('Version')
                          ->searchable()
                          ->numeric()
                          ->sortable(),
                TextColumn::make('nombre_operador')->label('Operador')
                          ->searchable(),
                TextColumn::make('observaciones'),
                TextColumn::make('created_at')->dateTime()->label('Fecha y hora')
            ])
            ->filters([
                //
            ])
            ->actions([
                \Filament\Tables\Actions\ViewAction::make()->infolist([Grid::make()->schema([
                    Section::make('Datos de la hoja de chequeo')
                           ->icon('heroicon-o-document')
                           ->schema([
                               Grid::make()->schema([
                                   TextEntry::make('hojaChequeo.version')->label('version'),
                                   TextEntry::make('hojaChequeo.area')->label('Area')
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
                                   TextEntry::make('nombre_operador')
                                            ->label('Operador')
                                            ->columnSpan(1),
                               ]),

                           ])->columnSpan(1),
                    Section::make('Datos del equipo')->columnSpan(1)
                           ->icon('heroicon-o-wrench-screwdriver')
                           ->schema([
                               Grid::make()->schema([
                                   TextEntry::make('hojaChequeo.equipo.nombre')
                                            ->label('Equipo'),
                                   ImageEntry::make('hojaChequeo.equipo.foto')->label('Foto')
                                             ->extraImgAttributes(['class' => 'rounded-lg'])
                               ]),
                               Grid::make()->schema([
                                   TextEntry::make('hojaChequeo.equipo.tag')
                                            ->label('Tag')
                                            ->columnSpan(1),
                                   TextEntry::make('hojaChequeo.equipo.area')
                                            ->label('Area')
                               ]),
                           ]),
                    Section::make('Items')->columnSpanFull()
                           ->icon('heroicon-o-table-cells')
                           ->schema([
                               ViewChequeoDiarioItems::make('itemsChequeoDiario')->hiddenLabel()
                           ])
                ])]),
            ])
            ->bulkActions([

            ]);
    }
}
