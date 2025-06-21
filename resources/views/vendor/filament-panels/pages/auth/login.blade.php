<x-filament-panels::page.simple>

    @php
        // PHP variables for CSS (keep this block as it was, correctly preparing variables)
        $currentPanelForView = filament()->getCurrentPanel();
        $panelColorsForView = $currentPanelForView?->getColors() ?? [];
        $viewRegBrandPrimary = $panelColorsForView['primary'][600] ?? '#D7263D';
        $viewRegBoxColor1 = 'rgba(215, 38, 61, 0.08)';
        $viewRegBoxColor2 = 'rgba(230, 57, 70, 0.05)';
        // ... (all other PHP color variables from your last correct @php block) ...
        $viewRegBoxBorderColor = 'rgba(215, 38, 61, 0.15)';
        $viewRegPageOverallBgLight = '#f8f9fa';
        $viewRegCardActualBgLight = '#ffffff';
        $viewRegTextHeadingActualLight = '#111827';
        $viewRegTextMutedActualLight = '#6b7280';
        $viewRegPageOverallBgDark = '#0b0f19';
        $viewRegCardActualBgDark = '#1f2937';
        $viewRegTextHeadingActualDark = '#f3f4f6;';
        $viewRegTextMutedActualDark = '#9ca3af;';
        $viewRegBoxColor1Dark = 'rgba(215, 38, 61, 0.06)';
        $viewRegBoxColor2Dark = 'rgba(230, 57, 70, 0.04)';
        $viewRegBoxBorderColorDark = 'rgba(215, 38, 61, 0.1)';
    @endphp

    @pushOnce('styles')
    <style>
        /* --- CSS Variable Definitions (as previously corrected) --- */
        :root {
            --reg-brand-primary: {{ $viewRegBrandPrimary }};
            --reg-box-color-1: {{ $viewRegBoxColor1 }};
            --reg-box-color-2: {{ $viewRegBoxColor2 }};
            --reg-box-border-color: {{ $viewRegBoxBorderColor }};
            --reg-page-overall-bg: {{ $viewRegPageOverallBgLight }};
            --reg-card-actual-bg: {{ $viewRegCardActualBgLight }};
            --reg-text-heading-actual: {{ $viewRegTextHeadingActualLight }};
            --reg-text-muted-actual: {{ $viewRegTextMutedActualLight }};
        }
        @media (prefers-color-scheme: dark) {
            :root { /* Dark Mode Overrides */
                --reg-page-overall-bg: {{ $viewRegPageOverallBgDark }};
                --reg-card-actual-bg: {{ $viewRegCardActualBgDark }};
                --reg-text-heading-actual: {{ $viewRegTextHeadingActualDark }};
                --reg-text-muted-actual: {{ $viewRegTextMutedActualDark }};
                --reg-box-color-1: {{ $viewRegBoxColor1Dark }};
                --reg-box-color-2: {{ $viewRegBoxColor2Dark }};
                --reg-box-border-color: {{ $viewRegBoxBorderColorDark }};
            }
        }

        /* Full Page Styling for simple layout's main content area */
        .fi-simple-layout > main[class*="fi-simple-main"],
        .fi-simple-layout > div:first-child { /* Target wrapper inside page.simple */
            background-color: var(--reg-page-overall-bg);
            min-height: 100vh; width: 100vw;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            padding: 1rem; /* Padding for viewport edges */
            position: relative;
            overflow: hidden;
        }

        /* Decorative Moving Boxes Background Layer */
        /* This DIV needs to be a direct child of the full-page wrapper above for the boxes to fill it. */
        .decorative-box-background-layer-register {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            overflow: hidden; z-index: 0; /* Behind form card */
        }
        .decorative-box-register { /* Styles for individual boxes - as before */
            position: absolute;
            background-color: var(--reg-box-color-1);
            border: 1px solid var(--reg-box-border-color);
            opacity: 0;
            animation: floatAndSpinForAdminRegister 25s infinite ease-in-out;
            border-radius: 0.5rem; will-change: transform, opacity;
        }
        /* ... Type-2, Type-3, @keyframes floatAndSpinForAdminRegister styles as previously defined ... */
        @keyframes floatAndSpinForAdminRegister { /* Your keyframes as before */
            0%   { transform: translateY(110vh) translateX(calc(var(--startX, 50) * 1vw - 50vw)) rotate(-90deg) scale(0.15); opacity: 0; }
            15%  { opacity: 0.7; transform: translateY(calc(60vh + (var(--i, 0) * 3vh))) translateX(calc(var(--startX, 30) * 0.5vw - 15vw)) rotate(calc(var(--i, 0) * 15deg)) scale(0.7); }
            85%  { opacity: 0.7; transform: translateY(calc(-60vh - (var(--i, 0) * 3vh))) translateX(calc(var(--endX, 70) * 0.5vw - 35vw)) rotate(680deg) scale(1.1); }
            100% { transform: translateY(-110vh) translateX(calc(var(--endX, 50) * 1vw - 50vw)) rotate(720deg) scale(0.2); opacity: 0; }
        }


        /* Registration Form Card */
        .registration-form-card {
            position: relative; /* So z-index works to keep it above boxes */
            z-index: 1;
            width: 100%;
            max-width: 30rem; /* Larger card, approx 480px */
            background-color: var(--reg-card-actual-bg); /* Solid background */
            padding: 2.5rem;
          /*  border-radius: 1rem;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.1), 0 10px 20px -10px rgba(0,0,0,0.08); */
            text-align: center; /* Center internal elements like logo and heading */
        }

        /* Styles for elements inside the registration-form-card (logo, heading, form, links) */
        .registration-form-card .logo-area { margin-bottom: 1.75rem; }
        .registration-form-card .logo-area a { display: inline-block; }
        .registration-form-card .logo-area :first-child { max-height: 3.5rem; width: auto; }

        .registration-form-card .heading-area h1 {
            font-size: 1.8rem; font-weight: 700;
            color: var(--reg-text-heading-actual); margin-bottom: 0.5rem;
        }
        .registration-form-card .subheading-text-area {
            font-size: 0.9rem; color: var(--reg-text-muted-actual); margin-bottom: 1.75rem;
        }
        .registration-form-card .login-link-container { /* "Already have account?" link area */
            font-size: 0.9rem; color: var(--reg-text-muted-actual); margin-top: 1.5rem;
            border-top: 1px solid {{ $panelColorsForView['gray'][200] ?? '#e5e7eb' }};
            padding-top: 1.25rem;
        }
        @media (prefers-color-scheme: dark) {
            .registration-form-card .login-link-container { border-top-color: {{ $panelColorsForView['gray'][700] ?? '#374151' }}; }
        }
        .registration-form-card .login-link-container a { color: var(--reg-brand-primary); font-weight: 600; }

        /* Form inputs themselves should be interactive by Filament's default styling */
        /* Ensure no custom CSS is making them pointer-events: none or similar */

    </style>
    @endPushOnce

    {{-- The x-filament-panels::page.simple provides the main centered content area. --}}
    {{-- Inside it, we first put the full-page background box container. --}}
    {{-- Then, as a sibling, our styled registration card sits on top. --}}

    {{-- DIV FOR DECORATIVE BOXES (Appended by JS) --}}
    {{-- This should cover the entire area defined by '.fi-simple-layout > div:first-child' or 'main.fi-simple-main' --}}
    <div class="decorative-box-background-layer-register" id="registrationPageBoxContainerForReal"></div>

    {{-- MAIN REGISTRATION CARD CONTAINER --}}
    <div class="registration-form-card">
        {{-- Content for the card is only here, preventing duplication --}}




        <x-filament-panels::form id="form" wire:submit="authenticate" class="mt-6 space-y-6">
            {{ $this->form }}
            <x-filament-panels::form.actions
                :actions="$this->getCachedFormActions()"
                :full-width="true"
            />
        </x-filament-panels::form>

        <div class="login-link-container">
            <span>Tayari una akaunti?</span>
            <a href="{{ filament()->getLoginUrl() }}" class="ml-1 hover:underline">
                Ingia Sasa Hapa.
            </a>
        </div>
    </div> {{-- End of .registration-form-card --}}

    @pushOnce('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const containerForBoxes = document.getElementById('registrationPageBoxContainerForReal'); // Match new ID
            if (!containerForBoxes) {
                console.warn('Registration page decorative box container NOT FOUND!');
                return;
            }
            // ... (rest of your JavaScript for box generation, ensure class 'decorative-box-register' is used)
            const numBoxes = 25;
            const boxColorsJs = ['var(--reg-box-color-1)', 'var(--reg-box-color-2)'];
            for (let i = 0; i < numBoxes; i++) {
                const box = document.createElement('div');
                box.classList.add('decorative-box-register');
                const type = Math.random();
                if (type < 0.33) {} else if (type < 0.66) {box.classList.add('type-2');} else {box.classList.add('type-3');}
                const size = Math.random() * 120 + 60;
                box.style.width = size + 'px'; box.style.height = size + 'px';
                box.style.left = (Math.random() * 120 - 10) + 'vw';
                box.style.top = (Math.random() * 120 - 10) + 'vh';
                box.style.setProperty('--startX', Math.random() * 100);
                box.style.setProperty('--endX', Math.random() * 100);
                box.style.setProperty('--i', i);
                const animDuration = parseFloat(getComputedStyle(box).animationDuration || '20');
                box.style.animationDelay = (Math.random() * animDuration * -0.9) + 's';
                box.style.backgroundColor = boxColorsJs[Math.floor(Math.random() * boxColorsJs.length)];
                if (Math.random() > 0.55 && !box.classList.contains('type-3')) { box.style.border = 'none';}
                containerForBoxes.appendChild(box);
            }
        });
    </script>
    @endPushOnce
</x-filament-panels::page.simple>
