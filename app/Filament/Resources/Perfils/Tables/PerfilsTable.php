<?php

namespace App\Filament\Resources\Perfils\Tables;

use App\Models\Perfil;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PerfilsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')->label('Nombre'),
                IconColumn::make('acceso_total')
                    ->label('Acceso total')
                    ->true(),
                TextColumn::make('Hojas')
                    ->badge()
                    ->default(fn (Perfil $record) => count($record->hoja_ids)),
                TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Usuarios')
                    ->badge()
                    ->default(0),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
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
            ]);
    }
}
