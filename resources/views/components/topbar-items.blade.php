<div class="flex items-center gap-4">
    {{-- We removed the call to the non-existent language-switcher component --}}

    {{-- This is the only part that should be in this file for now --}}
    @if(auth()->check())
    <div class="relative flex items-center">
        <a href="{{ \App\Filament\Pages\UserNotifications::getUrl() }}"
           class="relative rounded-full p-1.5 text-gray-500 hover:bg-gray-100 focus:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 focus:outline-none transition-colors duration-200"
           aria-label="Taarifa"
        >
            <x-heroicon-o-bell class="h-6 w-6" />
            @if ($unreadNotificationsCount > 0)
                <span class="absolute right-1 top-1.5 h-3 w-3 rounded-full bg-primary-600 opacity-75 animate-ping"></span>
                <span class="absolute right-1 top-1.5 flex h-3 w-3 items-center justify-center rounded-full bg-primary-500 text-[9px] font-bold text-white"></span>
            @endif
        </a>
    </div>
    @endif
</div>
