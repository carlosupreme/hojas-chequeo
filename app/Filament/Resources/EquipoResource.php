<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EquipoResource\Pages;
use App\Models\Equipo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EquipoResource extends Resource
{
    protected static ?string $model                = Equipo::class;
    protected static ?string $recordTitleAttribute = 'tag';

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array {
        return [
            'Nombre' => $record->nombre,
            'Area'   => $record->area,
        ];
    }

    public static function form(Form $form): Form {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                                          ->required()
                                          ->maxLength(255),
                Forms\Components\TextInput::make('tag')
                                          ->required()
                                          ->unique(ignoreRecord: true)
                                          ->maxLength(255),
                Forms\Components\TextInput::make('area')
                                          ->required()
                                          ->maxLength(255),
                Forms\Components\TextInput::make('numeroControl')
                                          ->label('Numero de control')
                                          ->maxLength(255),
                Forms\Components\TextInput::make('revision')
                                          ->label('Revision')
                                          ->maxLength(255),
                Forms\Components\FileUpload::make('foto')->image(),
            ]);
    }

    public static function table(Table $table): Table {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                                         ->sortable()
                                         ->searchable(),
                Tables\Columns\TextColumn::make('tag')
                                         ->sortable()
                                         ->searchable(),
                Tables\Columns\TextColumn::make('area')->extraAttributes(['class' => 'uppercase'])
                                         ->sortable()
                                         ->searchable(),
                Tables\Columns\ImageColumn::make('foto'),
                Tables\Columns\TextColumn::make('revision')
                                         ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('numeroControl')
                                         ->toggleable(isToggledHiddenByDefault: true),
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
            ->persistSortInSession()
            ->persistFiltersInSession()
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()->closeModalByClickingAway(false),
                    Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array {
        return [
            'index' => Pages\ManageEquipos::route('/'),
        ];
    }
}
