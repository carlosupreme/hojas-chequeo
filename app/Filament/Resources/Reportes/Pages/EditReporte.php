<?php

namespace App\Filament\Resources\Reportes\Pages;

use App\Filament\Resources\Reportes\ReporteResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditReporte extends EditRecord
{
    protected static string $resource = ReporteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
