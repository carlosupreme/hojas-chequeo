<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SimbologiaResource\Pages;
use App\Filament\Resources\SimbologiaResource\RelationManagers;
use App\Models\Simbologia;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use TomatoPHP\FilamentIcons\Components\IconPicker;

class SimbologiaResource extends Resource
{
    protected static ?string $model = Simbologia::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';

    protected static ?string $recordTitleAttribute = 'nombre';


    public static function form(Form $form): Form {
        return $form
            ->schema([
                IconPicker::make('icono')
                          ->searchable()->preload()
                          ->required(),
                Forms\Components\ColorPicker::make('color')
                                            ->required()
                                            ->default('#000000'),
                Forms\Components\TextInput::make('nombre')
                                          ->live(onBlur: true)
                                          ->unique(ignoreRecord: true)
                                          ->required()
                                          ->maxLength(255),
                Forms\Components\TextInput::make('descripcion')
                                          ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('icono')
                          ->icon(fn($record) => $record->icono)
                          ->color(fn(Simbologia $record) => Color::hex($record->color)),
                Tables\Columns\TextColumn::make('nombre')
                                         ->searchable(),
                Tables\Columns\TextColumn::make('descripcion')
                                         ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                                         ->dateTime()
                                         ->sortable()
                                         ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                                         ->dateTime()
                                         ->sortable()
                                         ->toggleable(isToggledHiddenByDefault: true),
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

    public static function getPages(): array {
        return [
            'index' => Pages\ManageSimbologias::route('/'),
        ];
    }
}
