<x-filament-panels::page.simple>
    @php
        $panel = filament();
        $customRegistrationUrl = null;
        try {
            // Make sure your AdminRegistration page class is correctly namespaced and has the getUrl method.
            // Replace 'admin' with $panel->getId() for dynamic panel ID.
            $customRegistrationUrl = \App\Filament\Pages\AdminRegistration::getUrl(panel: $panel->getId());
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Filament Login Page: Could not generate AdminRegistration URL: " . $e->getMessage());
        }
    @endphp

    {{-- Use the correct link component tag --}}
    @if ($customRegistrationUrl)
        <x-slot name="subheading">
            <div class="text-center"> {{-- Ensure subheading content is centered if that's the style --}}
                <span>{{ __('filament-panels::pages/auth/login.actions.register.before') }}</span>
                {{-- Use filament::link for a standard themed link --}}
                <x-filament::link :href="$customRegistrationUrl">
                    {{ __('filament-panels::pages/auth/login.actions.register.label') }}
                </x-filament::link>
            </div>
        </x-slot>
    @endif

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, scopes: $this->getRenderHookScopes()) }}

    <x-filament-panels::form id="form" wire:submit="authenticate">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, scopes: $this->getRenderHookScopes()) }}
</x-filament-panels::page.simple>
