<?php

namespace App\Filament\Resources\Reportes;

use Auth;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\Reportes\Pages\ListReportes;
use App\Filament\Resources\Reportes\Pages\CreateReporte;
use App\Filament\Resources\Reportes\Pages\EditReporte;
use App\Filament\Resources\ReporteResource\Pages;
use App\Filament\Resources\ReporteResource\RelationManagers;
use App\HojaChequeoArea;
use App\Models\Reporte;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReporteResource extends Resource
{
    protected static ?string $model = Reporte::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-inbox-arrow-down';

     public static function canAccess(): bool {
        return Auth::user()->hasRole(["Administrador", 'Supervisor']);
    }

    public static function getPluralLabel(): ?string {
        return "Solicitudes de mantenimiento";
    }

    public static function getNavigationGroup(): ?string {
        return 'Reportes';
    }



    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Estado del Reporte')
                    ->schema([

                        Grid::make(3)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Fecha de Reporte')
                                    ->dateTime('d/m/Y H:i')
                                    ->icon('heroicon-o-calendar'),

                                TextEntry::make('priority')
                                    ->label('Prioridad')
                                    ->badge()
                                    ->color(fn(string $state): string => match (strtolower($state)) {
                                        'baja' => 'gray',
                                        'media' => 'warning',
                                        'alta' => 'danger',
                                    })
                                    ->icon('heroicon-o-exclamation-triangle'),

                                TextEntry::make('estado')
                                    ->label('Estado')
                                    ->default('Pendiente')
                                    ->badge()
                                    ->color(fn(string $state): string => match (strtolower($state)) {
                                        'pendiente' => 'warning',
                                        'realizado' => 'success',
                                    })
                                    ->icon(fn(string $state): string => match ($state) {
                                        'pendiente' => 'heroicon-o-clock',
                                        'realizado' => 'heroicon-o-check',
                                    }),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Información del Equipo')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('equipo.tag')
                                    ->label('Tag del Equipo')
                                    ->icon('heroicon-o-tag'),

                                TextEntry::make('area')
                                    ->label('Área')
                                    ->icon('heroicon-o-building-office'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('equipo.nombre')
                                    ->label('Nombre del Equipo')
                                    ->icon('heroicon-o-wrench'),

                                TextEntry::make('equipo.area')
                                    ->label('Ubicación del equipo')
                                    ->icon('heroicon-o-map-pin'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Detalles de la Falla')
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                TextEntry::make('failure')
                                    ->label('Descripción de la Falla')
                                    ->columnSpanFull()
                                    ->icon('heroicon-o-exclamation-circle'),

                                TextEntry::make('observations')
                                    ->label('Observaciones Adicionales')
                                    ->columnSpanFull()
                                    ->icon('heroicon-o-clipboard-document-list'),
                            ]),
                    ]),

                Section::make('Evidencia Fotográfica')
                    ->schema([
                        ImageEntry::make('photo')
                            ->hiddenLabel()
                            ->columnSpanFull()
                            ->alignCenter()
                            ->circular(false)
                            ->height(500),
                    ])
                    ->collapsible(),

                Section::make('Información de Seguimiento')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Creado el')
                                    ->dateTime('d/m/Y H:i')
                                    ->icon('heroicon-o-clock'),

                                TextEntry::make('updated_at')
                                    ->label('Última Actualización')
                                    ->dateTime('d/m/Y H:i')
                                    ->icon('heroicon-o-arrow-path'),

                                TextEntry::make('user')
                                    ->label('Reportado por')
                                    ->default(fn($record) => $record->user?->name ?? $record->name)
                                    ->icon('heroicon-o-user'),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos del reporte')
                    ->collapsible()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nombre')
                                    ->required()
                                    ->default(fn() => Auth::user()->name),
                                DatePicker::make('fecha')
                                    ->label('Fecha')
                                    ->default(Carbon::now())
                                    ->readOnly()
                                    ->native(false)
                                    ->closeOnDateSelection(),

                                Select::make('priority')->label('Prioridad')
                                    ->default('baja')
                                    ->options([
                                        'alta' => 'Alta',
                                        'media' => 'Media',
                                        'baja' => 'Baja'
                                    ])
                                    ->required()
                                    ->native(false)
                            ]),
                    ]),

                Section::make('Detalles del equipo')
                    ->description('Selecciona el equipo que presenta la falla')
                    ->collapsible()
                    ->schema([
                        Grid::make()
                            ->schema([
                                Select::make('equipo_id')
                                    ->label('Tag del Equipo')
                                    ->relationship('equipo', 'tag')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Select::make('area')->label('Area')
                                    ->options(fn() => array_combine(
                                        array_map(fn(HojaChequeoArea $area) => $area->value, HojaChequeoArea::cases()),
                                        array_map(fn(HojaChequeoArea $area) => $area->value, HojaChequeoArea::cases())
                                    ))
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                            ]),
                    ]),

                Section::make('Detalles de la falla')
                    ->description('Describe el problema que presenta')
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                TextInput::make('failure')
                                    ->label('Falla')
                                    ->required()
                                    ->columnSpanFull(),
                                Textarea::make('observations')
                                    ->label('Observaciones')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Section::make('Evidencia')
                    ->schema([
                        FileUpload::make('photo')
                            ->label('Foto')
                            ->image()
                            ->imageEditor()
                            ->columnSpanFull(),
                    ]),
            ]);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('equipo.tag')
                    ->label("Equipo")
                    ->searchable()
                    ->sortable(),
                TextColumn::make('fecha')->label('Reportado el')
                    ->date()
                    ->sortable(),
                TextColumn::make('failure')->label('Falla'),
                TextColumn::make('observations')->label('Observaciones'),
                TextColumn::make('area')->label('Area')->searchable(),
                TextColumn::make('priority')
                    ->label('Prioridad')
                    ->badge()
                    ->color(fn(string $state): string => match (strtolower($state)) {
                        'baja' => 'gray',
                        'media' => 'warning',
                        'alta' => 'danger'
                    }),
                SelectColumn::make("estado")->label("Estado")->options([
                    "pendiente" => "Pendiente",
                    "realizado" => "Realizado"
                ])->selectablePlaceholder(false),
                TextColumn::make('created_at')->label('Creado el')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label('Actualizado el')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('priority')
                    ->label('Prioridad')
                    ->options([
                        'alta' => 'Alta',
                        'media' => 'Media',
                        'baja' => 'Baja',
                    ]),
                SelectFilter::make('area')->label('Area')
                    ->options(fn() => array_combine(
                        array_map(fn(HojaChequeoArea $area) => $area->value, HojaChequeoArea::cases()),
                        array_map(fn(HojaChequeoArea $area) => $area->value, HojaChequeoArea::cases())
                    ))
            ])
            ->persistSortInSession()
            ->persistFiltersInSession()
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReportes::route('/'),
            'create' => CreateReporte::route('/create'),
            'edit' => EditReporte::route('/{record}/edit'),
        ];
    }
}
