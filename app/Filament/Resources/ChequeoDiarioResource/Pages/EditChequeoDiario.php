<?php

namespace App\Filament\Resources\ChequeoDiarioResource\Pages;

use App\Filament\Resources\ChequeoDiarioResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditChequeoDiario extends EditRecord
{
    protected static string $resource = ChequeoDiarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
