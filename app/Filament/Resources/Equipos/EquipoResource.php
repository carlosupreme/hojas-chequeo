<?php

namespace App\Filament\Resources\Equipos;

use App\Filament\Resources\Equipos\Pages\ManageEquipos;
use App\Models\Equipo;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EquipoResource extends Resource
{
    protected static ?string $model = Equipo::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected static ?string $recordTitleAttribute = 'tag';

    public static function getNavigationGroup(): ?string
    {
        return 'Mantenimiento';
    }

    public static function form(Schema $schema): Schema
    {
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
                Repeater::make('Especificaciones')->columnSpanFull()
                    ->relationship('specs')->label('Especificaciones')
                    ->schema([
                        TextInput::make('tipo')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('unidad')->label('Unidad de medida')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('min')->label('Valor mínimo')
                            ->required()
                            ->numeric(),
                        TextInput::make('optimo')->label('Valor óptimo')
                            ->required()
                            ->numeric(),
                        TextInput::make('max')->label('Valor máximo')
                            ->required()
                            ->numeric(),
                    ])
                    ->columns(3)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('tag')
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
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageEquipos::route('/'),
        ];
    }
}
