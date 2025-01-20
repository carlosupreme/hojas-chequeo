<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $navigationIcon       = 'heroicon-o-users';

    public static function form(Form $form): Form {
        return $form
            ->schema([
                TextInput::make('name')
                         ->label(ucfirst(__('validation.attributes.name')))
                         ->required()
                         ->maxLength(255),
                TextInput::make('email')
                         ->label(ucfirst(__('validation.attributes.email')))
                         ->email()
                         ->unique(ignoreRecord: true)
                         ->required()
                         ->maxLength(255)
                         ->live(),
                TextInput::make('password')
                         ->label(ucfirst(__('validation.attributes.password')))
                         ->password()
                         ->revealable()
                         ->required()
                         ->maxLength(255)
                         ->hiddenOn('edit'),
                TextInput::make('profile_photo_path')
                         ->label(ucfirst(__('validation.attributes.photo')))
                         ->maxLength(2048),
                Forms\Components\Select::make('roles')
                      ->relationship('roles', 'name')
                      ->multiple()
                      ->required()
                      ->placeholder('Selecciona los roles del usuario')
                      ->preload()
                      ->searchable(),
            ]);
    }

    public static function table(Table $table): Table {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatarUrl')
                           ->label(ucfirst(__('validation.attributes.photo')))
                           ->circular(),
                TextColumn::make('name')
                          ->label(ucfirst(__('validation.attributes.name')))
                          ->searchable()
                          ->sortable(),
                TextColumn::make('email')
                          ->label(ucfirst(__('validation.attributes.email')))
                          ->searchable(),
                TextColumn::make('roles.name')
                          ->searchable()
                          ->badge(),
                TextColumn::make('created_at')
                          ->label(ucfirst(__('validation.attributes.created_at')))
                          ->dateTime()
                          ->sortable()
                          ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                          ->label(__('filament-shield::filament-shield.column.updated_at'))
                          ->dateTime()
                          ->sortable()
                          ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('email_verified_at')
                             ->label(__('Correo verificado'))
                             ->nullable()
                             ->placeholder('Todos')
                             ->trueLabel('Verificados')
                             ->falseLabel('No verificados'),
                Tables\Filters\SelectFilter::make('Rol')
                            ->relationship('roles', 'name')
                            ->preload()
            ])
            ->filtersFormColumns(2)
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
            'index' => Pages\ManageUsers::route('/'),
        ];
    }

    public static function getModelLabel(): string {
        return __('User'); // Singular
    }

    public static function getNavigationBadge(): ?string {
        return strval(static::getEloquentQuery()->count());
    }

    public static function getNavigationBadgeColor(): string|array|null {
        return \Filament\Support\Colors\Color::Neutral;

    }
}
