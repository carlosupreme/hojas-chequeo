<?php

namespace App\Filament\Resources\Reportes\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
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
                    ->dateTime('M d, Y')
                    ->sortable(),
                TextColumn::make('equipo.tag')
                    ->label('Equipo:')
                    ->searchable(),
                textColumn::make('falla')
                    ->label('Falla')
                    ->limit(30)
                    ->tooltip(fn (TextColumn $column): ?string => $column->getState()),
                TextColumn::make('observaciones')
                    ->label('Observaciones')
                    ->limit(30)
                    ->tooltip(fn (TextColumn $column): ?string => $column->getState()),
                textColumn::make('area')
                    ->label('Area'),
                textColumn::make('prioridad')
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
                //
            ])
            ->recordActions([
                DeleteAction::make()->visible(fn () => Auth::user()->hasRole(['Administrador', 'Supervisor'])),
                EditAction::make()->visible(fn () => Auth::user()->hasRole(['Administrador', 'Supervisor'])),
            ]);
    }
}
