<?php

namespace App\Providers\Filament;

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
use Filament\SpatieLaravelTranslatablePlugin;
use App\Filament\Widgets\AnalyticsDashboard;
use App\Filament\Widgets\SummaryStatsOverviewWidget;
use App\Filament\Widgets\TransactionTrendChartWidget;
use App\Filament\Widgets\ProfitCommissionChartWidget;
use App\Filament\Widgets\RecentTransactionsTableWidget;
use Illuminate\Support\HtmlString;



class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $logoPath = asset('images/logo/wakala-logo.jpg');

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandLogo(fn () => view('components.filament.brand.wakala-brand'))
            ->darkModeBrandLogo(fn () => view('components.filament.brand.wakala-brand', ['darkMode' => true]))
            ->brandLogoHeight('4rem')
            ->colors([
                'primary' => Color::Red,
                'danger' => Color::Red,
            ])

            //->viteTheme('resources/css/filament.css')
            ->renderHook(
           'panels::global-search.after',
                  fn () => view('components.filament.language-switcher.language-switcher')
                )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,

            ])
            //->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                \App\Filament\Widgets\AnalyticsDashboard::class,
                \App\Filament\Widgets\SummaryStatsOverviewWidget::class,
                \App\Filament\Widgets\TransactionTrendChartWidget::class,
                \App\Filament\Widgets\ProfitCommissionChartWidget::class,
                \App\Filament\Widgets\MnoSharePieChartWidget::class,
                \App\Filament\Widgets\RecentTransactionsTableWidget::class,
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
                \App\Http\Middleware\SetLocaleMiddleware::class, //registering its Middleware
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
