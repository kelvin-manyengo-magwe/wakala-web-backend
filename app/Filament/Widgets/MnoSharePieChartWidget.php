<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\AirtelTransaction;
use App\Models\HalotelTransaction;
use Carbon\Carbon;

class MnoSharePieChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Mgao wa Miamala kwa MNO (Jumla)'; // Swahili: Transaction Share per MNO (Total)
    protected static ?string $pollingInterval = '120s'; // Refresh less frequently for totals
    protected static bool $isLazy = true;
    protected int | string | array $columnSpan = 1; // Default to 1

    // Optional: Set a fixed height for pie charts to look good
    protected static ?string $maxHeight = '300px';


    protected function getData(): array
    {
        $airtelTotalTransactions = AirtelTransaction::count();
        $halotelTotalTransactions = HalotelTransaction::count();

        $totalTransactions = $airtelTotalTransactions + $halotelTotalTransactions;

        // Avoid division by zero if no transactions
        $airtelPercentage = ($totalTransactions > 0) ? round(($airtelTotalTransactions / $totalTransactions) * 100, 1) : 0;
        $halotelPercentage = ($totalTransactions > 0) ? round(($halotelTotalTransactions / $totalTransactions) * 100, 1) : 0;

        return [
            'datasets' => [
                [
                    'label' => 'Mgao wa Miamala', // Swahili: Transaction Share
                    'data' => [$airtelTotalTransactions, $halotelTotalTransactions], // Or use percentages: [$airtelPercentage, $halotelPercentage]
                    'backgroundColor' => [
                        'rgba(220, 38, 38, 0.8)', // Red-ish for Airtel
                        'rgba(22, 163, 74, 0.8)',  // Green-ish for Halotel
                    ],
                    'borderColor' => [
                        'rgb(255, 255, 255)', // White border for segments
                        'rgb(255, 255, 255)',
                    ],
                ],
            ],
            'labels' => [
                'Airtel (' . $airtelPercentage . '%)',
                'Halotel (' . $halotelPercentage . '%)'
            ],
        ];
    }

    protected function getType(): string
    {
        return 'pie'; // Or 'doughnut'
    }
}
