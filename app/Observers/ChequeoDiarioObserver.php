<?php

namespace App\Observers;

use App\Models\ChequeoDiario;
use Illuminate\Support\Facades\Cache;

class ChequeoDiarioObserver
{
    /**
     * Handle the ChequeoDiario "created" event.
     */
    public function created(ChequeoDiario $chequeoDiario): void
    {
        $this->clearCache($chequeoDiario);
    }

    /**
     * Handle the ChequeoDiario "updated" event.
     */
    public function updated(ChequeoDiario $chequeoDiario): void
    {
        $this->clearCache($chequeoDiario);
    }

    /**
     * Handle the ChequeoDiario "deleted" event.
     */
    public function deleted(ChequeoDiario $chequeoDiario): void
    {
        $this->clearCache($chequeoDiario);
    }

    /**
     * Handle the ChequeoDiario "restored" event.
     */
    public function restored(ChequeoDiario $chequeoDiario): void
    {
        $this->clearCache($chequeoDiario);
    }

    /**
     * Handle the ChequeoDiario "force deleted" event.
     */
    public function forceDeleted(ChequeoDiario $chequeoDiario): void
    {
        $this->clearCache($chequeoDiario);
    }

    protected function clearCache(ChequeoDiario $chequeoDiario): void
    {
        Cache::tags(['hojas'])->flush();
    }
}
