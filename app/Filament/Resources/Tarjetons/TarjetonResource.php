<?php

namespace App\Filament\Resources\Tarjetons;

use App\Filament\Resources\Tarjetons\Pages\ManageTarjetons;
use App\Models\Equipo;
use App\Models\Tarjeton;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class TarjetonResource extends Resource
{
    protected static ?string $model = Tarjeton::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getPluralLabel(): string
    {
        return 'Tarjetones';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Mantenimiento';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                // ── Left column: equipo + time entries (spans 2 cols) ──
                Grid::make(1)
                    ->columnSpan(2)
                    ->schema([
                        Section::make('Equipo')
                            ->icon('heroicon-o-cpu-chip')
                            ->schema([
                                Select::make('equipo_id')
                                    ->options(Equipo::calderas()->pluck('tag', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, $state) {
                                        if ($state) {
                                            $set('hora_encendido', now());
                                            $set('encendido_por', Auth::user()->name);
                                            $set('estado', 'encendido');
                                        }
                                    })
                                    ->required()
                                    ->columnSpanFull(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                // Encendido
                                Fieldset::make('Encendido')
                                    ->columns(1)
                                    ->schema([
                                        DateTimePicker::make('hora_encendido')
                                            ->default(now())
                                            ->seconds(false)
                                            ->label('Fecha / Hora')
                                            ->prefixIcon('heroicon-o-play')
                                            ->live()
                                            ->afterStateUpdated(function (Set $set) {
                                                $set('encendido_por', auth()->user()->name);
                                            }),

                                        TextInput::make('encendido_por')
                                            ->default(fn () => auth()->user()->name)
                                            ->label('Responsable')
                                            ->prefixIcon('heroicon-o-user')
                                            ->required()
                                            ->maxLength(255),
                                    ]),

                                // Apagado
                                Fieldset::make('Apagado')
                                    ->columns(1)
                                    ->schema([
                                        DateTimePicker::make('hora_apagado')
                                            ->seconds(false)
                                            ->label('Fecha / Hora')
                                            ->prefixIcon('heroicon-o-stop')
                                            ->live()
                                            ->afterStateUpdated(function (Set $set, $state, Get $get) {
                                                if ($state) {
                                                    $set('apagado_por', auth()->user()->name);
                                                    $set('estado', 'apagado');

                                                    $horaEncendido = $get('hora_encendido');
                                                    if ($horaEncendido && $state <= $horaEncendido) {
                                                        Notification::make()
                                                            ->warning()
                                                            ->title('Atención')
                                                            ->body('La hora de apagado debe ser posterior a la de encendido')
                                                            ->send();
                                                    }
                                                }
                                            }),

                                        TextInput::make('apagado_por')
                                            ->label('Responsable')
                                            ->prefixIcon('heroicon-o-user')
                                            ->maxLength(255),
                                    ]),
                            ]),

                        // Tiempo calculado — full width banner
                        TextEntry::make('tiempo_total')
                            ->label('Tiempo Total de Operación')
                            ->icon('heroicon-o-clock')
                            ->state(function (Get $get): string {
                                $horaEncendido = $get('hora_encendido');
                                $horaApagado = $get('hora_apagado');

                                if ($horaEncendido && $horaApagado) {
                                    try {
                                        $inicio = Carbon::parse($horaEncendido);
                                        $fin = Carbon::parse($horaApagado);

                                        if ($fin->greaterThan($inicio)) {
                                            $totalMin = (int) $inicio->diffInMinutes($fin);
                                            $h = intdiv($totalMin, 60);
                                            $m = $totalMin % 60;

                                            return "{$h}h {$m}m";
                                        }

                                        return 'Verificar horarios';
                                    } catch (\Exception $e) {
                                        return 'Formato inválido';
                                    }
                                }

                                return 'Registra ambas horas para calcular';
                            })
                            ->live(),

                        // Observaciones
                        Textarea::make('observaciones')
                            ->label('Observaciones')
                            ->placeholder('Notas sobre el funcionamiento del equipo...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                // ── Right sidebar: estado + falla (spans 1 col) ──
                Grid::make(1)
                    ->columnSpan(1)
                    ->schema([
                        Section::make('Estado')
                            ->icon('heroicon-o-signal')
                            ->schema([
                                ToggleButtons::make('estado')
                                    ->options([
                                        'encendido' => 'Encendido',
                                        'apagado' => 'Apagado',
                                        'mantenimiento' => 'Mantenimiento',
                                    ])
                                    ->icons([
                                        'encendido' => 'heroicon-o-play',
                                        'apagado' => 'heroicon-o-stop',
                                        'mantenimiento' => 'heroicon-o-wrench-screwdriver',
                                    ])
                                    ->colors([
                                        'encendido' => 'success',
                                        'apagado' => 'danger',
                                        'mantenimiento' => 'warning',
                                    ])
                                    ->default('encendido')
                                    ->required()
                                    ->inline()
                                    ->live(),
                            ]),

                        Section::make('Falla de Vapor')
                            ->icon('heroicon-o-fire')
                            ->description('Reportar si el equipo presenta falla de vapor')
                            ->collapsible()
                            ->collapsed()
                            ->schema([
                                ToggleButtons::make('falla_vapor')
                                    ->label('¿Falla detectada?')
                                    ->boolean('Sí', 'No')
                                    ->default(false)
                                    ->icons([
                                        true => 'heroicon-s-fire',
                                        false => 'heroicon-o-check-circle',
                                    ])
                                    ->colors([
                                        true => 'danger',
                                        false => 'success',
                                    ])
                                    ->inline()
                                    ->live(),

                                Textarea::make('falla_vapor_descripcion')
                                    ->label('Descripción de la falla')
                                    ->placeholder('Describe la falla detectada...')
                                    ->rows(3)
                                    ->visible(fn (Get $get): bool => (bool) $get('falla_vapor')),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('equipo.tag')
                    ->label('Equipo')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('hora_encendido')
                    ->label('Fecha')
                    ->date('d/M/Y')
                    ->sortable()
                    ->description(fn (Tarjeton $record): string => $record->hora_encendido?->locale('es')->translatedFormat('l') ?? ''),

                TextColumn::make('hora_encendido')
                    ->label('Encendido')
                    ->badge()
                    ->color('success')
                    ->dateTime('d/M H:i')
                    ->placeholder('N/A'),

                TextColumn::make('hora_apagado')
                    ->label('Apagado')
                    ->badge()
                    ->color('danger')
                    ->dateTime('d/M H:i')
                    ->placeholder('En operación'),

                TextColumn::make('tiempo_operacion_formateado')
                    ->label('Tiempo Total')
                    ->badge()
                    ->color('info')
                    ->placeholder('Calculando...'),

                TextColumn::make('encendido_por')
                    ->label('Op. Encendido')
                    ->limit(12)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 12 ? $state : null;
                    })
                    ->placeholder('N/A'),

                TextColumn::make('apagado_por')
                    ->label('Op. Apagado')
                    ->limit(12)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 12 ? $state : null;
                    })
                    ->placeholder('Pendiente'),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->colors([
                        'success' => 'encendido',
                        'danger' => 'apagado',
                        'warning' => 'mantenimiento',
                    ])
                    ->badge(fn (string $state): string => ucfirst($state)),
                IconColumn::make('falla_vapor')
                    ->label('Falla de vapor')
                    ->boolean()
                    ->trueIcon('heroicon-s-fire')
                    ->falseIcon('heroicon-o-minus-circle')
                    ->trueColor('danger')
                    ->falseColor('gray')
                    ->tooltip(fn (Tarjeton $record): ?string => $record->falla_vapor && $record->falla_vapor_descripcion
                        ? "Falla: {$record->falla_vapor_descripcion}"
                        : null),
            ])
            ->filters([
                SelectFilter::make('equipo_id')
                    ->relationship('equipo', 'tag')
                    ->label('Equipo')
                    ->multiple()
                    ->preload(),

                Filter::make('fecha')
                    ->schema([
                        DatePicker::make('desde')
                            ->default(now()->subDays(7)),
                        DatePicker::make('hasta')
                            ->default(now()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['desde'],
                                fn (Builder $query, $date): Builder => $query->whereDate('hora_encendido', '>=', $date),
                            )
                            ->when(
                                $data['hasta'],
                                fn (Builder $query, $date): Builder => $query->whereDate('hora_encendido', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['desde'] ?? null) {
                            $indicators['desde'] = 'Desde: '.Carbon::parse($data['desde'])
                                ->format('d/M/Y');
                        }
                        if ($data['hasta'] ?? null) {
                            $indicators['hasta'] = 'Hasta: '.Carbon::parse($data['hasta'])
                                ->format('d/M/Y');
                        }

                        return $indicators;
                    }),

                SelectFilter::make('estado')
                    ->options([
                        'encendido' => 'Encendido',
                        'apagado' => 'Apagado',
                        'mantenimiento' => 'Mantenimiento',
                    ])
                    ->multiple(),

                Filter::make('solo_hoy')
                    ->label('Solo Hoy')
                    ->query(fn (Builder $query): Builder => $query->whereDate('hora_encendido', today()))
                    ->toggle(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('toggle_estado')
                    ->label(fn (Tarjeton $record) => $record->estado === 'encendido' ? 'Apagar' : 'Encender')
                    ->icon(fn (Tarjeton $record) => $record->estado === 'encendido' ? 'heroicon-o-stop' : 'heroicon-o-play')
                    ->color(fn (Tarjeton $record) => $record->estado === 'encendido' ? 'danger' : 'success')
                    ->action(function (Tarjeton $record) {
                        if ($record->estado === 'encendido') {
                            $record->update([
                                'hora_apagado' => now(),
                                'apagado_por' => auth()->user()->name,
                                'estado' => 'apagado',
                            ]);

                            Notification::make()
                                ->success()
                                ->title('Equipo Apagado')
                                ->body("El equipo {$record->equipo->tag} ha sido apagado correctamente")
                                ->send();
                        } else {
                            $record->update([
                                'hora_encendido' => now(),
                                'encendido_por' => auth()->user()->name,
                                'estado' => 'encendido',
                            ]);

                            Notification::make()
                                ->success()
                                ->title('Equipo Encendido')
                                ->body("El equipo {$record->equipo->tag} ha sido encendido correctamente")
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading(fn (Tarjeton $record) => $record->estado === 'encendido' ? 'Apagar Equipo' : 'Encender Equipo')
                    ->modalDescription(fn (Tarjeton $record) => '¿Confirmas que quieres '.($record->estado === 'encendido' ? 'apagar' : 'encender')." el equipo {$record->equipo->tag}?"),

                Action::make('toggle_falla_vapor')
                    ->label(fn (Tarjeton $record) => $record->falla_vapor ? 'Limpiar Falla Vapor' : 'Reportar Falla Vapor')
                    ->icon(fn (Tarjeton $record) => $record->falla_vapor ? 'heroicon-o-check-circle' : 'heroicon-o-fire')
                    ->color(fn (Tarjeton $record) => $record->falla_vapor ? 'gray' : 'danger')
                    ->schema(fn (Tarjeton $record) => ! $record->falla_vapor ? [
                        Textarea::make('falla_vapor_descripcion')
                            ->label('Descripción de la falla')
                            ->placeholder('Describe la falla de vapor detectada...')
                            ->required()
                            ->rows(3),
                    ] : [])
                    ->requiresConfirmation()
                    ->modalHeading(fn (Tarjeton $record) => $record->falla_vapor
                        ? 'Limpiar Falla de Vapor'
                        : 'Reportar Falla de Vapor')
                    ->modalDescription(fn (Tarjeton $record) => $record->falla_vapor
                        ? '¿Confirmas que deseas limpiar el registro de falla de vapor?'
                        : 'Ingresa una descripción de la falla de vapor detectada.')
                    ->action(function (Tarjeton $record, array $data) {
                        if ($record->falla_vapor) {
                            $record->update([
                                'falla_vapor' => false,
                                'falla_vapor_descripcion' => null,
                            ]);
                            Notification::make()
                                ->success()
                                ->title('Falla de vapor eliminada')
                                ->send();
                        } else {
                            $record->update([
                                'falla_vapor' => true,
                                'falla_vapor_descripcion' => $data['falla_vapor_descripcion'],
                            ]);
                            Notification::make()
                                ->warning()
                                ->title('Falla de vapor registrada')
                                ->body($data['falla_vapor_descripcion'])
                                ->send();
                        }
                    }),

                Action::make('mantenimiento')
                    ->label('Mantenimiento')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->color('warning')
                    ->action(function (Tarjeton $record) {
                        $record->update([
                            'estado' => 'mantenimiento',
                            'apagado_por' => auth()->user()->name,
                            'hora_apagado' => now(),
                        ]);
                    })
                    ->visible(fn (Tarjeton $record) => $record->estado !== 'mantenimiento'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),

                    BulkAction::make('cambiar_estado')
                        ->label('Cambiar Estado')
                        ->icon('heroicon-o-arrow-path')
                        ->schema([
                            Select::make('nuevo_estado')
                                ->label('Nuevo Estado')
                                ->options([
                                    'encendido' => 'Encendido',
                                    'apagado' => 'Apagado',
                                    'mantenimiento' => 'Mantenimiento',
                                ])
                                ->required(),
                        ])
                        ->action(function (array $data, Collection $records) {
                            $records->each(function (Tarjeton $record) use ($data) {
                                $record->update([
                                    'estado' => $data['nuevo_estado'],
                                    'apagado_por' => auth()->user()->name,
                                ]);
                            });

                            Notification::make()
                                ->success()
                                ->title('Estados Actualizados')
                                ->body("Se actualizaron {$records->count()} registros")
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('hora_encendido', 'desc')
            ->poll('60s') // Auto-refresh cada minuto
            ->persistSortInSession()
            ->persistSearchInSession()
            ->persistFiltersInSession();
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageTarjetons::route('/'),
            'bitacora' => Pages\BitacoraReporte::route('/bitacora'),
        ];
    }
}
