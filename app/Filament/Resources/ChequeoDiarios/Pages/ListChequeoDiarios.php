<?php

namespace App\Filament\Resources\ChequeoDiarios\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\ChequeoDiarios\ChequeoDiarioResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListChequeoDiarios extends ListRecords
{
    protected static string $resource = ChequeoDiarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
