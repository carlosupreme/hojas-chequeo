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
use TomatoPHP\FilamentIcons\Components\IconPicker;
use TomatoPHP\FilamentIcons\Models\Icon;

class SimbologiaResource extends Resource
{
    protected static ?string $model = Simbologia::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';

    protected static ?string $recordTitleAttribute = 'nombre';

    public static function getNavigationGroup(): ?string {
        return 'Administracion';
    }


    public static function form(Form $form): Form {
        return $form
            ->schema([
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
                                         ->badge()
                                         ->dateTime('d M Y H:i')
                                         ->label('Creado el')
                                         ->sortable()
                                         ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                                         ->badge()
                                         ->dateTime('d M Y H:i')
                                         ->label('Actualizado el')
                                         ->sortable()
                                         ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->closeModalByClickingAway(false),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ViewAction::make()
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
