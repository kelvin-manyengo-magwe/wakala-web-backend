<x-filament-panels::page.simple>
    <style>
        /* Optional: Custom styles for finer control if Tailwind classes are not enough */
        .auth-form-container {
            background-color: #fff; /* Ensure white background for the card on all themes */
        }
        @media (prefers-color-scheme: dark) {
            .auth-form-container {
                background-color: #1f2937; /* Tailwind gray-800 for dark mode card */
            }
        }
        .form-input-focused {
        border-color: #D7263D !important;
        box-shadow: 0 0 0 1px #D7263D !important;
    }
    </style>

    {{-- Main Card Container --}}


        {{-- Logo or App Name --}}
        <div class="mb-8 flex justify-center">
            <a href="{{ url('/') }}" class="inline-block">
                {{-- Assuming you have this brand component defined for your panel --}}
                <x-filament.brand.wakala-brand />
                {{-- Fallback if brand component not found or for simple text:
                <span class="text-4xl font-extrabold tracking-tight text-primary-600 dark:text-primary-400">
                    {{ config('app.name', 'WakalaTel') }}
                </span>
                --}}
            </a>
        </div>

        {{-- Page Title and Subtitle --}}
        <div class="mb-8 space-y-1 text-center">
            


            <p class="text-sm text-gray-500 dark:text-gray-400">
                Jaza fomu ifuatayo kwa usahihi ili kufungua akaunti yako ya usimamizi.
            </p>
        </div>

        {{-- Registration Form --}}
        <form wire:submit.prevent="submitRegistrationForm" class="grid grid-cols-1 gap-y-6">
            {{ $this->form }} {{-- Renders Name, Email, Phone, Password, Password Confirmation --}}

            {{-- Form Actions (Submit Button) --}}
            <x-filament-panels::form.actions
                :actions="$this->getFormActions()" {{-- From AdminRegistration.php --}}
                :full-width="true"
            />
        </form>

        {{-- Link to Login Page --}}
        <div class="mt-8 border-t border-gray-200 pt-6 text-center dark:border-gray-700">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Tayari unayo akaunti?
                <a  href="{{ filament()->getLoginUrl() }}"
                    class="font-semibold text-primary-600 hover:text-primary-500 hover:underline dark:text-primary-400 dark:hover:text-primary-300">
                    Ingia Sasa Hapa.
                </a>
            </p>
        </div>

</x-filament-panels::page.simple>
