<?php
namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\AirtelTransaction;
use App\Models\HalotelTransaction;
use App\Models\User;
use Carbon\Carbon;

class AnalyticsDashboard extends BaseWidget
{
    protected static ?string $pollingInterval = '60s'; // e.g. refresh every 60 seconds
    protected static bool $isLazy = true;

    protected function getStats(): array
    {
        // Airtel Stats
        $airtelTotalAmount = AirtelTransaction::sum('amount');
        $airtelCommission = AirtelTransaction::sum('commission'); // Example
        $airtelCount = AirtelTransaction::count();
        $latestAirtelSync = AirtelTransaction::latest('processed_at')->value('processed_at');

        // Halotel Stats
        $halotelTotalAmount = HalotelTransaction::sum('amount');
        $halotelCommission = HalotelTransaction::sum('commission'); // Example
        $halotelCount = HalotelTransaction::count();
        $latestHalotelSync = HalotelTransaction::latest('processed_at')->value('processed_at');

        // Overall Stats
        $totalAmount = $airtelTotalAmount + $halotelTotalAmount;
        $totalCommission = $airtelCommission + $halotelCommission; // Example
        $totalTransactions = $airtelCount + $halotelCount;
        $wakalaCount = User::count(); // Or filter by role: User::role('wakala')->count();

        $latestSyncTimes = collect([$latestAirtelSync, $latestHalotelSync])->filter()->map(fn($time) => Carbon::parse($time))->max();
        $syncTimeText = $latestSyncTimes ? __('dashboard.minutes_ago', ['minutes' => round($latestSyncTimes->diffInMinutes(now()))]) : __('dashboard.no_recent_sync');

        return [
            // Overall Summary First
            Stat::make(__('dashboard.total_revenue'), 'Tsh ' . number_format($totalAmount))
                ->description(__('dashboard.total_transactions') .': '. number_format($totalTransactions))
                ->color('primary')
                ->chart([7, 2, 10, 3, 15, 4, 17]), // Example chart data
            Stat::make(__('dashboard.total_commission_earned'), 'Tsh ' . number_format($totalCommission)) // Example
                 ->description(__('dashboard.this_month')) // Could be dynamic
                 ->color('success'),

            // Airtel Specific
            Stat::make(__('dashboard.airtel_revenue'), 'Tsh ' . number_format($airtelTotalAmount))
                ->description(__('dashboard.airtel_transactions') . ': ' . number_format($airtelCount))
                ->color('red'), // Assuming Airtel is red

            // Halotel Specific
            Stat::make(__('dashboard.halotel_revenue'), 'Tsh ' . number_format($halotelTotalAmount))
                ->description(__('dashboard.halotel_transactions') . ': ' . number_format($halotelCount))
                ->color('green'), // Example color for Halotel

            Stat::make(__('dashboard.wakala_count'), number_format($wakalaCount))
                 ->color('warning'),
            Stat::make(__('dashboard.last_sync'), $syncTimeText)
                ->color('gray'),
        ];
    }
}
