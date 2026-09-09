<?php

namespace App\Filament\Resources\HojaChequeos\Pages;

use App\Filament\Resources\HojaChequeos\HojaChequeoResource;
use App\Models\HojaChequeo;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Database\Eloquent\Collection;

class VersionsHojaChequeo extends Page
{
    use InteractsWithRecord;

    protected static string $resource = HojaChequeoResource::class;

    protected string $view = 'filament.resources.hoja-chequeos.pages.versions-hoja-chequeo';

    public static function canAccess(array $parameters = []): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function mount(int|string $record): void
    {
        $this->record = HojaChequeo::findOrFail($record);
    }

    public function getTitle(): string
    {
        return 'Versiones de '.$this->record->equipo->tag;
    }

    public function getVersions(): Collection
    {
        return HojaChequeo::where('equipo_id', $this->record->equipo_id)
            ->withCount('chequeos')
            ->orderBy('version')
            ->get();
    }
}
