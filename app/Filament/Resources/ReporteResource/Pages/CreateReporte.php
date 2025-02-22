<?php

namespace App\Filament\Resources\ReporteResource\Pages;

use App\Filament\Resources\ReporteResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateReporte extends CreateRecord
{
    protected static string $resource = ReporteResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array {
        return [...$data, 'user_id' => auth()->user()->id];
    }
}
