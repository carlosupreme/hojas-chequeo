<?php

namespace App\Forms\Components;

use App\HojaChequeoArea;
use App\Models\HojaChequeo;
use Filament\Forms\Components\Field;
use Illuminate\Support\Collection;

class SelectHojas extends Field
{
    protected string $view = 'forms.components.select-hojas';

    protected function setUp(): void {
        parent::setUp();

        $this->default([]);

        $this->afterStateHydrated(function (SelectHojas $component, $state): void {
            $component->state(is_array($state) ? $state : []);
        });

        $this->dehydrateStateUsing(fn ($state) => is_array($state) ? $state : []);
    }

    public function getHojas(): Collection {
        return HojaChequeo::with('equipo')->get();
    }


}
