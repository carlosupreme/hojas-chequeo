<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TarjetonResource\Pages;
use App\Filament\Resources\TarjetonResource\RelationManagers;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Tarjeton;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Carbon\Carbon;
 use Filament\Forms\Get;
use Illuminate\Validation\Rule;
use Filament\Notifications\Notification;
use Illuminate\Validation\Rules\Unique;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Closure;
use Illuminate\Support\Facades\DB;

class TarjetonResource extends Resource
{
    protected static ?string $model = Tarjeton::class;

    public static function getPluralLabel(): string {
        return "Tarjetones";
    }

    public static function getNavigationGroup(): ?string {
        return 'Mantenimiento';
    }

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
        Forms\Components\Select::make('equipo_id')
                    ->relationship('equipo', 'tag')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (Forms\Set $set, $state) {
                        if ($state) {
                            $set('fecha', now()->format('Y-m-d'));
                            $set('hora_encendido', now()->format('H:i'));
                            $set('encendido_por', auth()->user()->name);
                            $set('estado', 'encendido');
                        }
                    })
                    ->unique(ignoreRecord: true, modifyRuleUsing: function (Unique $rule, callable $get) {
                        return $rule
                        ->where('fecha', $get('fecha'))
                        ->where('equipo_id', $get('equipo_id'));
                    })
                    ->required(),
        Forms\Components\DatePicker::make('fecha')
            ->default(now())
            ->required()
            ->live()
            ->rules([
                fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                    $recordId = $get('id') ?? null;
                    $equipoId = $get('equipo_id');

                    if (!$equipoId || !$value) {
                        return; // Evita fallos si aún no se seleccionó un valor
                    }

                    $exists = DB::table('tarjetons')
                        ->where('equipo_id', $equipoId)
                        ->whereDate('fecha', $value)
                        ->when($recordId, fn ($query) => $query->where('id', '!=', $recordId))
                        ->exists();

                    if ($exists) {
                        $fail("Esta fecha ya esta registrada para este equipo.");
                    }
                },
            ]),

        // Sección de Encendido
        Forms\Components\Section::make('Registro de Encendido')
            ->schema([
                Forms\Components\TimePicker::make('hora_encendido')
                    ->default(now()->format('H:i'))
                    ->seconds(false)
                    ->label('Hora de Encendido')
                    ->live()
                    ->afterStateUpdated(function (Forms\Set $set) {
                        $set('encendido_por', auth()->user()->name);
                    }),

                Forms\Components\TextInput::make('encendido_por')
                    ->default(fn() => auth()->user()->name)
                    ->label('Encendido por')
                    ->required()
                    ->maxLength(255),
            ])
            ->columns(2),

        // Sección de Apagado
        Forms\Components\Section::make('Registro de Apagado')
            ->schema([
                Forms\Components\TimePicker::make('hora_apagado')
                    ->seconds(false)
                    ->label('Hora de Apagado')
                    ->live()
                    ->afterStateUpdated(function (Forms\Set $set, $state, Forms\Get $get) {
                        if ($state) {
                            $set('apagado_por', auth()->user()->name);
                            $set('estado', 'apagado');

                            // Validar que la hora de apagado sea posterior a la de encendido
                            $horaEncendido = $get('hora_encendido');
                            if ($horaEncendido && $state <= $horaEncendido) {
                                \Filament\Notifications\Notification::make()
                                    ->warning()
                                    ->title('Atención')
                                    ->body('La hora de apagado debe ser posterior a la de encendido')
                                    ->send();
                            }
                        }
                    }),

                Forms\Components\TextInput::make('apagado_por')
                    ->label('Apagado por')
                    ->maxLength(255),
            ])
            ->columns(2)
            ->collapsed()
            ->collapsible(),

        // Sección adicional
        Forms\Components\Section::make('Información Adicional')
            ->schema([
                Forms\Components\Select::make('estado')
                    ->options([
                        'encendido' => 'Encendido',
                        'apagado' => 'Apagado',
                        'mantenimiento' => 'Mantenimiento',
                    ])
                    ->default('encendido')
                    ->required()
                    ->live(),

                Forms\Components\Textarea::make('observaciones')
                    ->label('Observaciones')
                    ->placeholder('Agregar notas sobre el funcionamiento del equipo...')
                    ->rows(3)
                    ->columnSpanFull(),

                // Campo calculado para mostrar tiempo total
                Forms\Components\Placeholder::make('tiempo_total')
                    ->label('Tiempo Total de Operación')
                    ->content(function (Forms\Get $get): string {
                        $horaEncendido = $get('hora_encendido');
                        $horaApagado = $get('hora_apagado');

                        if ($horaEncendido && $horaApagado) {
                            try {
                                $inicio = \Carbon\Carbon::createFromFormat('H:i', $horaEncendido);
                                $fin = \Carbon\Carbon::createFromFormat('H:i', $horaApagado);

                                if ($fin->greaterThan($inicio)) {
                                    $diff = $fin->diff($inicio);
                                    return $diff->format('%h horas y %i minutos');
                                } else {
                                    return 'Verificar horarios';
                                }
                            } catch (\Exception $e) {
                                return 'Formato de hora inválido';
                            }
                        }

                        return 'Registra ambas horas para calcular';
                    })
                    ->live(),
            ])
            ->columns(2)
            ->collapsed()
            ->collapsible(),
    ]);
    }


 public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('equipo.tag')
                ->label('Equipo')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('fecha')
                ->label('Fecha')
                ->date('d/M/Y')
                ->sortable(),

            Tables\Columns\TextColumn::make('hora_encendido')
                ->label('Encendido')
                ->badge()
                ->color('success')
                ->formatStateUsing(fn (string $state): string => $state ?: 'N/A'),

            Tables\Columns\TextColumn::make('hora_apagado')
                ->label('Apagado')
                ->badge()
                ->color('danger')
                ->formatStateUsing(fn (?string $state): string => $state ?: 'En operación')
                ->placeholder('En operación'),

            Tables\Columns\TextColumn::make('tiempo_operacion_formateado')
                ->label('Tiempo Total')
                ->badge()
                ->color('info')
                ->placeholder('Calculando...'),

            Tables\Columns\TextColumn::make('encendido_por')
                ->label('Op. Encendido')
                ->limit(12)
                ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                    $state = $column->getState();
                    return strlen($state) > 12 ? $state : null;
                })
                ->placeholder('N/A'),

            Tables\Columns\TextColumn::make('apagado_por')
                ->label('Op. Apagado')
                ->limit(12)
                ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                    $state = $column->getState();
                    return strlen($state) > 12 ? $state : null;
                })
                ->placeholder('Pendiente'),

            Tables\Columns\BadgeColumn::make('estado')
                ->label('Estado')
                ->colors([
                    'success' => 'encendido',
                    'danger' => 'apagado',
                    'warning' => 'mantenimiento',
                ])
                ->formatStateUsing(fn (string $state): string => ucfirst($state)),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('equipo_id')
                ->relationship('equipo', 'tag')
                ->label('Equipo')
                ->multiple()
                ->preload(),

            Tables\Filters\Filter::make('fecha')
                ->form([
                    Forms\Components\DatePicker::make('desde')
                        ->default(now()->subDays(7)),
                    Forms\Components\DatePicker::make('hasta')
                        ->default(now()),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['desde'],
                            fn (Builder $query, $date): Builder => $query->whereDate('fecha', '>=', $date),
                        )
                        ->when(
                            $data['hasta'],
                            fn (Builder $query, $date): Builder => $query->whereDate('fecha', '<=', $date),
                        );
                })
                ->indicateUsing(function (array $data): array {
                    $indicators = [];
                    if ($data['desde'] ?? null) {
                        $indicators['desde'] = 'Desde: ' . Carbon::parse($data['desde'])->format('d/M/Y');
                    }
                    if ($data['hasta'] ?? null) {
                        $indicators['hasta'] = 'Hasta: ' . Carbon::parse($data['hasta'])->format('d/M/Y');
                    }
                    return $indicators;
                }),

            Tables\Filters\SelectFilter::make('estado')
                ->options([
                    'encendido' => 'Encendido',
                    'apagado' => 'Apagado',
                    'mantenimiento' => 'Mantenimiento',
                ])
                ->multiple(),

            Tables\Filters\Filter::make('solo_hoy')
                ->label('Solo Hoy')
                ->query(fn (Builder $query): Builder => $query->whereDate('fecha', today()))
                ->toggle(),
        ])
        ->actions([
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),
            Tables\Actions\Action::make('toggle_estado')
                ->label(fn (Tarjeton $record) => $record->estado === 'encendido' ? 'Apagar' : 'Encender')
                ->icon(fn (Tarjeton $record) => $record->estado === 'encendido' ? 'heroicon-o-stop' : 'heroicon-o-play')
                ->color(fn (Tarjeton $record) => $record->estado === 'encendido' ? 'danger' : 'success')
                ->action(function (Tarjeton $record) {
                    if ($record->estado === 'encendido') {
                        $record->update([
                            'hora_apagado' => now()->format('H:i'),
                            'apagado_por' => auth()->user()->name,
                            'estado' => 'apagado'
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Equipo Apagado')
                            ->body("El equipo {$record->equipo->tag} ha sido apagado correctamente")
                            ->send();
                    } else {
                        $record->update([
                            'hora_encendido' => now()->format('H:i'),
                            'encendido_por' => auth()->user()->name,
                            'estado' => 'encendido'
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Equipo Encendido')
                            ->body("El equipo {$record->equipo->tag} ha sido encendido correctamente")
                            ->send();
                    }
                })
                ->requiresConfirmation()
                ->modalHeading(fn (Tarjeton $record) => $record->estado === 'encendido' ? 'Apagar Equipo' : 'Encender Equipo')
                ->modalDescription(fn (Tarjeton $record) => "¿Confirmas que quieres " . ($record->estado === 'encendido' ? 'apagar' : 'encender') . " el equipo {$record->equipo->tag}?"),

            Tables\Actions\Action::make('mantenimiento')
                ->label('Mantenimiento')
                ->icon('heroicon-o-wrench-screwdriver')
                ->color('warning')
                ->action(function (Tarjeton $record) {
                    $record->update([
                        'estado' => 'mantenimiento',
                        'apagado_por' => auth()->user()->name,
                        'hora_apagado' => now()->format('H:i'),
                    ]);
                })
                ->visible(fn (Tarjeton $record) => $record->estado !== 'mantenimiento'),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),

                Tables\Actions\BulkAction::make('cambiar_estado')
                    ->label('Cambiar Estado')
                    ->icon('heroicon-o-arrow-path')
                    ->form([
                        Forms\Components\Select::make('nuevo_estado')
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

                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Estados Actualizados')
                            ->body("Se actualizaron {$records->count()} registros")
                            ->send();
                    }),
            ]),
        ])
        ->defaultSort('fecha', 'desc')
        ->defaultSort('created_at', 'desc')
        ->poll('60s') // Auto-refresh cada minuto
        ->persistSortInSession()
        ->persistSearchInSession()
        ->persistFiltersInSession();
}

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageTarjetons::route('/'),
            'bitacora' => Pages\BitacoraReporte::route('/bitacora'),
        ];
    }
}
