<?php

namespace App\Observers;

use Illuminate\Support\Facades\Cache;

class HojaChequeoObserver
{
    /**
     * Handle the HojaChequeo "creating" and "updating" event.
     * Validates BEFORE saving to database.
     */
    public function saved(): void
    {
        $this->invalidateCache();
        // TODO: Fetch the perfiles that has the same Hoja chequeo->equipo in the hoja_ids propery, and if the
        // saved is a NEW version of an existing one, add them to their array

        // Given a new version of a HojaChequeo
        // When the perfil which has access to the previous version
        // Then the perfil has access to the new version too, automatically
    }

    public function invalidateCache(): void
    {
        Cache::tags(['hojas'])->flush();
    }
}
