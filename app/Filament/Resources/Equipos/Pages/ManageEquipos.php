<?php

namespace App\Filament\Resources\Equipos\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Equipos\EquipoResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageEquipos extends ManageRecords
{
    protected static string $resource = EquipoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->closeModalByClickingAway(false),
        ];
    }
}
