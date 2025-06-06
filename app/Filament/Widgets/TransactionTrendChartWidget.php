<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\AirtelTransaction;
use App\Models\HalotelTransaction;
use Carbon\Carbon;
use Carbon\CarbonPeriod; // To iterate over dates
use Illuminate\Support\Facades\Config;

class TransactionTrendChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Mwenendo wa Idadi ya Miamala (Siku 30 Zilizopita)';
    protected static ?string $pollingInterval = '60s';
    protected static bool $isLazy = true;
    // protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $appTimezone = Config::get('app.timezone', 'UTC');
        $endDate = Carbon::now($appTimezone)->startOfDay(); // Today, just the date part for iteration
        $startDate = $endDate->copy()->subDays(29);    // 29 days before today = 30 days total inclusive

        $labelsForChart = [];
        $airtelDailyTransactionCounts = [];
        $halotelDailyTransactionCounts = [];

        $period = CarbonPeriod::create($startDate, $endDate);

        // Custom Swahili Month Abbreviation Map (if translatedFormat remains problematic)
        $swahiliMonths = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mac', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
            7 => 'Jul', 8 => 'Ago', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        foreach ($period as $date) {
            $dateString = $date->toDateString(); // YYYY-MM-DD for queries

            // Generate Label: e.g., "04 Jun"
            $labelsForChart[] = $date->format('d') . ' ' . ($swahiliMonths[$date->month] ?? $date->format('M'));

            // Calculate Airtel transaction count for this specific day
            $airtelCountForDay = AirtelTransaction::whereDate('processed_at', $dateString)
                                                  ->count();
            $airtelDailyTransactionCounts[] = $airtelCountForDay;

            // Calculate Halotel transaction count for this specific day
            $halotelCountForDay = HalotelTransaction::whereDate('processed_at', $dateString)
                                                   ->count();
            $halotelDailyTransactionCounts[] = $halotelCountForDay;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Miamala ya Airtel',
                    'data' => $airtelDailyTransactionCounts,
                    'borderColor' => 'rgb(220, 38, 38)', // Red for Airtel
                    'backgroundColor' => 'rgba(220, 38, 38, 0.3)',
                    'tension' => 0.2,
                    'fill' => 'start',
                ],
                [
                    'label' => 'Miamala ya Halotel',
                    'data' => $halotelDailyTransactionCounts,
                    'borderColor' => '#ebcfc6', // Green for Halotel
                    'backgroundColor' => '#ebcfc6',
                    'tension' => 0.2,
                    'fill' => 'start',
                ],
            ],
            'labels' => $labelsForChart,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
