<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TarjetonResource\Pages;
use App\Filament\Resources\TarjetonResource\RelationManagers;
use App\Models\Tarjeton;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TarjetonResource extends Resource
{
    protected static ?string $model = Tarjeton::class;

    public static function getPluralLabel(): string {
        return "Tarjetones";
    }

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('equipo_id')
                    ->relationship('equipo', 'tag')
                    ->required(),
                Forms\Components\DatePicker::make('fecha')
                    ->required(),
                Forms\Components\TextInput::make('hora_encendido')
                    ->maxLength(255),
                Forms\Components\TextInput::make('hora_apagado')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('equipo.tag')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('hora_encendido'),
                Tables\Columns\TextColumn::make('hora_apagado')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageTarjetons::route('/'),
        ];
    }
}
