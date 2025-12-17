<?php

namespace App\Filament\Resources\HojaChequeos\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\HojaChequeos\HojaChequeoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHojaChequeos extends ListRecords
{
    protected static string $resource = HojaChequeoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
