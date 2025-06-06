<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\AirtelTransaction;
use App\Models\HalotelTransaction;
use Carbon\Carbon;
use Carbon\CarbonPeriod; // For date iteration
use Illuminate\Support\Facades\Config;

class ProfitCommissionChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Mwenendo wa Kamisheni Iliyopatikana (Siku 30 Zilizopita)';
    protected static ?string $pollingInterval = '60s';
    protected static bool $isLazy = true;
    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $appTimezone = Config::get('app.timezone', 'UTC');

        // We want data for the last 30 days *including* today.
        // So, we go back 29 days from today to get a 30-day period.
        $endDate = Carbon::now($appTimezone)->startOfDay(); // Today, just the date part for iteration
        $startDate = $endDate->copy()->subDays(29);    // 29 days before today = 30 days total inclusive

        $labelsForChart = [];
        $airtelDailyCommissionData = [];
        $halotelDailyCommissionData = [];

        $period = CarbonPeriod::create($startDate, $endDate);

        // Custom Swahili Month Abbreviation Map (if translatedFormat is unreliable)
        $swahiliMonths = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mac', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
            7 => 'Jul', 8 => 'Ago', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];
        $swahiliDaysShort = [ // J1=Monday, J2=Tuesday etc.
            1 => 'J1', 2 => 'J2', 3 => 'J3', 4 => 'J4', 5 => 'J5', 6 => 'Sb', 7 => 'Jp',
        ];


        foreach ($period as $date) {
            $dateString = $date->toDateString(); // YYYY-MM-DD for queries

            // Generate Label: e.g., "J4 Mei 05" (Jumatano Mei 05) or simply "Mei 05"
            // $dayOfWeekShort = $swahiliDaysShort[$date->dayOfWeekIso]; // Monday=1, Sunday=7
            // $monthShort = $swahiliMonths[$date->month];
            // $labelsForChart[] = $dayOfWeekShort . ' ' . $monthShort . ' ' . $date->format('d');
            // Simpler and more common chart label:
            $labelsForChart[] = $date->format('d') . ' ' . ($swahiliMonths[$date->month] ?? $date->format('M'));


            // Calculate Airtel commission for this specific day
            $airtelCommissionForDay = AirtelTransaction::whereDate('processed_at', $dateString)
                                                     ->sum('commission');
            $airtelDailyCommissionData[] = $airtelCommissionForDay ?? 0;

            // Calculate Halotel commission for this specific day
            $halotelCommissionForDay = HalotelTransaction::whereDate('processed_at', $dateString)
                                                      ->sum('commission');
            $halotelDailyCommissionData[] = $halotelCommissionForDay ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Kamisheni ya Airtel (Tsh)',
                    'data' => $airtelDailyCommissionData,
                    'borderColor' => 'rgb(220, 38, 38)',
                    'backgroundColor' => 'rgba(220, 38, 38, 0.7)',
                ],
                [
                    'label' => 'Kamisheni ya Halotel (Tsh)',
                    'data' => $halotelDailyCommissionData,
                    'borderColor' => '#ebcfc6',
                    'backgroundColor' => '#ebcfc6',
                ],
            ],
            'labels' => $labelsForChart,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
