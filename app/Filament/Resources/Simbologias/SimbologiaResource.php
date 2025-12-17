<?php

namespace App\Filament\Resources\Simbologias;

use Filament\Schemas\Schema;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\Simbologias\Pages\ManageSimbologias;
use App\Filament\Resources\SimbologiaResource\Pages;
use App\Filament\Resources\SimbologiaResource\RelationManagers;
use App\Models\Simbologia;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Table;
use TomatoPHP\FilamentIcons\Components\IconPicker;
use TomatoPHP\FilamentIcons\Models\Icon;

class SimbologiaResource extends Resource
{
    protected static ?string $model = Simbologia::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-check-circle';

    protected static ?string $recordTitleAttribute = 'nombre';

    public static function getNavigationGroup(): ?string {
        return 'Administracion';
    }


    public static function form(Schema $schema): Schema {
        return $schema
            ->components([
                IconPicker::make('icono')
                          ->options(fn() => Icon::query()
                                                ->where('name', 'like', 'heroicon-c-check')
                                                ->orWhere('name', 'like', 'heroicon-o-x-mark')       // Checked but failed
                                                ->orWhere('name', 'like', 'heroicon-o-exclamation-triangle')  // Needs attention
                                                ->orWhere('name', 'like', 'heroicon-o-minus-circle') // Not applicable
                                                ->orWhere('name', 'like', 'heroicon-o-eye')          // Visually inspected
                                                ->orWhere('name', 'like', 'heroicon-o-clock')        // Pending/Deferred
                                                ->orWhere('name', 'like', 'heroicon-o-shield-exclamation')  // Safety issue
                                                ->orWhere('name', 'like', 'heroicon-o-wrench')       // Maintenance needed
                                                ->orWhere('name', 'like', 'heroicon-o-no-symbol')    // Prohibited action
                                                ->orWhere('name', 'like', 'heroicon-o-question-mark-circle')  // Unclear status
                                                ->limit(10)
                                                ->pluck('label', 'name')
                                                ->toArray()
                          )
                          ->preload()
                          ->required(),
                ColorPicker::make('color')
                                            ->required()
                                            ->default('#000000'),
                TextInput::make('nombre')
                                          ->live(onBlur: true)
                                          ->unique(ignoreRecord: true)
                                          ->required()
                                          ->maxLength(255),
                TextInput::make('descripcion')
                                          ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table {
        return $table
            ->columns([
                IconColumn::make('icono')
                                         ->icon(fn($record) => $record->icono)
                                         ->color(fn(Simbologia $record) => Color::generateV3Palette($record->color)),
                TextColumn::make('nombre')
                                         ->searchable(),
                TextColumn::make('descripcion')
                                         ->searchable(),

                TextColumn::make('created_at')
                                         ->badge()
                                         ->dateTime('d M Y H:i')
                                         ->label('Creado el')
                                         ->sortable()
                                         ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                                         ->badge()
                                         ->dateTime('d M Y H:i')
                                         ->label('Actualizado el')
                                         ->sortable()
                                         ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()->closeModalByClickingAway(false),
                DeleteAction::make(),
                ViewAction::make()
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array {
        return [
            'index' => ManageSimbologias::route('/'),
        ];
    }
}
