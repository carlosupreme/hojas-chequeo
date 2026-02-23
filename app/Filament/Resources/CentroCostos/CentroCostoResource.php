<?php

namespace App\Filament\Resources\CentroCostos;

use App\Filament\Resources\CentroCostos\Pages\ManageCentroCostos;
use App\Models\CentroCosto;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CentroCostoResource extends Resource
{
    protected static ?string $model = CentroCosto::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;

    protected static ?string $recordTitleAttribute = 'nombre';

    protected static ?string $modelLabel = 'Centro de Costo';

    protected static ?string $pluralModelLabel = 'Centros de Costo';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return 'Administración';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información General')
                    ->icon('heroicon-o-building-office')
                    ->description('Datos básicos del centro de costo')
                    ->schema([
                        TextInput::make('nombre')
                            ->label('Nombre')
                            ->required()
                            ->placeholder('Ej: Tintorería, Producción...')
                            ->maxLength(255),

                    ])->columnSpanFull(),

                Section::make('Días Festivos')
                    ->icon('heroicon-o-calendar-days')
                    ->description('Fechas en que este centro de costo no opera')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('offDays')
                            ->relationship('offDays')
                            ->hiddenLabel()
                            ->schema([
                                DatePicker::make('fecha')
                                    ->label('Fecha')
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('D d/m/Y')
                                    ->format('Y-m-d')
                                    ->prefixIcon('heroicon-o-calendar'),

                                TextInput::make('motivo')
                                    ->label('Motivo (opcional)')
                                    ->placeholder('Ej: Año Nuevo, Navidad...')
                                    ->maxLength(100),
                            ])
                            ->addActionLabel('+ Agregar día festivo')
                            ->reorderable(false)
                            ->columns(2)
                            ->defaultItems(0)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nombre')
            ->columns([
                TextColumn::make('nombre')
                    ->label('Centro de Costo')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('turnos_count')
                    ->label('Turnos')
                    ->counts('turnos')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('off_days_count')
                    ->label('Días Festivos')
                    ->counts('offDays')
                    ->badge()
                    ->color('warning')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
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
            'index' => ManageCentroCostos::route('/'),
        ];
    }
}
