<?php

namespace App\Providers\Filament;

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
use Illuminate\View\View;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Blue,
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => Blade::render("@vite('resources/js/app.js')")
            )
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->passwordReset()
            ->profile(isSimple: false)
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
            ->renderHook(PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
                fn (): View => view('operador-login-button'))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
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
