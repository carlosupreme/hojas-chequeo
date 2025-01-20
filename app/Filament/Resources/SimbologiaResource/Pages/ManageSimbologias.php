<?php

namespace App\Filament\Resources\SimbologiaResource\Pages;

use App\Filament\Resources\SimbologiaResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageSimbologias extends ManageRecords
{
    protected static string $resource = SimbologiaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->closeModalByClickingAway(false),
        ];
    }
}
