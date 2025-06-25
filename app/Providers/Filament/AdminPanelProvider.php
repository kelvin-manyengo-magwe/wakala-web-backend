<?php

namespace App\Providers\Filament;

use App\Filament\Pages\AdminRegistration;
use Filament\Http\Middleware\Authenticate as FilamentAuthenticateMiddleware;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\View\View;
use App\Filament\Pages\RegistrationSuccess;
use App\Filament\Pages\AdminSetupPage;
use App\Filament\Pages\MadukaMiamalaPage;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->registration(AdminRegistration::class)
            ->colors([
                'primary' => '#D7263D',
                'danger'  => Color::Red,
                'gray'    => Color::Slate,
            ])
            ->brandLogo(fn () => view('components.filament.brand.wakala-brand'))
            ->brandLogoHeight('4rem')

            ->pages([
                \Filament\Pages\Dashboard::class,
                RegistrationSuccess::class,
                AdminSetupPage::class,
                MadukaMiamalaPage::class,
            ])
            // The correct constant for the hook name
            ->renderHook(
                'panels::global-search.after',
                fn (): View => view('components.topbar-items')
            )

            // Other panel configurations...
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            //->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([

                \App\Filament\Widgets\AnalyticsDashboard::class,
            ])
            ->middleware([
                \Illuminate\Cookie\Middleware\EncryptCookies::class,
                \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
                \Illuminate\Session\Middleware\StartSession::class,
                \Illuminate\View\Middleware\ShareErrorsFromSession::class,
                \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
                \Illuminate\Routing\Middleware\SubstituteBindings::class,
                \Filament\Http\Middleware\DisableBladeIconComponents::class,
                \Filament\Http\Middleware\DispatchServingFilamentEvent::class,
                \App\Http\Middleware\SetLocaleMiddleware::class,
            ])
            ->authMiddleware([
                FilamentAuthenticateMiddleware::class,
            ]);
    }
}
