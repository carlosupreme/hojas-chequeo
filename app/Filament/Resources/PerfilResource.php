<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PerfilResource\Pages;
use App\Filament\Resources\PerfilResource\RelationManagers;
use App\Forms\Components\SelectHojas;
use App\Models\Perfil;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PerfilResource extends Resource
{
    protected static ?string $model = Perfil::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    public static function getPluralLabel(): ?string {
        return "Perfiles";
    }

    public static function form(Form $form): Form {
        return $form
            ->schema([
                Forms\Components\TextInput::make("name")->label("Nombre")->required(),
                SelectHojas::make("hoja_ids")
                           ->columnSpanFull()
                           ->label('Hojas de Chequeo')
                           ->required(),
            ]);
    }

    public static function table(Table $table): Table {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make("name")->label("Nombre"),
                Tables\Columns\TextColumn::make('Hojas')
                                         ->badge()
                                         ->default(fn(Perfil $record) => count($record->hoja_ids)),
                Tables\Columns\TextColumn::make('users_count')
                                         ->counts('users')
                                         ->label("Usuarios")
                                         ->badge()
                                         ->default(0),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->hidden(fn(Perfil $record): bool => $record->users()->exists()),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array {
        return [
            'index' => Pages\ManagePerfils::route('/'),
        ];
    }
}
