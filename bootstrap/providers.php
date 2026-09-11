<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\OperadorPanelProvider;
use App\Providers\Filament\SupervisorPanelProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    OperadorPanelProvider::class,
    SupervisorPanelProvider::class,
];
