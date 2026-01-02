<?php

namespace App\Filament\Resources\Chequeos\Pages;

use App\Filament\Resources\Chequeos\ChequeosResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditChequeos extends EditRecord
{
    protected static string $resource = ChequeosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
