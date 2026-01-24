<?php

namespace App\Providers;

use App\Services\ImageService;
use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ImageService::class, function () {
            return new ImageService;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // not working: shows in english
        Carbon::setLocale('es_MX');
    }
}
