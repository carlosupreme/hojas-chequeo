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
        // Set Carbon locale to match Laravel's locale
        Carbon::setLocale(config('app.locale'));

        // Set specific Spanish localization for better formatting
        if (config('app.locale') === 'es') {
            setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'Spanish_Spain.1252');
        }
    }
}
