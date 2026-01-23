<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Usuario';

    public static function getNavigationGroup(): ?string
    {
        return 'Administración';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->live(),
                TextInput::make('password')
                    ->label('Contraseña')
                    ->password()
                    ->saved(fn (?string $state): bool => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->revealable()
                    ->maxLength(255),
                Select::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->required()
                    ->placeholder('Selecciona los roles del usuario')
                    ->preload()
                    ->searchable(),
                Select::make('perfil_id')
                    ->relationship('perfil', 'nombre')
                    ->required()
                    ->placeholder('Selecciona el perfil del usuario')
                    ->preload()
                    ->searchable(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('email')
                    ->label('Email address'),
                TextEntry::make('email_verified_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('perfil.id')
                    ->label('Perfil'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Usuario')->weight(FontWeight::Bold)
                    ->description(fn (User $record) => $record->email)
                    ->searchable(['name', 'email'])
                    ->sortable(),

                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Administrador' => 'danger',
                        'Supervisor' => 'warning',
                        'Operador' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('perfil.nombre')
                    ->label('Perfil')
                    ->icon('heroicon-m-identification')
                    ->iconColor('primary')
                    ->color('gray')
                    ->searchable(),
                TextColumn::make('turno.nombre')
                    ->searchable()
                    ->badge()
                    ->color('primary'),
                TextColumn::make('Edicion de fecha')
                    ->badge()
                    ->state(fn (User $user) => $user->canModifyDate()
                        ? 'Habilitado'
                        : 'Deshabilitado'
                    )
                    ->color(fn (User $user) => $user->canModifyDate()
                        ? 'success'
                        : 'gray'
                    ),

                TextColumn::make('created_at')
                    ->label('Creado el')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Actualizado el')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    Action::make('dar_permisos')
                        ->icon(Heroicon::Pencil)
                        ->color('warning')
                        ->action(function (User $record) {
                            if ($record->canModifyDate()) {
                                $record->disableModifyDate();
                            } else {
                                $record->enableModifyDate();
                            }
                        })
                        ->requiresConfirmation()
                        ->label(fn (User $record) => $record->canModifyDate() ? 'Revocar edición de fecha' : 'Habilitar edición de fecha'),
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
            'index' => ManageUsers::route('/'),
        ];
    }
}
