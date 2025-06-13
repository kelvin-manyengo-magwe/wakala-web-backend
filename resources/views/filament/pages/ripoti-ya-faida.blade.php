<x-filament-panels::page>
    <div class="mb-6 p-4 bg-white rounded-lg shadow dark:bg-gray-800">
        {{-- This will render the form defined in the form() method of your Page class --}}
        {{ $this->form }}
    </div>

    {{-- The rest of your stat cards display --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {{-- Card 1: Jumla ya Uwekezaji --}}
        <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
            {{-- ... content for this card ... --}}
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Jumla ya Uwekezaji wa Kuanzia</p>
            <p class="text-lg font-semibold text-gray-700 dark:text-gray-200">Tsh {{ number_format($totalInitialInvestment, 2) }}</p>
        </div>

        {{-- Card 2: Jumla ya Float (Sasa hivi) --}}
        <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
            {{-- ... content for this card ... --}}
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Jumla ya Float (Sasa Mfumo Mzima)</p>
            <p class="text-lg font-semibold text-gray-700 dark:text-gray-200">Tsh {{ number_format($currentTotalSystemFloat, 2) }}</p>
        </div>

        {{-- Card 3: Makadirio ya Taslimu Mfumo Mzima (Sasa) --}}
        <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
            {{-- ... content for this card ... --}}
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Makadirio ya Taslimu Mfumo Mzima</p>
            <p class="text-lg font-semibold text-gray-700 dark:text-gray-200">Tsh {{ number_format($currentTotalSystemCashEstimate, 2) }}</p>
        </div>

        {{-- Card 4: Kamisheni Kipindi Kilichochaguliwa --}}
        <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
            {{-- ... content for this card ... --}}
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Kamisheni Kipindi Kilichochaguliwa</p>
            <p class="text-lg font-semibold text-gray-700 dark:text-gray-200">Tsh {{ number_format($totalCommissionEarnedInPeriod, 2) }}</p>
        </div>

        {{-- Card 5: Makadirio ya Faida ya Jumla --}}
        <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800 {{ $netProfitEstimate >= 0 ? 'border-l-4 border-green-500' : 'border-l-4 border-red-500' }}">
            {{-- ... content for this card ... --}}
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Makadirio ya Faida ya Jumla (Tangu Mwanzo)</p>
            <p class="text-lg font-semibold {{ $netProfitEstimate >= 0 ? 'text-green-600 dark:text-green-300' : 'text-red-600 dark:text-red-300' }}">
                Tsh {{ number_format($netProfitEstimate, 2) }}
            </p>
        </div>
    </div>
</x-filament-panels::page>
