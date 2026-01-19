<?php

namespace App\Providers\Filament;

use App\Filament\Pages\CreateChequeo;
use App\Filament\Resources\Chequeos\ChequeosResource;
use Filament\Enums\GlobalSearchPosition;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class OperadorPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('operador')
            ->path('operador')
            ->login(fn () => redirect()->route('login.selection'))
            ->colors([
                'primary' => Color::Blue,
            ])
            ->viteTheme('resources/css/filament/operador/theme.css')
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => Blade::render("@vite('resources/js/app.js')")
            )
            ->favicon(asset('/logo.png'))
            ->brandLogo(asset('logo.png'))
            ->darkModeBrandLogo(asset('dark.png'))
            ->brandName('Tacuba')
            ->brandLogoHeight('35px')
            ->sidebarCollapsibleOnDesktop()
            ->globalSearch()
            ->topbar(false)
            ->unsavedChangesAlerts()
            ->globalSearch(position: GlobalSearchPosition::Sidebar)
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->sidebarWidth('300px')
            ->resources([
                ChequeosResource::class,
            ])
            ->discoverResources(in: app_path('Filament/Operador/Resources'), for: 'App\Filament\Operador\Resources')
            ->discoverPages(in: app_path('Filament/Operador/Pages'), for: 'App\Filament\Operador\Pages')
            ->pages([
                Dashboard::class,
                CreateChequeo::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Operador/Widgets'), for: 'App\Filament\Operador\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
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
            ]);
    }
}
