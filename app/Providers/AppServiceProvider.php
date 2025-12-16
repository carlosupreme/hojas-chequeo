<?php

namespace App\Providers;

use App\Models\ChequeoDiario;
use App\Models\Equipo;
use App\Models\HojaChequeo;
use App\Observers\ChequeoDiarioObserver;
use App\Observers\EquipoObserver;
use App\Observers\HojaChequeoObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        HojaChequeo::observe(HojaChequeoObserver::class);
        ChequeoDiario::observe(ChequeoDiarioObserver::class);
        Equipo::observe(EquipoObserver::class);
    }
}
