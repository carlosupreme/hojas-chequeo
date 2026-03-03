<?php

namespace App\Filament\Resources\HojaChequeos\Pages;

use App\Filament\Resources\HojaChequeos\HojaChequeoResource;
use App\Models\HojaChequeo;
use Filament\Resources\Pages\Page;

class VersionsHojaChequeo extends Page
{
    protected static string $resource = HojaChequeoResource::class;

    protected string $view = 'filament.resources.hoja-chequeos.pages.versions-hoja-chequeo';

    public HojaChequeo $record;

    public function getTitle(): string
    {
        return 'Versiones de '.$this->record->equipo->tag;
    }

    public function getVersions(): \Illuminate\Database\Eloquent\Collection
    {
        return HojaChequeo::where('equipo_id', $this->record->equipo_id)
            ->withCount('chequeos')
            ->orderBy('version')
            ->get();
    }
}
