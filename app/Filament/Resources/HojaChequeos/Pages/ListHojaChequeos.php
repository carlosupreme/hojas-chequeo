<?php

namespace App\Filament\Resources\HojaChequeos\Pages;

use App\Filament\Resources\HojaChequeos\HojaChequeoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHojaChequeos extends ListRecords
{
    protected static string $resource = HojaChequeoResource::class;

    protected static ?string $title = 'Hojas de chequeo';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
