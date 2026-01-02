<?php

namespace App\Filament\Resources\Reportes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReportesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fecha')
                    ->label('Reportado el:')
                    ->dateTime('M d, Y')
                    ->sortable(),
                TextColumn::make('equipo.tag')
                    ->label('Equipo:')
                    ->searchable(),
                textColumn::make('failure')
                    ->label('Falla')
                    ->limit(30)
                    ->tooltip(fn (TextColumn $column): ?string => $column->getState()),
                TextColumn::make('observations')
                    ->label('Observaciones')
                    ->limit(30)
                    ->tooltip(fn (TextColumn $column): ?string => $column->getState()),
                textColumn::make('area')
                    ->label('Area'),
                textColumn::make('priority')
                    ->label('Prioridad')
                    ->badge() // Convierte el texto en una etiqueta (tag)
                    ->color(fn (string $state): string => match ($state) {
                        'Alta' => 'danger',   // Rojo
                        'Media' => 'warning', // Amarillo/Naranja
                        'Baja' => 'success',  // Verde
                    }),
                SelectColumn::make('estado')
                    ->label('Estado')
                    ->options([
                        'Pendiente' => 'Pendiente',
                        'Realizado' => 'Realizado',
                    ])
                    ->selectablePlaceholder(false)
                    ->default('Pendiente'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                DeleteAction::make(),
                EditAction::make(),

            ])
            ->toolbarActions([
                //                BulkActionGroup::make([
                //                    DeleteBulkAction::make(),
                //                ]),
            ]);
    }
}
