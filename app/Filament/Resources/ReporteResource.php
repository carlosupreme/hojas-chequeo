<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReporteResource\Pages;
use App\Filament\Resources\ReporteResource\RelationManagers;
use App\Models\Reporte;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReporteResource extends Resource
{
    protected static ?string $model = Reporte::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';

    public static function form(Form $form): Form {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table {
        return $table
            ->columns([
                TextColumn::make('Equipo')
                          ->state(fn(Reporte $record) => $record->tag ? $record->tag : ($record->equipment ? $record->equipment : $record->vehicle))
                          ->searchable()
                          ->sortable(),
                TextColumn::make('fecha')->label('Reportado el')
                          ->date()
                          ->sortable(),
                TextColumn::make('failure')->label('Falla'),
                TextColumn::make('observations')->label('Observaciones'),
                TextColumn::make('area')->label('Area')->searchable(),
                TextColumn::make('department')->label('Departamento')->searchable(),
                TextColumn::make('priority')
                          ->label('Prioridad')
                          ->badge()
                          ->color(fn(string $state): string => match (strtolower($state)) {
                              'baja'  => 'gray',
                              'media' => 'warning',
                              'alta'  => 'danger'
                          }),
                TextColumn::make('created_at')->label('Creado el')
                          ->dateTime()
                          ->sortable()
                          ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label('Actualizado el')
                          ->dateTime()
                          ->sortable()
                          ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array {
        return [
            //
        ];
    }

    public static function getPages(): array {
        return [
            'index'  => Pages\ListReportes::route('/'),
            'create' => Pages\CreateReporte::route('/create'),
            'edit'   => Pages\EditReporte::route('/{record}/edit'),
        ];
    }
}
