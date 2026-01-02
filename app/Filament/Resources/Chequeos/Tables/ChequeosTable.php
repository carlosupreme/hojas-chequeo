<?php

namespace App\Filament\Resources\Chequeos\Tables;

use App\Models\HojaEjecucion;
use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ChequeosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn () => Auth::user()->hasRole(['Administrador', 'Supervisor'])
                ? HojaEjecucion::query()->orderByDesc('finalizado_en')
                : HojaEjecucion::where('user_id', Auth::id())->orderByDesc('finalizado_en')
            )
            ->columns([
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->getStateUsing(fn (HojaEjecucion $record): string => $record->finalizado_en ? 'Finalizado' : 'En Proceso')
                    ->color(fn (string $state): string => match ($state) {
                        'Finalizado' => 'success',
                        'En Proceso' => 'warning',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'Finalizado' => 'heroicon-m-check-circle',
                        'En Proceso' => 'heroicon-m-clock',
                    }),
                TextColumn::make('finalizado_en')
                    ->dateTime()
                    ->label('Finalizado')
                    ->sortable()
                    ->placeholder('Pendiente')
                    ->tooltip(fn (HojaEjecucion $record): ?string => $record->finalizado_en ? 'Finalizado '.$record->finalizado_en->diffForHumans() : null),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Iniciado')
                    ->sortable(),
                TextColumn::make('duration')
                    ->label('Duración')
                    ->state(function (HojaEjecucion $record) {
                        if (! $record->finalizado_en) {
                            return $record->created_at->diffForHumans(null, true);
                        }

                        return $record->created_at->diffForHumans($record->finalizado_en, true);
                    })
                    ->description(fn (HojaEjecucion $record) => $record->finalizado_en ? 'Tiempo total' : 'Transcurrido')
                    ->icon('heroicon-m-clock'),
                TextColumn::make('hojaChequeo.equipo.tag')->label('Tag')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('hojaChequeo.equipo.nombre')->label('Equipo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('turno.nombre')->label('Area')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nombre_operador')->label('Operador')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'En Proceso (Pendiente)',
                        'finished' => 'Finalizado',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['value'] === 'pending', fn ($query) => $query->whereNull('finalizado_en'))
                            ->when($data['value'] === 'finished', fn ($query) => $query->whereNotNull('finalizado_en'));
                    }),
                Filter::make('rango_fechas')
                    ->label('Rango de Fechas')
                    ->schema([
                        DatePicker::make('fecha_inicio')->label('Fecha Inicio'),
                        DatePicker::make('fecha_fin')->label('Fecha Fin'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['fecha_inicio'] ?? null,
                                fn (Builder $query, $date) => $query->whereDate('finalizado_en', '>=', $date)
                            )
                            ->when(
                                $data['fecha_fin'] ?? null,
                                fn (Builder $query, $date) => $query->whereDate('finalizado_en', '<=', $date)
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['fecha_inicio'] ?? null) {
                            $indicators[] = Indicator::make('Desde: '.Carbon::parse($data['fecha_inicio'])
                                ->format('d/m/Y'))
                                ->removeField('fecha_inicio');
                        }

                        if ($data['fecha_fin'] ?? null) {
                            $indicators[] = Indicator::make('Hasta: '.Carbon::parse($data['fecha_fin'])
                                ->format('d/m/Y'))
                                ->removeField('fecha_fin');
                        }

                        return $indicators;
                    }),
            ])
            ->paginationPageOptions([10, 25, 50])
            ->persistSortInSession()
            ->persistFiltersInSession()
            ->recordActions([
                EditAction::make()->hidden(! Auth::user()->hasRole(['Administrador', 'Supervisor'])),
                ViewAction::make(),
                DeleteAction::make()->hidden(! Auth::user()->hasRole('Administrador')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
