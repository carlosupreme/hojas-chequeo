<?php

namespace App\Filament\Resources\TarjetonResource\Pages;

use App\Filament\Resources\TarjetonResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageTarjetons extends ManageRecords
{
    protected static string $resource = TarjetonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
