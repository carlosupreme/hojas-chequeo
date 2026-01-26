<?php

namespace App\Filament\Resources\Chequeos\Tables;

use App\Filament\Pages\CreateChequeo;
use App\Filament\Resources\Chequeos\ChequeosResource;
use App\Models\Equipo;
use App\Models\HojaEjecucion;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DatePicker;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ChequeosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // 1. PERFORMANCE: Eager load relationships to fix N+1
            ->modifyQueryUsing(function (Builder $query) {
                $query->with(['hojaChequeo.equipo', 'user', 'turno']);

                // Role Logic
                if (Auth::user()->hasRole(['Administrador', 'Supervisor'])) {
                    return $query->orderByDesc('created_at');
                }

                return $query->where('user_id', Auth::id())->orderByDesc('created_at');
            })
            // 2. QUICK FILTERS (Tabs at top)
            ->filtersTriggerAction(fn ($action) => $action->button()->label('Filtros Avanzados'))
            ->filters([
                // Filter by Area (Crucial for Supervisors)
                SelectFilter::make('area')
                    ->label('Área')
                    ->options(fn () => Equipo::distinct()->pluck('area', 'area')->toArray())
                    ->query(fn (Builder $query, array $data) => $query->when(
                        $data['value'],
                        fn ($q) => $q->whereHas('hojaChequeo.equipo', fn ($eq) => $eq->where('area', $data['value']))
                    )),

                // Filter by Shift
                SelectFilter::make('turno')
                    ->relationship('turno', 'nombre')
                    ->label('Turno'),

                // Filter by Status (Classic)
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'En Proceso',
                        'finished' => 'Finalizado',
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['value'] === 'pending', fn ($q) => $q->whereNull('finalizado_en'))
                        ->when($data['value'] === 'finished', fn ($q) => $q->whereNotNull('finalizado_en'))
                    ),

                // Date Range
                Filter::make('created_at')
                    ->label('Fecha de Ejecución')
                    ->schema([
                        DatePicker::make('desde')->label('Desde'),
                        DatePicker::make('hasta')->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['desde'], fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['hasta'], fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ], layout: FiltersLayout::Modal) // Clean Modal layout for filters
            ->columns([
                // COLUMN 5: DATE
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d M, Y H:i')
                    ->sortable()
                    ->toggleable(),
                // COLUMN 1: EQUIPMENT INFO (Stacked)
                TextColumn::make('hojaChequeo.equipo.tag')
                    ->label('Equipo')
                    ->weight(FontWeight::Bold)
                    ->description(fn (HojaEjecucion $record) => $record->hojaChequeo->equipo->nombre)
                    ->searchable(['tag', 'nombre'])
                    ->sortable()
                    ->color('primary'),

                // COLUMN 2: AREA (Helpful context)
                TextColumn::make('observaciones')
                    ->label('Observaciones')
                    ->badge()
                    ->color('warning')
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        // Only render the tooltip if the column contents exceeds the length limit.
                        return $state;
                    }),

                // COLUMN 4: OPERATOR & SHIFT (Stacked)
                TextColumn::make('nombre_operador')
                    ->label('Operador')
                    ->description(fn (HojaEjecucion $record) => $record->turno->nombre ?? 'Sin turno')
                    ->searchable()
                    ->icon('heroicon-m-user'),
                // COLUMN 3: STATUS & DURATION
                TextColumn::make('status_label')
                    ->label('Estado')
                    ->badge()
                    ->state(fn (HojaEjecucion $record) => $record->finalizado_en ? 'Finalizado' : 'En Curso')
                    ->color(fn (string $state) => match ($state) {
                        'Finalizado' => 'success',
                        'En Curso' => 'warning',
                    })
                    ->icon(fn (string $state) => match ($state) {
                        'Finalizado' => 'heroicon-m-check-badge',
                        'En Curso' => 'heroicon-m-clock',
                    })
                    // Show duration below the status badge
                    ->description(function (HojaEjecucion $record) {
                        if (! $record->finalizado_en) {
                            return 'Iniciado '.$record->created_at->diffForHumans();
                        }
                        // Calculate duration manually for better formatting
                        $duration = $record->created_at->diff($record->finalizado_en);

                        return $duration->format('%Hh %Im %Ss');
                    }),

            ])
            ->defaultSort('created_at', 'desc')
            ->persistSortInSession()
            ->persistFiltersInSession()
            ->recordActions([
                ActionGroup::make([
                    Action::make('resume')
                        ->label('Continuar')
                        ->icon('heroicon-m-play')
                        ->color('warning')
                        ->visible(fn (HojaEjecucion $record) => is_null($record->finalizado_en) || auth()->user()
                            ->canModifyDate())
                        ->url(function (HojaEjecucion $record) {
                            $b = ChequeosResource::getUrl('index');

                            return CreateChequeo::getUrl()."?h=$record->hoja_chequeo_id&e=$record->id&b=$b";
                        }),

                    Action::make('view-chequeo')
                        ->icon('heroicon-m-eye')
                        ->modalFooterActions([])
                        ->hidden(fn (HojaEjecucion $record) => is_null($record->finalizado_en))
                        ->label('Ver Detalle')
                        ->modalWidth(Width::FiveExtraLarge)
                        ->modalContent(fn (HojaEjecucion $record) => view('livewire.view-chequeo', compact('record'))),

                    DeleteAction::make()
                        ->visible(fn () => Auth::user()->isAdmin()),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->tooltip('Acciones'),
            ]);
    }
}
