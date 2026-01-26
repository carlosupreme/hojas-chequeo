<?php

namespace App\Filament\Resources\EntregaTurnos\Pages;

use App\Filament\Resources\EntregaTurnos\EntregaTurnoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageEntregaTurnos extends ManageRecords
{
    protected static string $resource = EntregaTurnoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
