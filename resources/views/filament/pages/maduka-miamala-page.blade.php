<x-filament-panels::page>

    {{-- Shop Selection Form Section --}}
    <x-filament::section>
        <x-slot name="heading">
            Chuja Miamala kwa Duka
        </x-slot>
        {{ $this->form }}
    </x-filament::section>

    {{-- This main @if block correctly checks if a shop has been selected --}}
    @if ($this->selectedShopId)
        <div class="mt-6 space-y-8">
            <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200 md:text-2xl">
                Unaangalia Miamala ya Duka:
                <span class="font-semibold text-primary-600 dark:text-primary-400">
                    {{ $this->displaySelectedShopName }}
                </span>
            </h2>

            {{-- ======================= AIRTEL TRANSACTIONS TABLE ======================= --}}
            <x-filament::section :collapsible="true" :collapsed="false">
                <x-slot name="heading">
                    Miamala ya Airtel (Jumla: {{ $this->airtelShopTransactionsList->count() }})
                </x-slot>

                {{-- This @if is balanced and only shows the table if there are transactions --}}
                @if ($this->airtelShopTransactionsList->isNotEmpty())
                    <div class="overflow-x-auto rounded-lg shadow ring-1 ring-gray-950/5 dark:ring-white/10">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
                            <thead class="bg-gray-50 dark:bg-white/5">
                                {{-- Table Headers --}}
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Tarehe/Muda</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Aina</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Mteja</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Namba ya Unukuzi (Kumbukumbu)</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Kiasi (TZS)</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Kamisheni (TZS)</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Wakala Aliyeingiza</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Floti (Baada)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-white/5 dark:bg-gray-800">
                                @foreach ($this->airtelShopTransactionsList as $txn)
                                    <tr class="transition-colors duration-150 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="whitespace-nowrap px-4 py-3.5 text-sm text-gray-700 dark:text-gray-300">
                                            <div>{{ $txn->processed_at?->format('d M Y') ?? 'N/A' }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $txn->processed_at?->format('H:i A') ?? '' }}</div>
                                        </td>

                                        {{-- ===== THE DEFINITIVE FIX FOR 'AINA' COLUMN ===== --}}
                                        <td class="px-4 py-3.5 text-sm">
                                            @php
                                                $typeInfo = $this->getTransactionTypeInfo($txn->type?->name ?? '');
                                            @endphp
                                            <x-filament::badge :color="$typeInfo['color']" size="sm">
                                                {{ $typeInfo['label'] }}
                                            </x-filament::badge>
                                        </td>
                                        {{-- ===== END OF FIX ===== --}}

                                        <td class="px-4 py-3.5 text-sm text-gray-700 dark:text-gray-300">
                                            <div>{{ $txn->customer?->name ?? 'N/A' }}</div>
                                            @if($txn->customer?->phone_number)<div class="text-xs text-gray-500 dark:text-gray-400">{{ $txn->customer->phone_number }}</div>@endif
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3.5 font-mono text-sm text-gray-500 dark:text-gray-400">{{ $txn->ref_no }}</td>
                                        <td class="whitespace-nowrap px-4 py-3.5 text-right text-sm font-semibold text-gray-700 dark:text-gray-300">{{ number_format($txn->amount ?? 0, 2) }}</td>
                                        <td class="whitespace-nowrap px-4 py-3.5 text-right text-sm font-semibold text-green-600 dark:text-green-400">+{{ number_format($txn->commission ?? 0, 2) }}</td>
                                        <td class="px-4 py-3.5 text-sm text-gray-700 dark:text-gray-300">{{ $txn->user?->name ?? 'N/A' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3.5 text-right text-sm text-gray-600 dark:text-gray-300">{{ number_format($txn->float_balance ?? 0, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">Hakuna miamala ya Airtel kwa duka hili katika kipindi hiki.</p>
                @endif
            </x-filament::section>

            {{-- ======================= HALOTEL TRANSACTIONS TABLE ======================= --}}
            <x-filament::section :collapsible="true" :collapsed="true">
                <x-slot name="heading">
                    Miamala ya Halotel (Jumla: {{ $this->halotelShopTransactionsList->count() }})
                </x-slot>

                @if ($this->halotelShopTransactionsList->isNotEmpty())
                    <div class="overflow-x-auto rounded-lg shadow ring-1 ring-gray-950/5 dark:ring-white/10">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
                             <thead class="bg-gray-50 dark:bg-white/5">
                                {{-- Table Headers --}}
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Tarehe/Muda</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Aina</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Mteja</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Namba ya Unukuzi (Kumbukumbu)</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Kiasi</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Kamisheni</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Wakala Aliyeingiza</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Floti (Baada)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-white/5 dark:bg-gray-800">
                                @foreach ($this->halotelShopTransactionsList as $txn)
                                    <tr class="transition-colors duration-150 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="whitespace-nowrap px-4 py-3.5 text-sm text-gray-700 dark:text-gray-300"><div>{{ $txn->processed_at?->format('d M Y') ?? 'N/A' }}</div><div class="text-xs text-gray-500 dark:text-gray-400">{{ $txn->processed_at?->format('H:i A') ?? '' }}</div></td>

                                        {{-- ===== THE DEFINITIVE FIX FOR 'AINA' COLUMN ===== --}}
                                        <td class="px-4 py-3.5 text-sm">
                                            @php
                                                $typeInfo = $this->getTransactionTypeInfo($txn->type?->name ?? '');
                                            @endphp
                                            <x-filament::badge :color="$typeInfo['color']" size="sm">
                                                {{ $typeInfo['label'] }}
                                            </x-filament::badge>
                                        </td>
                                        {{-- ===== END OF FIX ===== --}}

                                        <td class="px-4 py-3.5 text-sm text-gray-700 dark:text-gray-300"><div>{{ $txn->customer?->name ?? 'N/A' }}</div>@if($txn->customer?->phone_number)<div class="text-xs text-gray-500 dark:text-gray-400">{{ $txn->customer->phone_number }}</div>@endif</td>
                                        <td class="whitespace-nowrap px-4 py-3.5 font-mono text-sm text-gray-500 dark:text-gray-400">{{ $txn->ref_no }}</td>
                                        <td class="whitespace-nowrap px-4 py-3.5 text-right text-sm font-semibold text-gray-700 dark:text-gray-300">{{ number_format($txn->amount ?? 0, 2) }}</td>
                                        <td class="whitespace-nowrap px-4 py-3.5 text-right text-sm font-semibold text-green-600 dark:text-green-400">+{{ number_format($txn->commission ?? 0, 2) }}</td>
                                        <td class="px-4 py-3.5 text-sm text-gray-700 dark:text-gray-300">{{ $txn->user?->name ?? 'N/A' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3.5 text-right text-sm text-gray-600 dark:text-gray-300">{{ number_format($txn->float_balance ?? 0, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">Hakuna miamala ya Halotel kwa duka hili katika kipindi hiki.</p>
                @endif
            </x-filament::section>

            {{-- You can add other MNO tables here following the same pattern --}}

        </div>
    @else
        <div class="rounded-lg bg-white p-6 text-center text-gray-500 shadow dark:bg-gray-800 dark:text-gray-400">
            <x-heroicon-o-information-circle class="mx-auto h-8 w-8 text-gray-400" />
            <p class="mt-2 text-lg">Tafadhali chagua duka hapo juu ili kuona orodha ya miamala yake.</p>
        </div>
    @endif
</x-filament-panels::page>
