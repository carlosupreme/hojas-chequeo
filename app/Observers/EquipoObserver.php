<?php

namespace App\Observers;

use App\Models\Equipo;
use Illuminate\Support\Facades\Cache;

class EquipoObserver
{
    /**
     * Handle the Equipo "created" event.
     */
    public function created(Equipo $equipo): void
    {
        $this->clearCache($equipo);
    }

    /**
     * Handle the Equipo "updated" event.
     */
    public function updated(Equipo $equipo): void
    {
        $this->clearCache($equipo);
    }

    /**
     * Handle the Equipo "deleted" event.
     */
    public function deleted(Equipo $equipo): void
    {
        $this->clearCache($equipo);
    }

    /**
     * Handle the Equipo "restored" event.
     */
    public function restored(Equipo $equipo): void
    {
        $this->clearCache($equipo);
    }

    /**
     * Handle the Equipo "force deleted" event.
     */
    public function forceDeleted(Equipo $equipo): void
    {
        $this->clearCache($equipo);
    }

    protected function clearCache(Equipo $equipo): void
    {
        Cache::tags(['hojas'])->flush();
    }
}
