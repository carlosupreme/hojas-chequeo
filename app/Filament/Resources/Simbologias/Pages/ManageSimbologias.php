<?php

namespace App\Filament\Resources\Simbologias\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Simbologias\SimbologiaResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageSimbologias extends ManageRecords
{
    protected static string $resource = SimbologiaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->closeModalByClickingAway(false),
        ];
    }
}
