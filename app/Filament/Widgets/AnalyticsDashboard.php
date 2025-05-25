<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Str;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;
use App\Models\Device;
use Carbon\Carbon;
use App\Models\User;


class AnalyticsDashboard extends BaseWidget
{

          protected static ?string $pollingInterval = null;
          protected static bool $isLazy = true;



          protected function getStats(): array {
       if (!$this->isDataLoaded()) {
           return $this->getLoadingStats();
       }

       return $this->getRealStats();
   }

   protected function getLoadingStats(): array
    {
        return [
            Stat::make('Taarifa zinapakia...', '')
                ->description('')
                ->color('gray')
                ->icon('heroicon-o-arrow-path')
                ->extraAttributes([
                    'class' => 'fi-stats-overview-stat-loading',
                    'style' => 'grid-column: 1 / -1; text-align: center;'
                ])
        ];
    }



    protected function getRealStats(): array
{
    $amana = Transaction::sum('amount'); // Total deposits or transactions

    $salio = Transaction::latest()->value('float'); // Last known float
    $faida = $amana;

    $jumla = Transaction::count(); // Total transactions
    $wakala = User::count(); // Total devices/wakala

    // Latest transaction sync time (created_at of latest transaction)
    $lastSynced = Transaction::latest()->value('created_at');
    $syncTime = $lastSynced
        ?  ' Dakika '. round(Carbon::parse($lastSynced)->diffInMinutes(now())) .' zilizopita'
        : 'Hakuna';



    return [
        Stat::make('Jumla ya Amana', 'Tsh ' . number_format($amana))

            ->color('success'),



        Stat::make('Salio la Sasa', 'Tsh ' . number_format($salio))

            ->color('success'),

        Stat::make('Faida ya Wiki', 'Tsh ' . number_format($faida))

            ->color('success'),

        Stat::make('Jumla ya Miamala', number_format($jumla))

            ->color('success'),

        Stat::make('Wakala Waliopo', number_format($wakala))

            ->color('danger'),

        Stat::make('Usawazishwaji wa Miamala', $syncTime)
            ->description('')
            ->color('info'),


    ];
}

    protected function isDataLoaded(): bool
    {
        // Implement your actual data loading check
        // For demo, we'll simulate a 1.5 second load time
        if (Str::contains(request()->url(), 'localhost')) {
            usleep(1500000); // 1.5 seconds delay for local testing
        }

        return true;
    }

    public static function getSort(): int
    {
        return 1;
    }
}
