<?php

namespace App\Filament\Resources\Users;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\Users\Pages\ManageUsers;
use Filament\Support\Colors\Color;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
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
    protected static string | \BackedEnum | null $navigationIcon       = 'heroicon-o-users';

    public static function getNavigationGroup(): ?string {
        return 'Administracion';
    }

    public static function form(Schema $schema): Schema {
        return $schema
            ->components([
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
                         ->maxLength(255),
                Select::make('roles')
                                       ->relationship('roles', 'name')
                                       ->multiple()
                                       ->required()
                                       ->placeholder('Selecciona los roles del usuario')
                                       ->preload()
                                       ->searchable(),
                Select::make('perfil_id')
                                       ->relationship('perfil', 'name')
                                       ->required()
                                       ->placeholder('Selecciona el perfil del usuario')
                                       ->preload()
                                       ->searchable(),
            ]);
    }

    public static function table(Table $table): Table {
        return $table
            ->columns([
                ImageColumn::make('avatarUrl')
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
                TextColumn::make('perfil.name')
                          ->searchable()
                          ->badge()->color('gray'),
                TextColumn::make('created_at')
                          ->label(ucfirst(__('validation.attributes.created_at')))
                          ->dateTime()
                          ->sortable()
                          ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                          ->label(ucfirst(__('validation.attributes.updated_at')))
                          ->dateTime()
                          ->sortable()
                          ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('email_verified_at')
                                            ->label(__('Correo verificado'))
                                            ->nullable()
                                            ->placeholder('Todos')
                                            ->trueLabel('Verificados')
                                            ->falseLabel('No verificados'),
                SelectFilter::make('Rol')
                                           ->relationship('roles', 'name')
                                           ->preload()
            ])
            ->filtersFormColumns(2)
            ->recordActions([
                EditAction::make()->closeModalByClickingAway(false),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array {
        return [
            'index' => ManageUsers::route('/'),
        ];
    }

    public static function getModelLabel(): string {
        return __('User'); // Singular
    }

    public static function getNavigationBadge(): ?string {
        return strval(static::getEloquentQuery()->count());
    }

    public static function getNavigationBadgeColor(): string|array|null {
        return Color::Neutral;

    }
}
