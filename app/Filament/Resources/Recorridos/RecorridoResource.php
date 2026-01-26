<?php

namespace App\Filament\Resources\Recorridos;

use App\Filament\Pages\CreateRecorrido;
use App\Filament\Resources\Recorridos\Pages\ListRecorridos;
use App\Models\LogRecorrido;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class RecorridoResource extends Resource
{
    protected static ?string $model = LogRecorrido::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $navigationLabel = 'Recorridos';

    protected static ?string $modelLabel = 'Recorrido';

    public static function getNavigationGroup(): ?string
    {
        return 'Mantenimiento';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('fecha', 'desc')
            ->columns([
                TextColumn::make('fecha')->dateTime('D d/m/Y H:II')->label('Fecha'),
                TextColumn::make('formularioRecorrido.nombre')->label('Recorrido'),
                TextColumn::make('operador.name')
                    ->label('Operador')->weight(FontWeight::Bold)
                    ->description(fn (LogRecorrido $record) => $record->turno->nombre),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('edit_recorrido')
                        ->label('Editar')
                        ->icon('heroicon-m-pencil')
                        ->color('primary')
                        ->url(function (LogRecorrido $record) {
                            $b = RecorridoResource::getUrl('index');

                            return CreateRecorrido::getUrl()."?f=$record->formulario_recorrido_id&e=$record->id&b=$b";
                        }),
                    DeleteAction::make()->hidden(fn () => Auth::user()->isOperador()),
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
            'index' => ListRecorridos::route('/'),
        ];
    }
}
