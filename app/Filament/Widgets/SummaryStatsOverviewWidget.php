<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\AirtelTransaction;
use App\Models\HalotelTransaction;
use App\Models\TransactionType;
use Carbon\Carbon;
use Illuminate\Support\HtmlString; // Import HtmlString

class SummaryStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '60s';
    protected static bool $isLazy = true;
    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $depositTypeId = TransactionType::where('name', 'deposit')->value('id');
        $withdrawalTypeId = TransactionType::where('name', 'withdrawal')->value('id');

        $getCombinedTransactionCount = function ($startDate, $endDate = null) {
            $queryEnd = $endDate ?: Carbon::now();
            $airtelCount = AirtelTransaction::whereBetween('processed_at', [$startDate, $queryEnd])->count();
            $halotelCount = HalotelTransaction::whereBetween('processed_at', [$startDate, $queryEnd])->count();
            return $airtelCount + $halotelCount;
        };

        $transactionsToday = $getCombinedTransactionCount($today, $today->copy()->endOfDay());
        $transactionsThisWeek = $getCombinedTransactionCount($startOfWeek, $endOfWeek);
        $transactionsThisMonth = $getCombinedTransactionCount($startOfMonth, $endOfMonth);

        $totalCommissionAirtel = AirtelTransaction::sum('commission');
        $totalCommissionHalotel = HalotelTransaction::sum('commission');
        $jumlaYaFaidaAuKamisheni = $totalCommissionAirtel + $totalCommissionHalotel;

        $jumlaPesaZilizoingia = AirtelTransaction::where('type_id', $depositTypeId)->sum('amount') +
                               HalotelTransaction::where('type_id', $depositTypeId)->sum('amount');
        $jumlaPesaZilizotoka = AirtelTransaction::where('type_id', $withdrawalTypeId)->sum('amount') +
                              HalotelTransaction::where('type_id', $withdrawalTypeId)->sum('amount');

        $uwianoWaKuingiaKutoka = 'N/A';
        if ($jumlaPesaZilizotoka > 0) {
            $ratio = $jumlaPesaZilizoingia / $jumlaPesaZilizotoka;
            $uwianoWaKuingiaKutoka = number_format($ratio, 2) . ':1';
        } elseif ($jumlaPesaZilizoingia > 0) {
            $uwianoWaKuingiaKutoka = '∞:1 (Hakuna Zilizotoka)';
        }

        $miamalaAirtelJumla = AirtelTransaction::count();
        $miamalaHalotelJumla = HalotelTransaction::count();

        // Paths to your MNO logos
        $airtelLogoUrl = asset('images/mno/airtel-money-logo.png'); // Ensure this path is correct
        $halotelLogoUrl = asset('images/mno/halo-pesa-logo.png');   // Ensure this path is correct

        // Updated "Miamala kwa MNO" stat to include logos directly in the main value
        // This makes the logos more prominent for this specific stat.
        $miamalaKwaMnoValue = new HtmlString(
            '<div class="space-y-1 text-center">' . // text-center for horizontal alignment of items below
                '<div class="flex items-center justify-center space-x-2 rtl:space-x-reverse">' .
                    '<img src="' . $airtelLogoUrl . '" alt="Airtel" class="h-5 w-auto object-contain">' .
                    '<span class="text-lg font-semibold">Airtel: ' . number_format($miamalaAirtelJumla) . '</span>' .
                '</div>' .
                '<div class="flex items-center justify-center space-x-2 rtl:space-x-reverse">' .
                    '<img src="' . $halotelLogoUrl . '" alt="Halotel" class="h-5 w-auto object-contain">' .
                    '<span class="text-lg font-semibold">Halotel: ' . number_format($miamalaHalotelJumla) . '</span>' .
                '</div>' .
            '</div>'
        );


        return [
            Stat::make('Jumla ya Miamala (Leo)', number_format($transactionsToday))
                ->description('Airtel na Halotel')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary'),

            Stat::make('Jumla ya Miamala (Wiki Hii)', number_format($transactionsThisWeek))
                ->description('Kuanzia ' . $startOfWeek->isoFormat('D MMM'))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),

            Stat::make('Jumla ya Miamala (Mwezi Huu)', number_format($transactionsThisMonth))
                ->description('Kwa mwezi wa ' . $startOfMonth->isoFormat('MMMM'))
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('success'),

            Stat::make('Jumla ya Kamisheni Iliyopatikana', 'Tsh ' . number_format($jumlaYaFaidaAuKamisheni))
                ->description('Kamisheni ya Mitandao yote')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),

            /*Stat::make('Uwiano: Kuingia vs Kutoa (Kiasi)', $uwianoWaKuingiaKutoka)
                ->description('Jumla ya amana dhidi ya jumla ya kutolewa')
                ->descriptionIcon('heroicon-m-arrows-right-left')
                ->color('secondary'),*/

            // Updated Stat for "Miamala kwa MNO"
            Stat::make('Jumla ya Miamala', $miamalaKwaMnoValue) // The value is now the HTML string with logos
                ->description('Mgawanyo wa jumla ya miamala') // Keep a general description
                // No descriptionIcon here, as logos are in the value
                ->color('primary'),
        ];
    }
}
