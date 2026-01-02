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
                TextColumn::make('finalizado_en')
                    ->dateTime()->label('Fecha y hora')->sortable(),
                TextColumn::make('hojaChequeo.equipo.tag')->label('Tag')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
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
                EditAction::make()->hidden(! Auth::user()->hasRole(['Administrador','Supervisor'])),
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
