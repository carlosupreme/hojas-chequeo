<?php

namespace App\Filament\Resources\EntregaTurnos;

use App\Filament\Resources\EntregaTurnos\Pages\ManageEntregaTurnos;
use App\Models\EntregaTurno;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class EntregaTurnoResource extends Resource
{
    protected static ?string $model = EntregaTurno::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocument;

    protected static ?string $navigationLabel = 'Entrega de Turnos';

    protected static ?string $pluralModelLabel = 'Entregas de Turno';

    public static function getNavigationGroup(): ?string
    {
        return 'Mantenimiento';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ])
                    ->columnSpanFull()
                    ->components([
                        DateTimePicker::make('fecha')
                            ->label('Fecha y hora')
                            ->displayFormat('D d/m/Y H:i')
                            ->native(false)
                            ->seconds(false)
                            ->locale('es')
                            ->default(fn () => now())
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                debug($get('fecha'));
                                $hora = Carbon::createFromFormat('Y-m-d H:i', $get('fecha'))
                                    ->format('H:i');
                                $set('hora', $hora);
                            })
                            ->columnSpanFull()
                            ->maxDate(now()),

                        Hidden::make('hora')
                            ->default(fn () => now()->format('H:i:s')),

                        // ENTREGA
                        Section::make('ENTREGA')
                            ->schema([
                                Textarea::make('entrega_equipos')
                                    ->label('Equipos funcionando')
                                    ->rows(2),

                                Textarea::make('entrega_observaciones_equipos')
                                    ->label('Observaciones')
                                    ->rows(2),

                                Textarea::make('entrega_servicios')
                                    ->label('Servicios funcionando')
                                    ->rows(2),

                                Textarea::make('entrega_observaciones_servicios')
                                    ->label('Observaciones')
                                    ->rows(2),
                            ])
                            ->columnSpan(1),

                        // RECEPCIÓN
                        Section::make('RECEPCIÓN')
                            ->schema([
                                Textarea::make('recepcion_equipos')
                                    ->label('Equipos funcionando')
                                    ->rows(2),

                                Textarea::make('recepcion_observaciones_equipos')
                                    ->label('Observaciones')
                                    ->rows(2),

                                Textarea::make('recepcion_servicios')
                                    ->label('Servicios funcionando')
                                    ->rows(2),

                                Textarea::make('recepcion_observaciones_servicios')
                                    ->label('Observaciones')
                                    ->rows(2),
                            ])
                            ->columnSpan(1),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fecha')
                    ->label('Fecha')
                    ->date()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('hora')
                    ->label('Hora')
                    ->time('H:i')
                    ->sortable(),

                TextColumn::make('entrega_equipos')
                    ->label('Entrega Equipos')
                    ->limit(40)
                    ->toggleable()
                    ->wrap(),

                TextColumn::make('entrega_servicios')
                    ->label('Entrega Servicios')
                    ->limit(40)
                    ->toggleable()
                    ->wrap(),

                TextColumn::make('recepcion_equipos')
                    ->label('Recepción Equipos')
                    ->limit(40)
                    ->wrap(),

                TextColumn::make('recepcion_servicios')
                    ->label('Recepción Servicios')
                    ->limit(40)
                    ->wrap(),
            ])
            ->filters([
                Filter::make('fecha')
                    ->schema([
                        DatePicker::make('desde'),
                        DatePicker::make('hasta'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['desde'], fn ($q) => $q->whereDate('fecha', '>=', $data['desde']))
                            ->when($data['hasta'], fn ($q) => $q->whereDate('fecha', '<=', $data['hasta']));
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()->mutateRecordDataUsing(function (array $data) {
                        return [
                            ...$data,
                            'fecha' => $data['fecha'].' '.$data['hora'],
                        ];
                    }),
                    DeleteAction::make()->hidden(fn () => Auth::user()->isOperador()),
                ]),
            ])
            ->defaultSort('fecha', 'desc')
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ])->hidden(fn () => Auth::user()->isOperador()),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageEntregaTurnos::route('/'),
        ];
    }
}
