<?php

namespace App\Providers\Filament;

use App\Filament\Pages\ChequeoDaily;
use App\Filament\Pages\ChequeoHistorico;
use App\Filament\Pages\Reportar;
use App\Filament\Pages\ReporteHistorico;
use App\Filament\Resources\ChequeoDiarioResource;
use App\Filament\Resources\TarjetonResource;
use App\Http\Middleware\EnsureIsOperador;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class OperadorPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel {
        return $panel
            ->id('operador')
            ->viteTheme('resources/css/filament/operador/theme.css')
            ->path('operador')
            ->login()
            ->favicon(asset(path: '/lg.png'))
            ->brandLogo(asset('lg.png'))
            ->darkModeBrandLogo(asset('dark.png'))
            ->brandName('Tacuba')
            ->brandLogoHeight('50px')
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('300px')
            ->colors([
                'primary' => Color::Blue,
            ])
            ->resources([ChequeoDiarioResource::class, TarjetonResource::class])
            ->pages([ChequeoDaily::class, Reportar::class, ReporteHistorico::class])
            ->widgets([Widgets\AccountWidget::class])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                EnsureIsOperador::class
            ]);
    }
}
