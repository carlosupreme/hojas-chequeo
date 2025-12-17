<?php

namespace App\Filament\Pages;

use Auth;
use Filament\Actions\ViewAction;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use App\Infolists\Components\ViewChequeoDiarioItems;
use App\Models\ChequeoDiario;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ChequeoHistorico extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected string $view = 'filament.pages.chequeo-historico';

    protected static ?string $title = 'Mis chequeos';

    public static function getNavigationGroup(): ?string {
        return 'Mantenimiento';
    }

    public static function canAccess(): bool {
        return Auth::user()->hasRole('Operador');
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
            ->persistSortInSession()
            ->persistFiltersInSession()
            ->filters([
                Filter::make('rango_fechas')
                      ->label('Rango de Fechas')
                      ->schema([
                          DatePicker::make('fecha_inicio')
                                                     ->label('Fecha Inicio'),
                          DatePicker::make('fecha_fin')
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
            ->recordActions([
                ViewAction::make()->schema([Grid::make()->schema([
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
            ->toolbarActions([

            ]);
    }
}
