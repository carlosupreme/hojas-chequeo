<?php

namespace App\Filament\Forms\Components;

use App\Models\HojaChequeo;
use Filament\Forms\Components\Field;
use Illuminate\Support\Collection;

class SelectHojas extends Field
{
    protected string $view = 'filament.forms.components.select-hojas';

    protected function setUp(): void
    {
        parent::setUp();

        $this->default([]);

        $this->afterStateHydrated(function (SelectHojas $component, $state): void {
            $component->state(is_array($state) ? $state : []);
        });

        $this->dehydrateStateUsing(fn ($state) => is_array($state) ? $state : []);
    }

    /**
     * Get only active (encendido) hojas, grouped by area.
     */
    public function getHojas(): Collection
    {
        return HojaChequeo::with('equipo')
            ->where('encendido', true)
            ->get()
            ->sortBy(fn (HojaChequeo $h) => $h->equipo?->nombre)
            ->groupBy(fn (HojaChequeo $h) => $h->equipo?->area
                ? ucwords(mb_strtolower($h->equipo->area))
                : 'Sin Área');
    }

    /**
     * Get the total count of active hojas.
     */
    public function getHojasCount(): int
    {
        return HojaChequeo::where('encendido', true)->count();
    }
}
