<?php

namespace App\Filament\Resources\Perfils;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\Perfils\Pages\ManagePerfils;
use App\Filament\Resources\PerfilResource\Pages;
use App\Filament\Resources\PerfilResource\RelationManagers;
use App\Forms\Components\SelectHojas;
use App\Models\Perfil;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PerfilResource extends Resource
{
    protected static ?string $model = Perfil::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user-circle';

    public static function getPluralLabel(): ?string {
        return "Perfiles";
    }

    public static function getNavigationGroup(): ?string {
        return 'Administracion';
    }

    public static function form(Schema $schema): Schema {
        return $schema
            ->components([
                TextInput::make("name")->label("Nombre")->required(),
                SelectHojas::make("hoja_ids")
                           ->columnSpanFull()
                           ->label('Hojas de Chequeo')
                           ->required(),
            ]);
    }

    public static function table(Table $table): Table {
        return $table
            ->columns([
                TextColumn::make("name")->label("Nombre"),
                TextColumn::make('Hojas')
                                         ->badge()
                                         ->default(fn(Perfil $record) => count($record->hoja_ids)),
                TextColumn::make('users_count')
                                         ->counts('users')
                                         ->label("Usuarios")
                                         ->badge()
                                         ->default(0),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()->closeModalByClickingAway(false),
                DeleteAction::make()->hidden(fn(Perfil $record): bool => $record->users()->exists()),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array {
        return [
            'index' => ManagePerfils::route('/'),
        ];
    }
}
