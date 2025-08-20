<?php

namespace App\Filament\Widgets;

use App\Models\Equipo;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Carbon;

class EquipmentAlertsWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table {
        return $table
            ->heading("Alertas de equipos")
            ->query($this->getQuery())
            ->columns([
                Tables\Columns\TextColumn::make('tag')
                                         ->label('Tag de Equipo')
                                         ->searchable()
                                         ->sortable()
                                         ->description(fn($record) => $record->last_checked)
                                         ->weight('semibold'),

                Tables\Columns\TextColumn::make('total_alerts')
                                         ->label('Alertas Activas')
                                         ->badge()
                                         ->color(fn($state) => $state > 0 ? 'danger' : 'success')
                                         ->formatStateUsing(fn($state) => number_format($state))
                                         ->icon(fn($state) => $state > 0 ? 'heroicon-o-bell-alert' : 'heroicon-o-check-circle')
                                         ->sortable(),
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
                                  fn(Builder $query, $date) => $query->whereDate('alertas.updated_at', '>=', $date)
                              )
                              ->when(
                                  $data['fecha_fin'] ?? null,
                                  fn(Builder $query, $date) => $query->whereDate('alertas.updated_at', '<=', $date)
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
                SelectFilter::make('tag')
                            ->label('Filtrar por Equipo')
                            ->searchable()
                            ->options(Equipo::pluck('tag', 'id')->toArray())
                            ->query(function (Builder $query, $data) {
                                if ($data['value']) {
                                    $query->where('equipos.id', $data['value']);
                                }
                            }),
            ], layout: Tables\Enums\FiltersLayout::Modal)
            ->persistSortInSession()
            ->persistFiltersInSession()
            ->actions([
                // Acciones adicionales si son necesarias
            ])
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('No se encontraron equipos')
            ->emptyStateDescription('Crea tu primer equipo para comenzar')
            ->emptyStateIcon('heroicon-o-beaker');
    }

    protected function getQuery(): Builder {
        return Equipo::query()
                     ->select([
                         'equipos.id',
                         'equipos.tag',
                         DB::raw('MAX(hoja_chequeos.created_at) as last_checked'),
                         DB::raw('COALESCE(SUM(alertas.contador), 0) as total_alerts')
                     ])
                     ->leftJoin('hoja_chequeos', 'equipos.id', '=', 'hoja_chequeos.equipo_id')
                     ->leftJoin('items', 'hoja_chequeos.id', '=', 'items.hoja_chequeo_id')
                     ->leftJoin('alertas', 'items.id', '=', 'alertas.item_id')
                     ->groupBy('equipos.id', 'equipos.tag');
    }

    public function getHeading(): string {
        return 'Monitoreo de Alertas de Equipos';
    }

    public function getDescription(): ?string {
        return 'Seguimiento histórico de alertas con filtros por fecha';
    }
}
