<?php

namespace App\Filament\Resources\HojaChequeoResource\Pages;

use App\Filament\Resources\HojaChequeoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateHojaChequeo extends CreateRecord
{
    protected static string $resource = HojaChequeoResource::class;


    protected function getRedirectUrl(): string {
        return HojaChequeoResource::getUrl('index');
    }
}
