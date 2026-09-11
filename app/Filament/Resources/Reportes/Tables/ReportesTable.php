<?php

namespace App\Filament\Resources\Reportes\Tables;

use Carbon\Carbon;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ReportesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                if (Auth::user()->hasRole(['Administrador', 'Supervisor'])) {
                    return $query->orderByDesc('fecha');
                }

                return $query->where('user_id', Auth::id())->orderByDesc('fecha');
            })
            ->columns([
                TextColumn::make('fecha')
                    ->label('Reportado el:')
                    ->dateTime('M d, Y H:m')
                    ->sortable(),
                TextColumn::make('equipo.tag')
                    ->label('Equipo:')
                    ->searchable(),
                TextColumn::make('falla')
                    ->label('Falla')
                    ->limit(30)
                    ->tooltip(fn (TextColumn $column): ?string => $column->getState()),
                TextColumn::make('observaciones')
                    ->label('Observaciones')
                    ->limit(30)
                    ->tooltip(fn (TextColumn $column): ?string => $column->getState()),
                TextColumn::make('area')
                    ->label('Area'),
                TextColumn::make('prioridad')
                    ->label('Prioridad')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'alta' => 'danger',
                        'media' => 'warning',
                        'baja' => 'success',
                    }),
                SelectColumn::make('estado')
                    ->label('Estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'realizado' => 'Realizado',
                    ])
                    ->disabled(fn () => ! Auth::user()->hasRole(['Administrador', 'Supervisor']))
                    ->selectablePlaceholder(false)
                    ->default('Pendiente'),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'realizado' => 'Realizado',
                    ]),

                SelectFilter::make('prioridad')
                    ->label('Prioridad')
                    ->options([
                        'alta' => 'Alta',
                        'media' => 'Media',
                        'baja' => 'Baja',
                    ]),

                SelectFilter::make('equipo')
                    ->label('Equipo')
                    ->relationship('equipo', 'nombre')
                    ->searchable()
                    ->preload(),

                Filter::make('fecha')
                    ->label('Rango de fechas')
                    ->schema([
                        DatePicker::make('desde')
                            ->label('Desde')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        DatePicker::make('hasta')
                            ->label('Hasta')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['desde'] ?? null,
                                fn (Builder $q, $date) => $q->whereDate('fecha', '>=', $date),
                            )
                            ->when(
                                $data['hasta'] ?? null,
                                fn (Builder $q, $date) => $q->whereDate('fecha', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['desde'] ?? null) {
                            $indicators[] = Indicator::make('Desde: '.Carbon::parse($data['desde'])->format('d/m/Y'))
                                ->removeField('desde');
                        }

                        if ($data['hasta'] ?? null) {
                            $indicators[] = Indicator::make('Hasta: '.Carbon::parse($data['hasta'])->format('d/m/Y'))
                                ->removeField('hasta');
                        }

                        return $indicators;
                    })
                    ->columns(2),
            ])
            ->persistFiltersInSession()
            ->recordActions([
                DeleteAction::make()->visible(fn () => Auth::user()->hasRole(['Administrador', 'Supervisor'])),
                EditAction::make()->visible(fn () => Auth::user()->hasRole(['Administrador', 'Supervisor'])),
            ]);
    }
}
