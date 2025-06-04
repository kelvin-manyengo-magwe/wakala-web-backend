<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

// Import your actual widget class names (use English for class names)
use App\Filament\Widgets\SummaryStatsOverviewWidget;
use App\Filament\Widgets\TransactionTrendChartWidget;
use App\Filament\Widgets\ProfitCommissionChartWidget;
use App\Filament\Widgets\MnoSharePieChartWidget;
use App\Filament\Widgets\RecentTransactionsTableWidget;


class DetailedAnalyticsPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';
    protected static string $view = 'filament.pages.detailed-analytics-page'; // Blade file matches this

    protected static ?int $navigationSort = 20;
    protected static ?string $title = 'Uchambuzi wa Kina wa Miamala'; // Swahili for "Detailed Transaction Analytics"

    public static function getNavigationGroup(): ?string
    {
        return 'Ripoti na Takwimu'; // Swahili for "Reports & Analytics"
    }

    public static function getNavigationLabel(): string
    {
        return 'Uchambuzi wa Kina'; // Swahili for "Detailed Analytics"
    }

    /**
     * Widgets at the top of the page.
     */
    protected function getHeaderWidgets(): array
    {
        return [
            SummaryStatsOverviewWidget::class,      // Section 1
          //  TransactionTrendChartWidget::class,     // Section 2
        ];
    }

    /**
     * Widgets in the main content area.
     */
    protected function getWidgets(): array // Or getContentWidgets() in some Filament versions
    {
        return [
          //  ProfitCommissionChartWidget::class,    // Section 3
          //  MnoSharePieChartWidget::class,         // Section 4
          //  RecentTransactionsTableWidget::class,  // Section 5
        ];
    }

    /**
     * Number of columns for the main widget area.
     */
    public function getColumns(): int | string | array
    {
        // This will lay out widgets from getWidgets() in two columns.
        // Header widgets usually span full or are managed by their own columnSpan property.
        return 2;
        // Example for responsive: ['md' => 2, 'lg' => 3]
    }
}
