<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChequeoDiarioResource\Pages;
use App\Filament\Resources\ChequeoDiarioResource\RelationManagers;
use App\Infolists\Components\ViewChequeoDiarioItems;
use App\Models\ChequeoDiario;
use Carbon\Carbon;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;
class ChequeoDiarioResource extends Resource
{
    protected static ?string $model = ChequeoDiario::class;

    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';

    public static function canCreate(): bool {
        return false;
    }

    public static function getPluralLabel(): ?string {
        return "Chequeos";
    }

    public static function form(Form $form): Form {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table {
        return $table
            ->query(fn() => \Auth::user()->hasRole(["Administrador", 'Supervisor'])
                ? ChequeoDiario::query()
                : ChequeoDiario::where('operador_id', auth()->id())
            )
            ->columns([
                Tables\Columns\TextColumn::make('hojaChequeo.equipo.tag')->label("Tag")
                                         ->searchable()
                                         ->sortable(),
                Tables\Columns\TextColumn::make('hojaChequeo.equipo.nombre')
                                         ->searchable()
                                         ->sortable(),
                Tables\Columns\TextColumn::make('hojaChequeo.area')->label("Area")
                                         ->searchable()
                                         ->sortable(),
                Tables\Columns\TextColumn::make('hojaChequeo.version')->label("Version")
                                         ->searchable()
                                         ->numeric()
                                         ->sortable(),
                Tables\Columns\TextColumn::make('nombre_operador')->label("Operador")
                                         ->searchable(),
                Tables\Columns\TextColumn::make('observaciones'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->label("Fecha y hora")->sortable()
            ])
            ->filters([
                Filter::make('rango_fechas')
                      ->label('Rango de Fechas')
                      ->form([
                          Forms\Components\DatePicker::make('fecha_inicio')
                                                     ->label('Fecha Inicio'),
                          Forms\Components\DatePicker::make('fecha_fin')
                                                     ->label('Fecha Fin'),
                      ])
                      ->query(function (Builder $query, array $data): Builder {
                          return $query
                              ->when(
                                  $data['fecha_inicio'] ?? null,
                                  fn(Builder $query, $date) => $query->whereDate('created_at', '>=', $date)
                              )
                              ->when(
                                  $data['fecha_fin'] ?? null,
                                  fn(Builder $query, $date) => $query->whereDate('created_at', '<=', $date)
                              );
                      })
                      ->indicateUsing(function (array $data): array {
                          $indicators = [];

                          if ($data['fecha_inicio'] ?? null) {
                              $indicators[] = Indicator::make('Desde: ' . Carbon::parse($data['fecha_inicio'])
                                                                                ->format('d/m/Y'))
                                                       ->removeField('fecha_inicio');
                          }

                          if ($data['fecha_fin'] ?? null) {
                              $indicators[] = Indicator::make('Hasta: ' . Carbon::parse($data['fecha_fin'])
                                                                                ->format('d/m/Y'))
                                                       ->removeField('fecha_fin');
                          }

                          return $indicators;
                      }),
            ])
            ->persistSortInSession()
            ->persistFiltersInSession()
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()->hidden(!\Auth::user()->hasRole('Administrador')),
                ])
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
                               Grid::make()->schema([
                                   TextEntry::make('hojaChequeo.version')->label("version"),
                                   TextEntry::make('hojaChequeo.area')->label("Area")
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
                               ImageEntry::make('firma_operador')->alignCenter()->maxWidth("200px")
                                         ->extraAttributes(["class" => "overflow-x-auto"])
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
            'index' => Pages\ListChequeoDiarios::route('/'),
            'edit'  => Pages\EditChequeoDiario::route('/{record}/edit'),
            'view'  => Pages\ViewChequeoDiario::route('/{record}'),
        ];
    }
}
