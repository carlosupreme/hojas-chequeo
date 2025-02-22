<?php

namespace App\Filament\Resources\ChequeoDiarioResource\Pages;

use App\Filament\Resources\ChequeoDiarioResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListChequeoDiarios extends ListRecords
{
    protected static string $resource = ChequeoDiarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
