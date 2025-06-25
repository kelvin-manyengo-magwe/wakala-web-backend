
@php
    // The data is now provided by AppServiceProvider, not hardcoded.
    // We access it via the shared 'unreadNotificationsCount'.
    $unreadCount = Filament::getShared('unreadNotificationsCount');
@endphp

@if(auth()->check())
<div class="relative flex items-center" x-data="{ isOpen: false }">
    <a href="{{ \App\Filament\Pages\UserNotifications::getUrl() }}"
       class="relative rounded-full p-1.5 text-gray-500 hover:bg-gray-100 focus:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 focus:outline-none transition-colors duration-200"
       aria-label="Taarifa"
    >
        {{-- Bell Icon --}}
        <x-heroicon-o-bell class="h-6 w-6" />

        {{-- VISUALLY APPEALING CUE: Glowing Pulse and Badge --}}
        @if ($unreadCount > 0)
            {{-- A subtle ping animation that plays once when the page loads --}}
            <span class="absolute right-1 top-1.5 h-3 w-3 rounded-full bg-primary-600 opacity-75 animate-ping"></span>

            {{-- The bright badge with the count --}}
            <span class="absolute right-1 top-1.5 flex h-3 w-3 items-center justify-center rounded-full bg-primary-500 text-[9px] font-bold text-white">
            </span>
        @endif
    </a>
</div>
@endif

    {{-- Dropdown Placeholder (Can be expanded into a full notification list) --}}
    <div
        x-show="isOpen"
        @click.outside="isOpen = false"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 top-full z-50 mt-2 w-80 origin-top-right rounded-xl bg-white p-4 shadow-xl ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
        style="display: none;" {{-- Initially hidden --}}
    >
        <div class="flex items-center justify-between">
            <p class="text-lg font-semibold text-gray-900 dark:text-white">
                Taarifa
            </p>
            {{-- Optional: Link to full notifications page --}}
            {{-- @php
                // $notificationsUrl = \App\Filament\Pages\YourNotificationsPage::getUrl();
            @endphp
            @if(isset($notificationsUrl))=
            <a href="{{ $notificationsUrl }}" class="text-sm font-medium text-primary-600 hover:underline dark:text-primary-400">
                Ona Zote
            </a>
            @endif --}}
        </div>
        <div class="mt-4 space-y-2">
            {{-- Placeholder for actual notifications --}}
            <p class="text-sm text-gray-500 dark:text-gray-400">Hakuna taarifa mpya kwa sasa.</p>
            {{-- Example item:
            <div class="rounded-lg p-3 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                <p class="truncate text-sm font-medium text-gray-900 dark:text-white">Wakala Mpya Amesajiliwa</p>
                <p class="truncate text-xs text-gray-500 dark:text-gray-400">Juma Juma amejiunga.</p>
            </div>
            --}}
        </div>
    </div>
</div>
