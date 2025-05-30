<?php

namespace App\Filament\Resources\EquipoResource\Pages;

use App\Filament\Resources\EquipoResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageEquipos extends ManageRecords
{
    protected static string $resource = EquipoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->closeModalByClickingAway(false),
        ];
    }
}
