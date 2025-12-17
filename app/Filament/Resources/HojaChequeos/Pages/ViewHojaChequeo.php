<?php

namespace App\Filament\Resources\HojaChequeos\Pages;

use Filament\Actions\EditAction;
use App\Filament\Resources\HojaChequeos\HojaChequeoResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewHojaChequeo extends ViewRecord
{
    protected static string $resource = HojaChequeoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
