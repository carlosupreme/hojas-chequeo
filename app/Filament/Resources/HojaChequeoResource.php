<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HojaChequeoResource\Pages;
use App\Filament\Resources\HojaChequeoResource\RelationManagers;
use App\Models\HojaChequeo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HojaChequeoResource extends Resource
{
    protected static ?string $model = HojaChequeo::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench';

    public static function form(Form $form): Form {
        return $form
            ->schema([
                Forms\Components\Select::make('equipo_id')
                                       ->relationship('equipo', 'tag')
                                       ->required(),
                Forms\Components\TextInput::make('version')
                                          ->readOnly()
                                          ->default(1),
                Forms\Components\RichEditor::make('observaciones')
                                           ->disableToolbarButtons(['codeBlock', 'attachFiles'])
                                           ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('equipo.tag')
                                         ->sortable(),
                Tables\Columns\TextColumn::make('equipo.nombre')
                                         ->sortable(),
                Tables\Columns\TextColumn::make('version')
                                         ->numeric()
                                         ->sortable(),
                Tables\Columns\TextColumn::make('observaciones')
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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            'index'  => Pages\ListHojaChequeos::route('/'),
            'create' => Pages\CreateHojaChequeo::route('/create'),
            'view'   => Pages\ViewHojaChequeo::route('/{record}'),
            'edit'   => Pages\EditHojaChequeo::route('/{record}/edit'),
        ];
    }
}
