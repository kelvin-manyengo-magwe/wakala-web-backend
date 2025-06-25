<?php

namespace App\Providers;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Filament\Widgets\SummaryStatsOverviewWidget;
use App\Filament\Widgets\TransactionTrendChartWidget;
use App\Filament\Widgets\ProfitCommissionChartWidget;
use App\Filament\Widgets\MnoSharePieChartWidget;
use App\Filament\Widgets\RecentTransactionsTableWidget;
use Livewire\Livewire;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth; // <-- Add this
use Illuminate\Support\Facades\View;




use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        $host = request()->getHost();

        $appUrl = env('APP_URL');


        View::composer('components.topbar-items', function ($view) {
           $view->with('unreadNotificationsCount', Auth::user()?->unreadNotifications()->count() ?? 0);
       });

        //prevents the mass assignment issue
      Permission::unguard();
      Role::unguard();
        Livewire::component('my-summary-stats', SummaryStatsOverviewWidget::class);
        Livewire::component('wakala-transaction-trend-chart', TransactionTrendChartWidget::class);
        Livewire::component('wakala-profit-commission-chart', ProfitCommissionChartWidget::class);
        Livewire::component('wakala-mno-share-pie-chart', MnoSharePieChartWidget::class);
        Livewire::component('wakala-recent-transactions-table', RecentTransactionsTableWidget::class);




        /*if (request()->isSecure() || str_contains($host, parse_url($appUrl, PHP_URL_HOST)) ) {
            URL::forceScheme('https');
        }*/

    }
}
