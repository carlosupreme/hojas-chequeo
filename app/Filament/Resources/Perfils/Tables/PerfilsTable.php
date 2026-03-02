<?php

namespace App\Filament\Resources\Perfils\Tables;

use App\Models\HojaChequeo;
use App\Models\Perfil;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PerfilsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Perfil $record) => $record->acceso_total
                        ? 'Acceso total a todas las hojas'
                        : count($record->hoja_ids).' hojas asignadas'),
                IconColumn::make('acceso_total')
                    ->label('Acceso total')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->sortable(),
                TextColumn::make('hojas_count')
                    ->label('Hojas')
                    ->badge()
                    ->color(fn (Perfil $record) => $record->acceso_total ? 'success' : 'primary')
                    ->default(fn (Perfil $record) => $record->acceso_total
                        ? HojaChequeo::where('encendido', true)->count()
                        : count($record->hoja_ids)),
                TextColumn::make('users.name')
                    ->label('Usuarios')
                    ->badge()
                    ->color('gray')
                    ->limitList(3)
                    ->separator(', '),
                TextColumn::make('updated_at')
                    ->label('Última modificación')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('nombre')
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make()
                        ->visible(function (Perfil $record) {
                            return $record->users->count() === 0;
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Sin perfiles')
            ->emptyStateDescription('Crea un perfil para agrupar permisos de acceso a hojas de chequeo.')
            ->striped()
            ->paginated([10, 25, 50]);
    }
}
