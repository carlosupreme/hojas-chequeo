<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Pages\SystemUpdate;
use Filament\Actions\Action;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
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
            ->databaseNotifications()
            ->databaseNotificationsPolling(null)
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => Blade::render("@vite('resources/js/app.js')")
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): View => view('filament.hooks.admin-charts')
            )
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->font('Inter Variable', provider: LocalFontProvider::class, url: '/css/inter.css')
            ->passwordReset()
            ->globalSearch(false)
            ->profile(isSimple: false)
            ->favicon(asset('/logo.png'))
            ->brandLogo(asset('logo.png'))
            ->darkModeBrandLogo(asset('dark.png'))
            ->brandName('Tacuba')
            ->brandLogoHeight('35px')
            ->sidebarCollapsibleOnDesktop()
            ->topbar(false)
            ->unsavedChangesAlerts()
            ->sidebarWidth('300px')
            ->renderHook(PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
                fn (): View => view('operador-login-button'))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([])
            ->userMenuItems([
                Action::make('update')
                    ->label('Actualizar Sistema')
                    ->url(fn (): string => SystemUpdate::getUrl())
                    ->icon('heroicon-m-cog-8-tooth'),
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
