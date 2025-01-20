<?php

namespace App\Filament\Resources\HojaChequeoResource\Pages;

use App\Filament\Resources\HojaChequeoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHojaChequeo extends EditRecord
{
    protected static string $resource = HojaChequeoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
