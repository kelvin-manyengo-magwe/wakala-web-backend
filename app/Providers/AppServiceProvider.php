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
        //prevents the mass assignment issue
      Permission::unguard();
      Role::unguard();
        Livewire::component('my-summary-stats', SummaryStatsOverviewWidget::class);
        Livewire::component('wakala-transaction-trend-chart', TransactionTrendChartWidget::class);
        Livewire::component('wakala-profit-commission-chart', ProfitCommissionChartWidget::class);
        Livewire::component('wakala-mno-share-pie-chart', MnoSharePieChartWidget::class);
        Livewire::component('wakala-recent-transactions-table', RecentTransactionsTableWidget::class);

    }
}
