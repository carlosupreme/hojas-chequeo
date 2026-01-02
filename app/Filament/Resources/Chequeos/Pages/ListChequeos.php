<?php

namespace App\Filament\Resources\Chequeos\Pages;

use App\Filament\Resources\Chequeos\ChequeosResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListChequeos extends ListRecords
{
    protected static string $resource = ChequeosResource::class;

    protected static ?string $title = 'Chequeos';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
