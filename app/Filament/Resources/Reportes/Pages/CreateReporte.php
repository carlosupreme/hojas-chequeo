<?php

namespace App\Filament\Resources\Reportes\Pages;

use App\Filament\Resources\Reportes\ReporteResource;
use Filament\Resources\Pages\CreateRecord;

class CreateReporte extends CreateRecord
{
    protected static string $resource = ReporteResource::class;


    /**
     * Título de la página
     */
    public function getTitle(): string
    {
        return 'Nuevo Reporte';
    }

    /**
     * Descripción o Subtítulo (Subheading)
     */
    public function getSubheading(): ?string
    {
        return 'Complete los campos a continuación para registrar un nuevo reporte en el sistema.';
    }
}
