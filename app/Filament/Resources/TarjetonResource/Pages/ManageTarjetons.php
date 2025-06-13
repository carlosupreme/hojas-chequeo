<?php

namespace App\Filament\Resources\TarjetonResource\Pages;

use App\Filament\Resources\TarjetonResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use App\Models\Tarjeton;

class ManageTarjetons extends ManageRecords
{
    protected static string $resource = TarjetonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('generar_bitacora')
                ->label('Generar Bitácora')
                ->icon('heroicon-o-document-text')
                ->color('info')
                ->url(fn (): string => static::$resource::getUrl('bitacora')),
        ];
    }
}
