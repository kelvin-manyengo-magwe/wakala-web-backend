<x-filament-panels::page>
    <div class="mb-6">
        {{ $this->profitReportForm }} {{-- Render the form --}}

        {{-- If you need a button to explicitly trigger calculations after date change:
        <x-filament::button wire:click="calculateProfitReport" class="mt-2">
            Tafuta Ripoti
        </x-filament::button>
        --}}
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <x-filament::stats.card label="Jumla ya Uwekezaji wa Kuanzia"
                                value="Tsh {{ number_format($totalInitialInvestment, 2) }}"
                                icon="heroicon-o-scale" />

        <x-filament::stats.card label="Jumla ya Float (Sasa hivi Mfumo Mzima)"
                                value="Tsh {{ number_format($currentTotalSystemFloat, 2) }}"
                                icon="heroicon-o-arrows-right-left"
                                color="success" />

        <x-filament::stats.card label="Makadirio ya Taslimu Mfumo Mzima (Sasa)"
                                value="Tsh {{ number_format($currentTotalSystemCashEstimate, 2) }}"
                                description="Kulingana na salio la kuanzia na miamala ya kipindi"
                                icon="heroicon-o-currency-dollar"
                                color="info"/>

        <x-filament::stats.card label="Kamisheni Kipindi Kilichochaguliwa"
                                value="Tsh {{ number_format($totalCommissionEarnedInPeriod, 2) }}"
                                icon="heroicon-o-receipt-percent"
                                color="warning"/>

        <x-filament::stats.card label="Makadirio ya Faida ya Jumla (Tangu Mwanzo)"
                                value="Tsh {{ number_format($netProfitEstimate, 2) }}"
                                description="Thamani ya sasa ukitoa uwekezaji wa kuanzia"
                                icon="heroicon-o-chart-pie"
                                color="{{ $netProfitEstimate >= 0 ? 'success' : 'danger' }}" />
    </div>

    {{-- TODO: Display per-shop details if $shopData is populated --}}

</x-filament-panels::page>
