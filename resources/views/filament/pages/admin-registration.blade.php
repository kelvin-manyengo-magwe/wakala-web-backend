<x-filament-panels::page.simple>
    {{-- Custom Card Styling for a more focused registration form --}}
    <div class="mx-auto w-full max-w-xl space-y-8 rounded-2xl bg-white p-8 shadow-2xl ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 md:p-12">

        {{-- App Logo (Optional) --}}
        <div class="flex justify-center">
            {{-- Replace with your actual logo component or img tag --}}
            {{-- Assuming you have resources/views/components/application-logo.blade.php --}}
            <a href="{{ url('/') }}">
                <x-application-logo class="h-16 w-auto text-primary-600 dark:text-primary-400" />
            </a>
        </div>

        <div class="space-y-2 text-center">
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white md:text-3xl">
                {{ $this->getTitle() }} {{-- Get title from Page class --}}
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Jaza fomu kwa makini ili ufungue akaunti yako ya Usimamizi.
            </p>
        </div>

        <form wire:submit.prevent="submitRegistrationForm" class="grid gap-y-6">
            {{ $this->form }}

            {{-- Renders actions defined in getFormActions() of your PHP page class --}}
            <x-filament-panels::form.actions
                :actions="$this->getFormActions()"
                :full-width="true"
            />
        </form>

        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Tayari unayo akaunti?
                <a  href="{{ route(config('filament.auth.login', 'filament.admin.auth.login')) }}"
                    class="font-semibold text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300 hover:underline">
                    Ingia Sasa Hapa.
                </a>
            </p>
        </div>
    </div>
</x-filament-panels::page.simple>
