<?php

namespace App\Filament\Pages;

use App\HojaChequeoArea;
use App\Models\Reporte;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Pages\Page;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReporteHistorico extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.reporte-historico';

    protected static ?string $title = 'Mis reportes';

    public static function getNavigationGroup(): ?string
    {
        return 'Reportes';
    }

    public static function canAccess(): bool
    {
        return \Auth::user()->hasRole('Operador');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Reporte::where('user_id', auth()->id())->with('equipo'))
            ->columns([
                TextColumn::make('equipo.tag')
                    ->label('Equipo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('fecha')->label('Reportado el')
                    ->date()
                    ->sortable(),
                TextColumn::make('failure')->label('Falla'),
                TextColumn::make('observations')->label('Observaciones'),
                TextColumn::make('area')->label('Area')->extraAttributes(['class' => 'uppercase'])->searchable(),
                TextColumn::make('priority')
                    ->label('Prioridad')
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'baja' => 'gray',
                        'media' => 'warning',
                        'alta' => 'danger'
                    }),
                TextColumn::make('estado')->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'pendiente' => 'warning',
                        'realizado' => 'success',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'pendiente' => 'heroicon-o-clock',
                        'realizado' => 'heroicon-o-check',
                    }),
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
                    ->options(fn () => array_combine(
                        array_map(fn (HojaChequeoArea $area) => $area->value, HojaChequeoArea::cases()),
                        array_map(fn (HojaChequeoArea $area) => $area->value, HojaChequeoArea::cases())
                    )),
            ])
            ->actions([
                ViewAction::make()->infolist([
                    \Filament\Infolists\Components\Section::make('Estado del Reporte')
                        ->schema([

                            \Filament\Infolists\Components\Grid::make(3)
                                ->schema([
                                    TextEntry::make('created_at')
                                        ->label('Fecha de Reporte')
                                        ->dateTime('d/m/Y H:i')
                                        ->icon('heroicon-o-calendar'),

                                    TextEntry::make('priority')
                                        ->label('Prioridad')
                                        ->badge()
                                        ->color(fn (string $state): string => match (strtolower($state)) {
                                            'baja' => 'gray',
                                            'media' => 'warning',
                                            'alta' => 'danger',
                                        })
                                        ->icon('heroicon-o-exclamation-triangle'),

                                    TextEntry::make('estado')
                                        ->label('Estado')
                                        ->default('Pendiente')
                                        ->badge()
                                        ->color(fn (string $state): string => match (strtolower($state)) {
                                            'pendiente' => 'warning',
                                            'realizado' => 'success',
                                        })
                                        ->icon(fn (string $state): string => match ($state) {
                                            'pendiente' => 'heroicon-o-clock',
                                            'realizado' => 'heroicon-o-check',
                                        }),
                                ]),
                        ])
                        ->collapsible(),

                    \Filament\Infolists\Components\Section::make('Información del Equipo')
                        ->schema([
                            \Filament\Infolists\Components\Grid::make(2)
                                ->schema([
                                    TextEntry::make('equipo.tag')
                                        ->label('Tag del Equipo')
                                        ->icon('heroicon-o-tag'),

                                    TextEntry::make('area')
                                        ->label('Área')
                                        ->icon('heroicon-o-building-office'),
                                ]),

                            \Filament\Infolists\Components\Grid::make(2)
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

                    \Filament\Infolists\Components\Section::make('Detalles de la Falla')
                        ->schema([
                            \Filament\Infolists\Components\Grid::make(1)
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

                    \Filament\Infolists\Components\Section::make('Evidencia Fotográfica')
                        ->schema([
                            ImageEntry::make('photo')
                                ->hiddenLabel()
                                ->columnSpanFull()
                                ->alignCenter()
                                ->circular(false)
                                ->height(500),
                        ])
                        ->collapsible(),

                    \Filament\Infolists\Components\Section::make('Información de Seguimiento')
                        ->schema([
                            \Filament\Infolists\Components\Grid::make(2)
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
                                        ->default(fn ($record) => $record->user?->name ?? $record->name)
                                        ->icon('heroicon-o-user'),
                                ]),
                        ])
                        ->collapsible(),
                ]),
            ])
            ->bulkActions([

            ]);
    }
}
