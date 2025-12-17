<?php

namespace App\Filament\Resources\Equipos;

use Illuminate\Database\Eloquent\Model;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\Equipos\Pages\ManageEquipos;
use App\Filament\Resources\EquipoResource\Pages;
use App\Models\Equipo;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EquipoResource extends Resource
{
    protected static ?string $model                = Equipo::class;
    protected static ?string $recordTitleAttribute = 'tag';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-wrench-screwdriver';

    public static function getNavigationGroup(): ?string {
        return 'Mantenimiento';
    }

    public static function getGlobalSearchResultDetails(Model $record): array {
        return [
            'Nombre' => $record->nombre,
            'Area'   => $record->area,
        ];
    }

    public static function form(Schema $schema): Schema {
        return $schema
            ->components([
                TextInput::make('nombre')
                                          ->required()
                                          ->maxLength(255),
                TextInput::make('tag')
                                          ->required()
                                          ->unique(ignoreRecord: true)
                                          ->maxLength(255),
                TextInput::make('area')
                                          ->required()
                                          ->maxLength(255),
                TextInput::make('numeroControl')
                                          ->label('Numero de control')
                                          ->maxLength(255),
                TextInput::make('revision')
                                          ->label('Revision')
                                          ->maxLength(255),
                FileUpload::make('foto')->image(),
            ]);
    }

    public static function table(Table $table): Table {
        return $table
            ->columns([
                TextColumn::make('nombre')
                                         ->sortable()
                                         ->searchable(),
                TextColumn::make('tag')
                                         ->sortable()
                                         ->searchable(),
                TextColumn::make('area')->extraAttributes(['class' => 'uppercase'])
                                         ->sortable()
                                         ->searchable(),
                ImageColumn::make('foto'),
                TextColumn::make('revision')
                                         ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('numeroControl')
                                         ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                                         ->dateTime()
                                         ->sortable()
                                         ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                                         ->dateTime()
                                         ->sortable()
                                         ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->persistSortInSession()
            ->persistFiltersInSession()
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()->closeModalByClickingAway(false),
                    DeleteAction::make(),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array {
        return [
            'index' => ManageEquipos::route('/'),
        ];
    }
}
