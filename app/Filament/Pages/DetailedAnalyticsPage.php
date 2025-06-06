<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

// Ensure all these 'use' statements are present and correct
use App\Filament\Widgets\SummaryStatsOverviewWidget;
use App\Filament\Widgets\TransactionTrendChartWidget;
use App\Filament\Widgets\ProfitCommissionChartWidget;
use App\Filament\Widgets\MnoSharePieChartWidget;
use App\Filament\Widgets\RecentTransactionsTableWidget;

class DetailedAnalyticsPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';
    protected static string $view = 'filament.pages.detailed-analytics-page';

    protected static ?int $navigationSort = 20;
    protected static ?string $title = 'Uchambuzi wa Kina wa Miamala';

    public static function getNavigationGroup(): ?string
    {
        return 'Ripoti na Takwimu';
    }

    public static function getNavigationLabel(): string
    {
        return 'Uchambuzi wa Kina';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SummaryStatsOverviewWidget::class,
            TransactionTrendChartWidget::class,
            ProfitCommissionChartWidget::class,
            MnoSharePieChartWidget::class,
          //  RecentTransactionsTableWidget::class,
        ];
    }

    protected function getWidgets(): array
    {
        return [
            /*ProfitCommissionChartWidget::class,
            MnoSharePieChartWidget::class,
            RecentTransactionsTableWidget::class,*/
        ];
    }

    public function getColumns(): int | string | array
    {
        return 2;
    }
}
