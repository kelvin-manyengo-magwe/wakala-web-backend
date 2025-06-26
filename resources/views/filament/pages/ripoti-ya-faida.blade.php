<x-filament-panels::page>
    <form wire:submit.prevent class="mb-6">
        {{ $this->form }}
    </form>

    {{-- Main Summary Section --}}
    <div class="p-6 bg-white rounded-xl shadow-lg dark:bg-gray-800 border-t-4 border-primary-500 mb-8">
        <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-4">Utendaji ({{ \Carbon\Carbon::parse($filterData['startDate'])->format('d M') }} - {{ \Carbon\Carbon::parse($filterData['endDate'])->format('d M Y') }})</h2>
        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Jumla ya Mtaji (Tangu Mwanzo)</dt>
                    <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-200">{{ number_format($totalInitialInvestment, 2) }} TZS</dd>
                </div>
                 <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Thamani ya Mali Mwanzo wa Biashara</dt>
                    <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-200">{{ number_format($openingTotalAssets, 2) }} TZS</dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Jumla ya Kamisheni (Kipindi Hiki)</dt>
                    <dd class="mt-1 text-lg font-semibold text-green-600 dark:text-green-400">+{{ number_format($totalCommissionInPeriod, 2) }} TZS</dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Thamani ya Biashara Sasa (Makadirio)</dt>
                    <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-200">{{ number_format($closingTotalAssets, 2) }} TZS</dd>
                </div>
                <div class="sm:col-span-2 mt-2 pt-4 border-t border-dashed">
                    <dt class="text-base font-bold text-gray-700 dark:text-gray-200">Faida Halisi ya Biashara (Tangu Mwanzo)</dt>
                    <dd class="mt-1 text-2xl font-extrabold {{ $closingTotalAssets - $totalInitialInvestment < 0 ? 'text-red-600' : 'text-primary-600' }}">
                       {{ number_format($closingTotalAssets - $totalInitialInvestment, 2) }} TZS
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white mb-6">Uchambuzi kwa Kila Duka</h2>

    <div class="space-y-6">
        @forelse ($shopReportData as $shopData)
            <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
                <div class="flex flex-col sm:flex-row gap-6">
                    <div>
                        @if($shopData['image_url'])
                            <img src="{{ $shopData['image_url'] }}" alt="Picha ya {{ $shopData['name'] }}" class="w-48 h-32 object-cover rounded-lg shadow-sm">
                        @else
                            <div class="w-48 h-32 bg-gray-200 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                                <span class="text-sm text-gray-500">Hakuna Picha</span>
                            </div>
                        @endif
                        <h3 class="text-lg font-bold text-primary-600 dark:text-primary-400 mt-2">{{ $shopData['name'] }}</h3>
                    </div>
                    <div class="flex-1">
                         <div class="w-full text-sm space-y-2 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                             <div class="flex justify-between items-center text-lg font-bold">
                                <span class="text-gray-700 dark:text-gray-200">Faida ya Duka:</span>
                                <span class="{{ $shopData['net_profit_period'] < 0 ? 'text-red-600' : 'text-green-600' }}">{{ number_format($shopData['net_profit_period'], 2) }} TZS</span>
                            </div>
                            <h4 class="font-semibold text-md pt-3 text-gray-600 dark:text-gray-300">Uchambuzi kwa Mtandao:</h4>
                             @foreach($shopData['mno_data'] as $mnoKey => $mnoDetails)
                                <div class="flex justify-between pl-4">
                                    <span class="text-gray-500">Kamisheni ({{ ucfirst($mnoKey) }}):</span>
                                    <span class="font-medium text-gray-800 dark:text-gray-200">+{{ number_format($mnoDetails['commission'], 2) }} TZS</span>
                                </div>
                            @endforeach
                         </div>
                    </div>
                </div>
            </div>
        @empty
            <p>Hakuna data ya kuonyesha.</p>
        @endforelse
    </div>
</x-filament-panels::page>
