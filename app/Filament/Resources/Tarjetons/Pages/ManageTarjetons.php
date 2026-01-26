<?php

namespace App\Filament\Resources\Tarjetons\Pages;

use App\Filament\Resources\Tarjetons\TarjetonResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageTarjetons extends ManageRecords
{
    protected static string $resource = TarjetonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('generar_bitacora')
                ->label('Generar Bitácora')
                ->icon('heroicon-o-document-text')
                ->color('info')
                ->url(fn (): string => static::$resource::getUrl('bitacora')),
        ];
    }
}
