<?php

namespace App\Filament\Resources\HojaChequeos\Tables;

use App\Filament\Resources\HojaChequeos\HojaChequeoResource;
use App\Models\Equipo;
use App\Models\HojaChequeo;
use App\Models\HojaFilaValor;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;

class HojaChequeosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('equipo.tag')
                    ->label('Tag Equipo')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->copyable()
                    ->copyMessage('Tag copiado')
                    ->icon('heroicon-o-tag'),

                TextColumn::make('equipo.nombre')
                    ->label('Equipo')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->equipo->area ?? null)
                    ->limit(30),

                TextColumn::make('version')
                    ->label('Versión')
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                ToggleColumn::make('encendido')
                    ->label('Publicada')
                    ->disabled(fn () => ! auth()->user()?->isAdmin())
                    ->beforeStateUpdated(fn ($record) => HojaChequeo::where('equipo_id', $record->equipo_id)
                        ->update(['encendido' => false])),

                TextColumn::make('chequeos_count')
                    ->label('Ejecuciones')
                    ->counts('chequeos')
                    ->badge()
                    ->color('warning')
                    ->icon('heroicon-o-clipboard-document-check'),

                TextColumn::make('observaciones')
                    ->label('Observaciones')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->observaciones)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('equipo_id')
                    ->label('Equipo')
                    ->relationship('equipo', 'tag')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('area')
                    ->label('Área')
                    ->placeholder('Todas')
                    ->options(fn () => Equipo::distinct()->orderBy('area')->pluck('area')
                        ->filter()
                        ->map(fn (string $a) => ucwords(mb_strtolower($a)))
                        ->unique()
                        ->sort()
                        ->values()
                        ->mapWithKeys(fn (string $a) => [$a => $a])
                        ->toArray()
                    )
                    ->query(fn (Builder $query, array $data) => $query->when(
                        $data['value'],
                        fn ($q) => $q->whereHas('equipo', fn ($eq) => $eq->whereRaw('LOWER(area) = ?', [strtolower($data['value'])]))
                    )),
            ])
            ->recordActions([
                Action::make('Copiar')
                    ->visible(fn () => auth()->user()?->isAdmin())
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->modalHeading('Copiar hoja de chequeo')
                    ->modalSubmitActionLabel('Copiar')
                    ->form([
                        Select::make('equipo_id')
                            ->label('Equipo')
                            ->options(fn () => Equipo::query()->pluck('tag', 'id'))
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->required()
                            ->createOptionModalHeading('Crear nuevo equipo')
                            ->createOptionForm([
                                TextInput::make('nombre')
                                    ->label('Nombre')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('tag')
                                    ->label('Tag')
                                    ->required()
                                    ->rules([Rule::unique('equipos', 'tag')])
                                    ->maxLength(255),
                                TextInput::make('area')
                                    ->label('Área')
                                    ->required()
                                    ->datalist(fn () => Equipo::getAreas())
                                    ->maxLength(255),
                            ])
                            ->createOptionUsing(fn (array $data): int => Equipo::create($data)->getKey()),
                        RichEditor::make('observaciones')
                            ->disableToolbarButtons(['codeBlock', 'attachFiles']),
                    ])
                    ->fillForm(fn (HojaChequeo $record): array => [
                        'equipo_id' => $record->equipo_id,
                        'observaciones' => $record->observaciones,
                    ])
                    ->action(function (HojaChequeo $record, array $data): void {
                        $newHoja = HojaChequeo::create([
                            'equipo_id' => $data['equipo_id'],
                            'observaciones' => $data['observaciones'] === '<p></p>' ? null : ($data['observaciones'] ?? null),
                            'version' => HojaChequeo::getCurrentVersion((int) $data['equipo_id']),
                            'encendido' => false,
                        ]);

                        // old_columna_id => new_columna_id
                        $columnaMap = [];
                        foreach ($record->columnas as $columna) {
                            $newColumna = $newHoja->columnas()->create([
                                'key' => $columna->key,
                                'label' => $columna->label,
                                'is_fixed' => $columna->is_fixed,
                                'order' => $columna->order,
                            ]);
                            $columnaMap[$columna->id] = $newColumna->id;
                        }

                        // old_fila_id => new_fila_id
                        $filaMap = [];
                        foreach ($record->filas as $fila) {
                            $newFila = $newHoja->filas()->create([
                                'answer_type_id' => $fila->answer_type_id,
                                'order' => $fila->order,
                                'categoria' => $fila->categoria,
                            ]);
                            $filaMap[$fila->id] = $newFila->id;
                        }

                        HojaFilaValor::whereIn('hoja_fila_id', array_keys($filaMap))
                            ->get()
                            ->each(function (HojaFilaValor $valor) use ($filaMap, $columnaMap): void {
                                HojaFilaValor::create([
                                    'hoja_fila_id' => $filaMap[$valor->hoja_fila_id],
                                    'hoja_columna_id' => $columnaMap[$valor->hoja_columna_id] ?? $valor->hoja_columna_id,
                                    'valor' => $valor->valor,
                                ]);
                            });

                        Notification::make()
                            ->success()
                            ->title('Hoja de chequeo copiada')
                            ->send();
                    }),
                Action::make('Versiones')
                    ->url(fn (HojaChequeo $record): string => HojaChequeoResource::getUrl('versions', ['record' => $record]))
                    ->icon('heroicon-o-document-duplicate')
                    ->visible(fn (HojaChequeo $record): bool => auth()->user()?->isAdmin() && HojaChequeo::where('equipo_id', $record->equipo_id)->count() > 1),
                Action::make('Historial')
                    ->url(fn (HojaChequeo $record): string => HojaChequeoResource::getUrl('history', ['record' => $record]))
                    ->icon('heroicon-o-calendar'),
                ViewAction::make()->modalWidth(Width::SevenExtraLarge),
                EditAction::make()
                    ->visible(fn () => auth()->user()?->isAdmin()),
                DeleteAction::make()
                    ->visible(fn () => auth()->user()?->isAdmin()),
            ])
            ->persistSortInSession()
            ->persistFiltersInSession()
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ])->visible(fn () => auth()->user()?->isAdmin()),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
