<x-filament-panels::page>
    <form wire:submit.prevent="saveInvestment">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit">
                Hifadhi Taarifa za Uwekezaji
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
