<?php

namespace App\Observers;

use App\Models\HojaChequeo;
use Illuminate\Support\Facades\Cache;

class HojaChequeoObserver
{
    /**
     * Handle the HojaChequeo "created" event.
     */
    public function created(HojaChequeo $hojaChequeo): void
    {
        $this->clearCache($hojaChequeo);
    }

    /**
     * Handle the HojaChequeo "updated" event.
     */
    public function updated(HojaChequeo $hojaChequeo): void
    {
        $this->clearCache($hojaChequeo);
    }

    /**
     * Handle the HojaChequeo "deleted" event.
     */
    public function deleted(HojaChequeo $hojaChequeo): void
    {
        $this->clearCache($hojaChequeo);
    }

    /**
     * Handle the HojaChequeo "restored" event.
     */
    public function restored(HojaChequeo $hojaChequeo): void
    {
        $this->clearCache($hojaChequeo);
    }

    /**
     * Handle the HojaChequeo "force deleted" event.
     */
    public function forceDeleted(HojaChequeo $hojaChequeo): void
    {
        $this->clearCache($hojaChequeo);
    }

    protected function clearCache(HojaChequeo $hojaChequeo): void
    {
        Cache::tags(['hojas', "hoja:{$hojaChequeo->id}"])->flush();
    }
}
