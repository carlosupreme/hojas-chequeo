<?php

namespace App\Filament\Resources\HojaChequeoResource\Pages;

use App\Filament\Resources\HojaChequeoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHojaChequeos extends ListRecords
{
    protected static string $resource = HojaChequeoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
