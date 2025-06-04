<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\AirtelTransaction;
use App\Models\HalotelTransaction;
use Carbon\Carbon;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Facades\Config; // To get app.timezone

class TransactionTrendChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Mwenendo wa Idadi ya Miamala (Siku 30 Zilizopita)';
    protected static ?string $pollingInterval = '60s';
    protected static bool $isLazy = true;
    // protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        // Use the application's timezone for consistency
        $appTimezone = Config::get('app.timezone', 'UTC'); // Default to UTC if not set

        // Define the overall period for the chart
        $endDate = Carbon::now($appTimezone)->endOfDay(); // Today, end of day in app timezone
        $startDate = Carbon::now($appTimezone)->subDays(29)->startOfDay(); // 30 days ago, start of day

        // Helper to get trend data
        $getTrendData = function ($modelClass) use ($startDate, $endDate) {
            return Trend::model($modelClass)
                ->between($startDate, $endDate)
                ->perDay() // This should aggregate by the date part of 'processed_at'
                ->dateColumn('processed_at') // Explicitly specify the date column
                ->count();
        };

        $airtelTrend = $getTrendData(AirtelTransaction::class);
        $halotelTrend = $getTrendData(HalotelTransaction::class);

        // Create a complete list of dates for the labels (from startDate to endDate)
        $dateLabels = [];
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $dateLabels[$currentDate->toDateString()] = $currentDate->isoFormat('D MMM');
            $currentDate->addDay();
        }

        // Map trend data to the complete list of dates, filling in 0 for missing days
        $mapTrendToLabels = function ($trend, $allDateLabels) {
            $trendData = $trend->keyBy(fn (TrendValue $value) => Carbon::parse($value->date)->toDateString())
                               ->map(fn (TrendValue $value) => $value->aggregate);

            $dataSet = [];
            foreach (array_keys($allDateLabels) as $dateString) {
                $dataSet[] = $trendData->get($dateString, 0); // Default to 0 if no data for that day
            }
            return $dataSet;
        };

        $airtelData = $mapTrendToLabels($airtelTrend, $dateLabels);
        $halotelData = $mapTrendToLabels($halotelTrend, $dateLabels);

        return [
            'datasets' => [
                [
                    'label' => 'Miamala ya Airtel',
                    'data' => $airtelData,
                    'borderColor' => 'rgb(220, 38, 38)',
                    'backgroundColor' => 'rgba(220, 38, 38, 0.3)',
                    'fill' => 'start',
                ],
                [
                    'label' => 'Miamala ya Halotel',
                    'data' => $halotelData,
                    'borderColor' => 'rgb(22, 163, 74)',
                    'backgroundColor' => 'rgba(22, 163, 74, 0.3)',
                    'fill' => 'start',
                ],
            ],
            'labels' => array_values($dateLabels), // Use the formatted date strings as labels
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
