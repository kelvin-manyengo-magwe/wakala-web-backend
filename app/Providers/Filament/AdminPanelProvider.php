<?php

namespace App\Providers\Filament; // Or your specific panel provider namespace

use Filament\Http\Middleware\Authenticate; // Filament's default auth middleware
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Pages;
use App\Filament\Pages\AdminRegistration; // Your custom registration page class
use App\Filament\Pages\RegistrationSuccess;
use App\Filament\Widgets\AnalyticsDashboard;



class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin') // Your panel ID, used in route names e.g., filament.admin.auth.login
            ->path('admin') // URL prefix, so routes will be /admin/...
            ->login()       // Enables default login page at /admin/login
                            // Route name: filament.admin.auth.login
            ->registration(AdminRegistration::class)


            ->colors([
                'primary' => '#D7263D', // Your Brand Red for buttons
                'danger'  => Color::Red,
                'gray'    => Color::Slate,
            ])
            ->renderHook(
                'panels::global-search.after',
                 fn () => view('components.filament.language-switcher.language-switcher')
               )
            ->brandLogo(fn () => view('components.filament.brand.wakala-brand'))
            ->darkModeBrandLogo(fn () => view('components.filament.brand.wakala-brand', ['darkMode' => true]))
            ->brandLogoHeight('4rem')

            ->pages([ // Standard pages for the panel
                Pages\Dashboard::class,
                RegistrationSuccess::class, // So getUrl() works for this success page

            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
           ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')

             ->widgets([
              //  Widgets\AccountWidget::class,
                \App\Filament\Widgets\AnalyticsDashboard::class,
              //  \App\Filament\Widgets\SummaryStatsOverviewWidget::class,
              //  \App\Filament\Widgets\TransactionTrendChartWidget::class,
              //  \App\Filament\Widgets\ProfitCommissionChartWidget::class,
              //  \App\Filament\Widgets\MnoSharePieChartWidget::class,
              //  \App\Filament\Widgets\RecentTransactionsTableWidget::class,
            ])

            ->middleware([ // Middleware applied to ALL requests to this panel's routes (even auth pages initially)
                \Illuminate\Cookie\Middleware\EncryptCookies::class,
                \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
                \Illuminate\Session\Middleware\StartSession::class,
                \Illuminate\View\Middleware\ShareErrorsFromSession::class,
                \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
                \Illuminate\Routing\Middleware\SubstituteBindings::class,
                \Filament\Http\Middleware\DisableBladeIconComponents::class,
                \Filament\Http\Middleware\DispatchServingFilamentEvent::class,
                \App\Http\Middleware\SetLocaleMiddleware::class, // Your locale middleware
            ])
            ->authMiddleware([ // Applied ONLY to routes that require authentication WITHIN the panel
                Authenticate::class, // Filament's own authentication middleware
            ]);
    }
}
