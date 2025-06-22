<x-filament-panels::page>
    @php
        // Get data from the Livewire/Page component properties
        $selectedShopIdFromData = $this->shopFilterData['selectedShopId'] ?? null;
        $shopNameForDisplay = $this->displaySelectedShopName ?? null;

        $airtelTransactions = $this->airtelShopTransactions ?? collect();
        $halotelTransactions = $this->halotelShopTransactions ?? collect();
        // $mpesaTransactions = $this->mpesaShopTransactions ?? collect();
    @endphp

    {{-- Shop Selection Form --}}
    <div class="mb-6 rounded-lg bg-white p-4 shadow dark:bg-gray-800">
        <h3 class="mb-3 text-lg font-semibold text-gray-900 dark:text-gray-100">
            Chagua Duka Kuona Miamala Yake
        </h3>
        {{ $this->form }} {{-- Renders the form defined in MadukaMiamalaPage::form() --}}
    </div>

    @if ($selectedShopIdFromData)
        <div class="space-y-8">
            <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200 md:text-2xl">
                Miamala ya Duka:
                <span class="text-primary-600">{{ $shopNameForDisplay ?: 'Duka Lililochaguliwa' }}</span>
            </h2>

            {{-- Airtel Transactions Section --}}
            <x-filament::section :collapsible="true" :collapsed="false">
                <x-slot name="heading">
                    Miamala ya Airtel (Jumla: {{ $airtelTransactions->count() }})
                </x-slot>

                @if ($airtelTransactions->isNotEmpty())
                    <div class="overflow-x-auto rounded-lg shadow ring-1 ring-gray-950/5 dark:ring-white/10">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
                            <thead class="bg-gray-50 dark:bg-white/5">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Tarehe/Muda</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Aina</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Mteja</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Ref No.</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Kiasi (TZS)</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Kamisheni (TZS)</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Wakala</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Float (Baada)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-white/5 dark:bg-gray-800">
                                @foreach ($airtelTransactions as $txn)
                                    <tr class="transition-colors duration-150 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="whitespace-nowrap px-4 py-3.5 text-sm text-gray-700 dark:text-gray-300">
                                            <div>{{ $txn->processed_at?->format('d M Y') ?? 'N/A' }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $txn->processed_at?->format('H:i A') ?? '' }}</div>
                                        </td>
                                        <td class="px-4 py-3.5 text-sm">
                                            <x-filament::badge
                                                :color="match(strtolower($txn->type?->name ?? '')) {
                                                    'deposit' => 'success',
                                                    'withdrawal' => 'danger',
                                                    default => 'gray'
                                                }"
                                                size="sm"
                                            >
                                                {{ ucfirst(strtolower($txn->type?->name ?? 'N/A')) }}
                                            </x-filament::badge>
                                        </td>
                                        <td class="px-4 py-3.5 text-sm text-gray-700 dark:text-gray-300">
                                            <div>{{ $txn->customer?->name ?? 'N/A' }}</div>
                                            @if($txn->customer?->phone_number)
                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $txn->customer->phone_number }}</div>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3.5 font-mono text-sm text-gray-500 dark:text-gray-400">{{ $txn->ref_no }}</td>
                                        <td class="whitespace-nowrap px-4 py-3.5 text-right text-sm font-semibold text-gray-700 dark:text-gray-300">{{ number_format($txn->amount ?? 0, 2) }}</td>
                                        <td class="whitespace-nowrap px-4 py-3.5 text-right text-sm font-semibold text-green-600 dark:text-green-400">+{{ number_format($txn->commission ?? 0, 2) }}</td>
                                        <td class="px-4 py-3.5 text-sm text-gray-700 dark:text-gray-300">{{ $txn->user?->name ?? 'N/A' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3.5 text-right text-sm text-gray-600 dark:text-gray-300">{{ number_format($txn->float_balance ?? 0, 2) }}</td>
                                    </tr>
                                @endforeach {{-- End Airtel Foreach --}}
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                        Hakuna miamala ya Airtel kwa duka hili iliyopatikana.
                    </p>
                @endif {{-- End Airtel isNotEmpty If --}}
            </x-filament::section>

            {{-- Halotel Transactions Section --}}
            <x-filament::section :collapsible="true" :collapsed="true">
                <x-slot name="heading">
                    Miamala ya Halotel (Jumla: {{ $halotelTransactions->count() }})
                </x-slot>
                @if ($halotelTransactions->isNotEmpty())
                    <div class="overflow-x-auto rounded-lg shadow ring-1 ring-gray-950/5 dark:ring-white/10">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
                            <thead class="bg-gray-50 dark:bg-white/5">
                                {{-- Table Headers (Identical) --}}
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Tarehe/Muda</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Aina</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Mteja</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Ref No.</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Kiasi (TZS)</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Kamisheni (TZS)</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Wakala</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Float (Baada)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-white/5 dark:bg-gray-800">
                                @foreach ($halotelTransactions as $txn)
                                    <tr class="transition-colors duration-150 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        {{-- Table Cells (Identical structure) --}}
                                        <td class="whitespace-nowrap px-4 py-3.5 text-sm text-gray-700 dark:text-gray-300">
                                            <div>{{ $txn->processed_at?->format('d M Y') ?? 'N/A' }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $txn->processed_at?->format('H:i A') ?? '' }}</div>
                                        </td>
                                        <td class="px-4 py-3.5 text-sm">
                                             <x-filament::badge
                                                :color="match(strtolower($txn->type?->name ?? '')) {
                                                    'deposit' => 'success',
                                                    'withdrawal' => 'danger',
                                                    default => 'gray'
                                                }"
                                                size="sm"
                                            >
                                                {{ ucfirst(strtolower($txn->type?->name ?? 'N/A')) }}
                                            </x-filament::badge>
                                        </td>
                                        <td class="px-4 py-3.5 text-sm text-gray-700 dark:text-gray-300">
                                            <div>{{ $txn->customer?->name ?? 'N/A' }}</div>
                                            @if($txn->customer?->phone_number)
                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $txn->customer->phone_number }}</div>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3.5 font-mono text-sm text-gray-500 dark:text-gray-400">{{ $txn->ref_no }}</td>
                                        <td class="whitespace-nowrap px-4 py-3.5 text-right text-sm font-semibold text-gray-700 dark:text-gray-300">{{ number_format($txn->amount ?? 0, 2) }}</td>
                                        <td class="whitespace-nowrap px-4 py-3.5 text-right text-sm font-semibold text-green-600 dark:text-green-400">+{{ number_format($txn->commission ?? 0, 2) }}</td>
                                        <td class="px-4 py-3.5 text-sm text-gray-700 dark:text-gray-300">{{ $txn->user?->name ?? 'N/A' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3.5 text-right text-sm text-gray-600 dark:text-gray-300">{{ number_format($txn->float_balance ?? 0, 2) }}</td>
                                    </tr>
                                @endforeach {{-- End Halotel Foreach --}}
                            </tbody>
                        </table>
                    </div>
                @else
                     <p class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                        Hakuna miamala ya Halotel kwa duka hili iliyopatikana.
                     </p>
                @endif {{-- End Halotel isNotEmpty If --}}
            </x-filament::section>

            {{-- Add other MNO sections here similarly --}}

        </div> {{-- End of space-y-8 for tables --}}
    @else
        <div class="rounded-lg bg-white p-6 text-center text-gray-500 shadow dark:bg-gray-800 dark:text-gray-400">
            <x-heroicon-o-information-circle class="mx-auto h-8 w-8 text-gray-400" />
            <p class="mt-2 text-lg">Tafadhali chagua duka hapo juu ili kuona orodha ya miamala yake.</p>
        </div>
    @endif {{-- End Main @if ($selectedShopIdFromData) --}}

</x-filament-panels::page>
