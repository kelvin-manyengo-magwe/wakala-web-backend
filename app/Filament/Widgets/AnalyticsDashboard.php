<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\AirtelTransaction;
use App\Models\HalotelTransaction;
use App\Models\TransactionType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\HtmlString; // Import HtmlString

class AnalyticsDashboard extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';
    protected static bool $isLazy = true;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $depositTypeId = TransactionType::where('name', 'deposit')->value('id');
        $withdrawalTypeId = TransactionType::where('name', 'withdrawal')->value('id');

        // --- Airtel Stats ---
        $airtelDepositsQuery = AirtelTransaction::where('type_id', $depositTypeId);
        $airtelDepositsAmount = (clone $airtelDepositsQuery)->sum('amount');
        $airtelDepositsCount = (clone $airtelDepositsQuery)->count();

        $airtelWithdrawalsQuery = AirtelTransaction::where('type_id', $withdrawalTypeId);
        $airtelWithdrawalsAmount = (clone $airtelWithdrawalsQuery)->sum('amount');
        $airtelWithdrawalsCount = (clone $airtelWithdrawalsQuery)->count();

        $airtelCommission = AirtelTransaction::sum('commission');
        $airtelLatestFloat = AirtelTransaction::latest('processed_at')->value('float_balance');
        $airtelLogoUrl = asset('images/mno/airtel-money-logo.png');

        // --- Halotel Stats ---
        $halotelDepositsQuery = HalotelTransaction::where('type_id', $depositTypeId);
        $halotelDepositsAmount = (clone $halotelDepositsQuery)->sum('amount');
        $halotelDepositsCount = (clone $halotelDepositsQuery)->count();

        $halotelWithdrawalsQuery = HalotelTransaction::where('type_id', $withdrawalTypeId);
        $halotelWithdrawalsAmount = (clone $halotelWithdrawalsQuery)->sum('amount');
        $halotelWithdrawalsCount = (clone $halotelWithdrawalsQuery)->count();

        $halotelCommission = HalotelTransaction::sum('commission');
        $halotelLatestFloat = HalotelTransaction::latest('processed_at')->value('float_balance');
        $halotelLogoUrl = asset('images/mno/halo-pesa-logo.png');

        // --- Combined Stats ---
        $totalDepositsAmount = $airtelDepositsAmount + $halotelDepositsAmount;
        $totalDepositsCount = $airtelDepositsCount + $halotelDepositsCount;

        $totalWithdrawalsAmount = $airtelWithdrawalsAmount + $halotelWithdrawalsAmount;
        $totalWithdrawalsCount = $airtelWithdrawalsCount + $halotelWithdrawalsCount;

        $totalCommission = $airtelCommission + $halotelCommission;
        $totalLatestFloat = ($airtelLatestFloat ?? 0) + ($halotelLatestFloat ?? 0);

        $wakalaCount = User::role('wakala')->count();
        // ... (sync time logic as before) ...
        $latestAirtelSync = AirtelTransaction::latest('processed_at')->value('processed_at');
        $latestHalotelSync = HalotelTransaction::latest('processed_at')->value('processed_at');
        $overallLatestSync = null;
        if ($latestAirtelSync && $latestHalotelSync) {
            $overallLatestSync = max(Carbon::parse($latestAirtelSync), Carbon::parse($latestHalotelSync));
        } elseif ($latestAirtelSync) {
            $overallLatestSync = Carbon::parse($latestAirtelSync);
        } elseif ($latestHalotelSync) {
            $overallLatestSync = Carbon::parse($latestHalotelSync);
        }
        $syncTimeText = $overallLatestSync ? 'Dakika ' . round($overallLatestSync->diffInMinutes(now())) . ' zilizopita' : 'Hakuna Usawazishaji';

        // Helper function to format description with count for MNO specific cards
        $formatMnoDescriptionWithCount = function($logoUrl, $altText, $count) {
            $countText = number_format($count) . ' Miamala'; // "Miamala" is Swahili for Transactions
            return new HtmlString(
                '<div class="flex items-center justify-between text-xs mt-1">' .
                    '<img src="' . $logoUrl . '" alt="' . $altText . '" class="h-5 w-auto object-contain mr-2">' . // mr-2 provides space after logo
                    '<span>' . $countText . '</span>' .
                '</div>'
            );
        };

        // Helper function to append count to total description
        $appendCountToDescription = function($descriptionText, $count) {
            // Adding a non-breaking space before the count in parentheses
            return $descriptionText . ' (' . number_format($count) . ' Miamala)';
        };


        return [
            // Row 1: Overall Totals - Using your existing labels and icons
            Stat::make('Jumla ya Miamala ya kuweka', 'Tsh ' . number_format($totalDepositsAmount))
                ->description($appendCountToDescription('Airtel na Halotel', $totalDepositsCount))
                ->color('success')
                ->icon('heroicon-o-arrow-trending-down'),
            Stat::make('Jumla ya Miamala ya kutoa', 'Tsh ' . number_format($totalWithdrawalsAmount))
                ->description($appendCountToDescription('Airtel na Halotel', $totalWithdrawalsCount))
                ->color('danger')
                ->icon('heroicon-o-arrow-trending-up'),
            Stat::make('Jumla ya Kamisheni', 'Tsh ' . number_format($totalCommission))
                ->description('Airtel na Halotel')
                ->color('primary')
                ->icon('heroicon-o-banknotes'),
            Stat::make('Jumla ya Floti', 'Tsh ' . number_format($totalLatestFloat))
                ->description('Floti Airtel + Halotel')
                ->color('warning')
                ->icon('heroicon-o-wallet'),

            // Row 2: Airtel Specifics - With Logo AND Count
            Stat::make('Miamala ya kuweka Airtel', 'Tsh ' . number_format($airtelDepositsAmount))
                ->description($formatMnoDescriptionWithCount($airtelLogoUrl, 'Airtel Money Logo', $airtelDepositsCount))
                ->color('success'),
            Stat::make('Miamala ya kutoa Airtel', 'Tsh ' . number_format($airtelWithdrawalsAmount))
                ->description($formatMnoDescriptionWithCount($airtelLogoUrl, 'Airtel Money Logo', $airtelWithdrawalsCount))
                ->color('danger'),
            Stat::make('Kamisheni ya Airtel', 'Tsh ' . number_format($airtelCommission))
                ->description(new HtmlString('<img src="' . $airtelLogoUrl . '" alt="Airtel Money Logo" class="h-6 w-auto object-contain">'))
                ->color('primary'),
            Stat::make('Floti ya Airtel', 'Tsh ' . number_format($airtelLatestFloat ?? 0))
                ->description(new HtmlString('<img src="' . $airtelLogoUrl . '" alt="Airtel Money Logo" class="h-6 w-auto object-contain">'))
                ->color('warning'),

            // Row 3: Halotel Specifics - With Logo AND Count
            Stat::make('Miamala ya kuweka Halotel', 'Tsh ' . number_format($halotelDepositsAmount))
                ->description($formatMnoDescriptionWithCount($halotelLogoUrl, 'Halopesa Logo', $halotelDepositsCount))
                ->color('success'),
            Stat::make('Miamala ya kutoa Halotel', 'Tsh ' . number_format($halotelWithdrawalsAmount))
                ->description($formatMnoDescriptionWithCount($halotelLogoUrl, 'Halopesa Logo', $halotelWithdrawalsCount))
                ->color('danger'),
            Stat::make('Kamisheni ya Halotel', 'Tsh ' . number_format($halotelCommission))
                ->description(new HtmlString('<img src="' . $halotelLogoUrl . '" alt="Halopesa Logo" class="h-6 w-auto object-contain">'))
                ->color('primary'),
            Stat::make('Floti ya Halotel', 'Tsh ' . number_format($halotelLatestFloat ?? 0))
                ->description(new HtmlString('<img src="' . $halotelLogoUrl . '" alt="Halopesa Logo" class="h-6 w-auto object-contain">'))
                ->color('warning'),

            // Row 4: Admin Info
            Stat::make('Idadi ya Wakala', number_format($wakalaCount))
                 ->color('info')
                 ->icon('heroicon-o-users'),
            /*Stat::make('Usawazishaji wa Mwisho', $syncTimeText)
                 ->description('Data ya Miamala')
                 ->color('gray')
                 ->icon('heroicon-o-arrow-path'),*/
        ];
    }
}
