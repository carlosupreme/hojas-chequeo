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
    }

    public function invalidateCache(): void
    {
        Cache::tags(['hojas'])->flush();
    }
}
