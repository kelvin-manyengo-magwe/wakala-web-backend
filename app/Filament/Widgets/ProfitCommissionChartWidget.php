<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\AirtelTransaction;
use App\Models\HalotelTransaction;
use Carbon\Carbon;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Facades\Config;

class ProfitCommissionChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Mwenendo wa Kamisheni Iliyopatikana (Siku 30 Zilizopita)'; // Swahili: Commission Earned Trend (Last 30 Days)
    protected static ?string $pollingInterval = '60s';
    protected static bool $isLazy = true;
    protected int | string | array $columnSpan = 1; // Default to 1, can be part of a 2-column layout

    protected function getData(): array
    {
        $appTimezone = Config::get('app.timezone', 'UTC');
        $endDate = Carbon::now($appTimezone)->endOfDay();
        $startDate = Carbon::now($appTimezone)->subDays(29)->startOfDay();

        $getCommissionTrend = function ($modelClass) use ($startDate, $endDate) {
            return Trend::model($modelClass)
                ->between($startDate, $endDate)
                ->perDay()
                ->dateColumn('processed_at') // Assuming commission is tied to 'processed_at' date
                ->sum('commission'); // Sum of commission
        };

        $airtelCommissionTrend = $getCommissionTrend(AirtelTransaction::class);
        $halotelCommissionTrend = $getCommissionTrend(HalotelTransaction::class);

        // Create a complete list of dates for the labels
        $dateLabels = [];
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $dateLabels[$currentDate->toDateString()] = $currentDate->isoFormat('D MMM');
            $currentDate->addDay();
        }

        // Map trend data to the complete list of dates
        $mapTrendToLabels = function ($trend, $allDateLabels) {
            $trendData = $trend->keyBy(fn (TrendValue $value) => Carbon::parse($value->date)->toDateString())
                               ->map(fn (TrendValue $value) => $value->aggregate);

            $dataSet = [];
            foreach (array_keys($allDateLabels) as $dateString) {
                $dataSet[] = $trendData->get($dateString, 0);
            }
            return $dataSet;
        };

        $airtelCommissionData = $mapTrendToLabels($airtelCommissionTrend, $dateLabels);
        $halotelCommissionData = $mapTrendToLabels($halotelCommissionTrend, $dateLabels);

        return [
            'datasets' => [
                [
                    'label' => 'Kamisheni ya Airtel', // Swahili: Airtel Commission
                    'data' => $airtelCommissionData,
                    'borderColor' => 'rgb(220, 38, 38)',
                    'backgroundColor' => 'rgba(220, 38, 38, 0.7)', // Solid color for bars
                ],
                [
                    'label' => 'Kamisheni ya Halotel', // Swahili: Halotel Commission
                    'data' => $halotelCommissionData,
                    'borderColor' => 'rgb(22, 163, 74)',
                    'backgroundColor' => 'rgba(22, 163, 74, 0.7)', // Solid color for bars
                ],
            ],
            'labels' => array_values($dateLabels),
        ];
    }

    protected function getType(): string
    {
        return 'bar'; // Bar chart for commission amounts
    }

    // Optional: Add chart options for tooltips or stacking if needed
    // protected function getOptions(): array
    // {
    //     return [
    //         'scales' => [
    //             'x' => [
    //                 'stacked' => true, // If you want to stack bars per day
    //             ],
    //             'y' => [
    //                 'stacked' => true, // If you want to stack bars per day
    //             ],
    //         ],
    //     ];
    // }
}
