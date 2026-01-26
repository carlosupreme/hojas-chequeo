<?php

namespace App\Filament\Resources\Recorridos\Pages;

use App\Filament\Pages\CreateRecorrido;
use App\Filament\Resources\Recorridos\RecorridoResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListRecorridos extends ListRecords
{
    protected static string $resource = RecorridoResource::class;

    protected static ?string $title = 'Recorridos';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create_recorrido')
                ->label('Empezar recorrido')
                ->url(CreateRecorrido::getUrl()),
        ];
    }
}
