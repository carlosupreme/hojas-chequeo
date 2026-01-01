<?php

namespace App\Filament\Resources\HojaChequeos\Schemas;

use App\Models\Equipo;
use App\Models\HojaChequeo;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class HojaChequeoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('equipo_id')
                    ->label('Equipo')
                    ->options(Equipo::query()->pluck('tag', 'id'))
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->live()
                    ->partiallyRenderComponentsAfterStateUpdated(['version'])
                    ->afterStateUpdated(function (Get $get, Set $set, $operation): int {
                        if ($operation === 'edit') {
                            return $get('version');
                        }

                        $equipoId = $get('equipo_id');

                        if (! $equipoId) {
                            return 1;
                        }

                        return $set('version', HojaChequeo::getCurrentVersion($equipoId));
                    })
                    ->required(),
                TextInput::make('version')
                    ->readOnly()
                    ->live()
                    ->default(1)
                    ->helperText('Esta version se calcula automaticamente'),
                RichEditor::make('observaciones')->disableToolbarButtons(['codeBlock', 'attachFiles'])->maxLength(255),
            ]);
    }
}
