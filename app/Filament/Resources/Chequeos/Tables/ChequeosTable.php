<?php

namespace App\Filament\Resources\Chequeos\Tables;

use App\Filament\Pages\CreateChequeo;
use App\Filament\Resources\Chequeos\ChequeosResource;
use App\Models\Equipo;
use App\Models\HojaEjecucion;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ChequeosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordAction(function () {
                return 'view-chequeo';
            })
            // 1. PERFORMANCE: Eager load relationships to fix N+1
            ->modifyQueryUsing(function (Builder $query) {
                $query->with(['hojaChequeo.equipo', 'user', 'centroCosto']);

                // Role Logic
                if (Auth::user()->hasRole(['Administrador', 'Supervisor'])) {
                    return $query->orderByDesc('finalizado_en');
                }

                return $query->where('user_id', Auth::id())->orderByDesc('finalizado_en');
            })
            // 2. FILTERS: CentroCosto handled by tabs; status + area + dates in a clean modal
            ->filtersTriggerAction(
                fn ($action) => $action
                    ->button()
                    ->label('Filtros')
                    ->icon('heroicon-m-funnel')
            )
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->placeholder('Todos')
                    ->options([
                        'pending' => 'En Proceso',
                        'finished' => 'Finalizado',
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['value'] === 'pending', fn ($q) => $q->whereNull('finalizado_en'))
                        ->when($data['value'] === 'finished', fn ($q) => $q->whereNotNull('finalizado_en'))
                    ),

                TernaryFilter::make('es_ppm')
                    ->label('PPM')
                    ->placeholder('Todos')
                    ->trueLabel('Solo PPM')
                    ->falseLabel('Sin PPM'),

                SelectFilter::make('area')
                    ->label('Área')
                    ->placeholder('Todas')
                    ->options(fn () => Equipo::distinct()->orderBy('area')->pluck('area')
                        ->filter()
                        ->map(fn (string $a) => ucwords(mb_strtolower($a)))
                        ->unique()
                        ->sort()
                        ->values()
                        ->mapWithKeys(fn (string $a) => [$a => $a])
                        ->toArray()
                    )
                    ->query(fn (Builder $query, array $data) => $query->when(
                        $data['value'],
                        fn ($q) => $q->whereHas('hojaChequeo.equipo', fn ($eq) => $eq->whereRaw('LOWER(area) = ?', [strtolower($data['value'])]))
                    )),

                Filter::make('created_at')
                    ->label('Fecha de Ejecución')
                    ->schema([
                        Select::make('preset')
                            ->label('Período rápido')
                            ->placeholder('Rango personalizado')
                            ->options([
                                'today' => 'Hoy',
                                'yesterday' => 'Ayer',
                                'this_week' => 'Esta semana',
                                'this_fortnight' => 'Esta quincena',
                                'this_month' => 'Este mes',
                            ])
                            ->live()
                            ->afterStateUpdated(function (?string $state, Set $set) {
                                [$desde, $hasta] = match ($state) {
                                    'today' => [now()->toDateString(), now()->toDateString()],
                                    'yesterday' => [now()->subDay()->toDateString(), now()->subDay()->toDateString()],
                                    'this_week' => [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()],
                                    'this_fortnight' => now()->day <= 15
                                        ? [now()->startOfMonth()->toDateString(), now()->startOfMonth()->addDays(14)->toDateString()]
                                        : [now()->startOfMonth()->addDays(15)->toDateString(), now()->endOfMonth()->toDateString()],
                                    'this_month' => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
                                    default => [null, null],
                                };
                                $set('desde', $desde);
                                $set('hasta', $hasta);
                            }),
                        DatePicker::make('desde')->label('Desde')->native(false),
                        DatePicker::make('hasta')->label('Hasta')->native(false),
                    ])
                    ->columns(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['desde'], fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['hasta'], fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (! $data['desde'] && ! $data['hasta']) {
                            return null;
                        }
                        $desde = $data['desde'] ? Carbon::parse($data['desde'])->isoFormat('D MMM YYYY') : '…';
                        $hasta = $data['hasta'] ? Carbon::parse($data['hasta'])->isoFormat('D MMM YYYY') : '…';

                        return "Fecha: {$desde} — {$hasta}";
                    }),
            ], layout: FiltersLayout::Modal)
            ->columns([
                // COLUMN 5: DATES (created + finalized)
                TextColumn::make('created_at')
                    ->label('Fechas')
                    ->sortable()
                    ->toggleable()
                    ->state(fn (HojaEjecucion $record) => $record->created_at->isoFormat('D MMM YYYY, HH:mm'))
                    ->description(fn (HojaEjecucion $record) => $record->finalizado_en
                        ? '✓ '.$record->finalizado_en->isoFormat('D MMM YYYY, HH:mm')
                        : '⏳ En curso'
                    )
                    ->tooltip(fn (HojaEjecucion $record) => implode("\n", [
                        'Creado: '.$record->created_at->isoFormat('D MMM YYYY, HH:mm'),
                        $record->finalizado_en
                            ? 'Finalizado: '.$record->finalizado_en->isoFormat('D MMM YYYY, HH:mm')
                            : 'Sin finalizar',
                    ])),
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
                    ->description(fn (HojaEjecucion $record) => $record->centroCosto->nombre ?? 'Sin centro de costo')
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

                IconColumn::make('es_ppm')
                    ->label('PPM')
                    ->boolean()
                    ->trueIcon('heroicon-o-wrench-screwdriver')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('warning')
                    ->falseColor('gray'),

            ])
            ->defaultSort('finalizado_en', 'desc')
            ->persistSortInSession()
            ->persistFiltersInSession()
            ->recordActions([
                ActionGroup::make([
                    Action::make('resume')
                        ->label('Continuar')
                        ->icon('heroicon-m-play')
                        ->color('warning')
                        ->visible(fn (HojaEjecucion $record) => is_null($record->finalizado_en) || Auth::user()
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
                        ->modalWidth(Width::SevenExtraLarge)
                        ->extraModalWindowAttributes(['x-init' => 'scrollModalToTop($el)'])
                        ->modalContent(fn (HojaEjecucion $record) => view('livewire.view-chequeo', compact('record'))),

                    DeleteAction::make()
                        ->visible(fn () => Auth::user()->isAdmin()),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->tooltip('Acciones'),
            ]);
    }
}
