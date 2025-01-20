<?php

namespace App\Filament\Resources\HojaChequeoResource\Pages;

use App\Filament\Resources\HojaChequeoResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewHojaChequeo extends ViewRecord
{
    protected static string $resource = HojaChequeoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
