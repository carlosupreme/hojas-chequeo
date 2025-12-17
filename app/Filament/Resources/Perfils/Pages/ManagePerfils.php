<?php

namespace App\Filament\Resources\Perfils\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Perfils\PerfilResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePerfils extends ManageRecords
{
    protected static string $resource = PerfilResource::class;

    protected function getHeaderActions(): array {
        return [
            CreateAction::make(),
        ];
    }
}
