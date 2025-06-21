<x-filament-panels::page.simple>


  {{-- Big Celebration Icon --}}


<div class="mb-8 text-7xl text-primary-500 animate-pulse">🎉</div>

    <div class="relative flex min-h-screen flex-col items-center justify-center p-6 text-center overflow-hidden">
        {{-- Confetti Canvas --}}
        <canvas id="confetti-canvas" class="pointer-events-none fixed inset-0 z-0 w-full h-full"></canvas>






        {{-- Content Block --}}
        <div class="relative z-10 max-w-md">
            <div class="mb-8 text-7xl text-primary-500 animate-pulse">🎉</div>
            <h1 class="mb-4 text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white md:text-4xl">
                Hongera Sana, {{ $adminName }}!
            </h1>
            <p class="mb-8 text-lg text-gray-600 dark:text-gray-400">
                Akaunti yako ya Msimamizi Mkuu kwa <strong>{{ config('app.name', 'WakalaPro') }}</strong> imeundwa kikamilifu!
                Tumekutumia SMS ya kukaribisha.
            </p>
            <x-filament::button
                tag="a"
                :href="route(config('filament.auth.login', 'filament.admin.auth.login'))"
                color="danger"
                size="xl"
                class="w-full"
            >
                Endelea Kuingia Sasa
            </x-filament::button>
        </div>
    </div>

    {{-- Confetti Script --}}
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const canvas = document.getElementById('confetti-canvas');
            if (!canvas) return;

            const confettiInstance = confetti.create(canvas, {
                resize: true,
                useWorker: true,
            });

            const duration = 3000; // 3 seconds
            const animationEnd = Date.now() + duration;
            const defaults = {
                startVelocity: 25,
                spread: 360,
                ticks: 60,
                zIndex: 0,
                scalar: 0.9,
            };

            function randomInRange(min, max) {
                return Math.random() * (max - min) + min;
            }

            (function frame() {
                const timeLeft = animationEnd - Date.now();
                if (timeLeft <= 0) return;

                const particleCount = 50 * (timeLeft / duration);

                confettiInstance({
                    ...defaults,
                    particleCount,
                    origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 },
                });

                confettiInstance({
                    ...defaults,
                    particleCount,
                    origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 },
                });

                requestAnimationFrame(frame);
            })();
        });
    </script>
</x-filament-panels::page.simple>
