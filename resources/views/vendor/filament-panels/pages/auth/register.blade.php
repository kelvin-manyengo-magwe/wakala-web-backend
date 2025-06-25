<x-filament-panels::page.simple>

    {{-- Prepare PHP variables safely at the top --}}
    @php
        $currentPanelForView = filament()->getCurrentPanel();
        $panelColorsForView = $currentPanelForView?->getColors() ?? [];

        // Primary color for accents (button, links)
        $viewRegBrandPrimary = $panelColorsForView['primary'][600] ?? '#D7263D'; // Your default brand red

        // Colors for decorative boxes
        $viewRegBoxColor1 = 'rgba(215, 38, 61, 0.08)';
        $viewRegBoxColor2 = 'rgba(230, 57, 70, 0.05)';
        $viewRegBoxBorderColor = 'rgba(215, 38, 61, 0.15)';

        // Background and text colors for Light Mode
        $viewRegPageOverallBgLight = '#f8f9fa';
        $viewRegCardActualBgLight = '#ffffff';
        $viewRegTextHeadingActualLight = '#111827';
        $viewRegTextMutedActualLight = '#6b7280';

        // Background and text colors for Dark Mode
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
        :root {
            /* Light Mode CSS Variables from PHP */
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
            :root { /* Dark Mode CSS Variable Overrides */
                --reg-page-overall-bg: {{ $viewRegPageOverallBgDark }};
                --reg-card-actual-bg: {{ $viewRegCardActualBgDark }};
                --reg-text-heading-actual: {{ $viewRegTextHeadingActualDark }};
                --reg-text-muted-actual: {{ $viewRegTextMutedActualDark }};
                --reg-box-color-1: {{ $viewRegBoxColor1Dark }};
                --reg-box-color-2: {{ $viewRegBoxColor2Dark }};
                --reg-box-border-color: {{ $viewRegBoxBorderColorDark }};
            }
        }

        /* --- Full Page Styling (Applied to main content wrapper of simple layout) --- */
        /* This selector targets the div where page.simple renders its content. */
        .fi-simple-layout > div[class*="fi-simple-main"] {
            background-color: var(--reg-page-overall-bg);
            min-height: 100vh; width: 100vw; /* Full viewport coverage */
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            padding: 1.5rem; /* Spacing from screen edges */
            position: relative; /* Context for absolute children (like box container) */
            overflow: hidden;   /* Keep animated boxes contained */
        }

        /* --- Decorative Moving Boxes Background --- */
        .decorative-box-background-container-register { /* Unique class for registration page */
            position: absolute; /* Covers the entire parent (.fi-simple-main) */
            top: 0; left: 0; width: 100%; height: 100%;
            overflow: hidden; /* Essential for containing moving boxes */
            z-index: 0;     /* Positioned behind the registration card */
        }
        .decorative-box-register { /* Individual animated boxes */
            position: absolute; /* Positioned by JS within the container */
            background-color: var(--reg-box-color-1); /* Default, JS can vary */
            border: 1px solid var(--reg-box-border-color);
            opacity: 0; /* Start transparent */
            animation: floatAndSpinForAdminRegister 25s infinite ease-in-out; /* Animation name */
            border-radius: 0.5rem;
            will-change: transform, opacity; /* Performance hint for browser */
        }
        /* Variations for boxes to add visual interest */
        .decorative-box-register.type-2 {
            background-color: var(--reg-box-color-2);
            animation-duration: 30s; /* Different speed */
            border-style: dashed;
            border-color: var(--reg-box-border-color);
        }
        .decorative-box-register.type-3 {
            animation-duration: 20s;
            border-radius: 50%; /* Circular shape */
            border: none; /* No border for circles for a softer look */
        }

        /* Keyframes for box animation */
        @keyframes floatAndSpinForAdminRegister {
            0%   { transform: translateY(110vh) translateX(calc(var(--startX, 50) * 1vw - 50vw)) rotate(-90deg) scale(0.15); opacity: 0; }
            15%  { opacity: 0.7; transform: /* intermediate position */ translateY(calc(60vh + (var(--i, 0) * 3vh))) translateX(calc(var(--startX, 30) * 0.5vw - 15vw)) rotate(calc(var(--i, 0) * 15deg)) scale(0.7); }
            85%  { opacity: 0.7; transform: /* nearing end position */ translateY(calc(-60vh - (var(--i, 0) * 3vh))) translateX(calc(var(--endX, 70) * 0.5vw - 35vw)) rotate(680deg) scale(1.1); }
            100% { transform: translateY(-110vh) translateX(calc(var(--endX, 50) * 1vw - 50vw)) rotate(720deg) scale(0.2); opacity: 0; }
        }
        /* --- End Decorative Boxes --- */

        /* --- Registration Form Card Styling --- */
        .registration-form-card { /* Our styled card for the form */
            position: relative; /* Sits on top of the decorative box container */
            z-index: 1;
            width: 100%;
            max-width: 30rem; /* Approx 480px. Adjust for your preference */
            background-color: var(--reg-card-actual-bg);
            padding: 2.5rem; /* Inner padding */
            border-radius: 0.75rem; /* Tailwind rounded-xl */
            box-shadow: 0 20px 35px -10px rgba(0,0,0,0.15), 0 8px 15px -8px rgba(0,0,0,0.07);
            text-align: center; /* Center content within the card */
        }

        .registration-form-card .logo-area { margin-bottom: 1.75rem; /* Space after logo */ }
        .registration-form-card .logo-area a { display: inline-block; }
        .registration-form-card .logo-area :first-child { /* Target image/text rendered by brand component */
             max-height: 3.5rem; /* Constrain logo height */
             width: auto;
        }

        .registration-form-card .heading-area h1 {
            font-size: 1.8rem; /* Main heading size */
            font-weight: 700;
            color: var(--reg-text-heading-actual);
            margin-bottom: 0.5rem;
        }
        .registration-form-card .subheading-text {
            font-size: 0.9rem;
            color: var(--reg-text-muted-actual);
            margin-bottom: 1.75rem; /* Space before form */
        }

        .registration-form-card .login-link-container { /* Renamed class for clarity */
            font-size: 0.9rem;
            color: var(--reg-text-muted-actual);
            margin-top: 1.5rem; /* Space after form actions */
            border-top: 1px solid {{ $panelColors['gray'][200] ?? '#e5e7eb' }}; /* Light separator */
            padding-top: 1.25rem;
        }
        @media (prefers-color-scheme: dark) {
            .registration-form-card .login-link-container {
                border-top-color: {{ $panelColors['gray'][700] ?? '#374151' }}; /* Darker separator */
            }
        }
        .registration-form-card .login-link-container a {
            color: var(--reg-brand-primary);
            font-weight: 600;
        }
    </style>
    @endPushOnce

    {{-- This div is essential for the JS to append the animated boxes INTO --}}
    {{-- It must be a direct child of the element styled for full-page background & overflow:hidden --}}
    <div class="decorative-box-background-layer-register" id="registrationPageBoxContainer">
        {{-- Moving boxes are generated here by JavaScript --}}
    </div>



        <div class="logo-area text-center">
            <a href="{{ url('/') }}"> {{-- Link to homepage --}}
                {{-- This assumes your AdminPanelProvider has ->brandLogo() correctly defined --}}
                @if($currentPanelForView && method_exists($currentPanelForView, 'getBrandLogoHtml') && ($brandLogoHtmlView = $currentPanelForView->getBrandLogoHtml()))
                    {!! $brandLogoHtmlView !!}
                @else
                    <span class="text-3xl font-extrabold" style="color: {{ $viewRegBrandPrimary }};">
                        {{ config('app.name', 'WakalaTel') }}
                    </span>
                @endif
            </a>
        </div>

        <div class="heading-area">
            {{-- $this->getTitle() comes from your AdminRegistration.php Page class --}}

        </div>

        {{-- Filament Form rendering --}}
        <form wire:submit.prevent="handleAdminRegistration" class="space-y-6">
            {{ $this->form }} {{-- This renders the form fields from AdminRegistration::form() --}}

            {{-- Renders the "KAMILISHA USAJILI" button (and other actions if defined) --}}
            <x-filament-panels::form.actions
                :actions="$this->getFormActions()"
                :full-width="true"
            />
        </form>

        {{-- Link back to Login page --}}
        <div class="login-link-container">
            <span>Tayari una akaunti? </span>
            <a href="{{ filament()->getLoginUrl() }}"
               class="ml-1 hover:underline">
                Ingia Sasa Hapa.
            </a>
        </div>


    {{-- JavaScript for creating and animating the decorative boxes --}}
    @pushOnce('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const bgBoxRegContainer = document.getElementById('registrationPageBoxContainer');
            if (!bgBoxRegContainer) {
                console.warn('Registration page box container not found.');
                return;
            }

            const numBoxes = 20; // Adjust for density
            const boxColorsArr = [ // Use array for easier random selection
                'var(--reg-box-color-1)',
                'var(--reg-box-color-2)'
                // Add more rgba colors with low alpha if desired for variety
            ];

            for (let i = 0; i < numBoxes; i++) {
                const box = document.createElement('div');
                box.classList.add('decorative-box-register'); // Use specific class

                const type = Math.random();
                if (type < 0.33) { /* No extra type class */ }
                else if (type < 0.66) { box.classList.add('type-2'); }
                else { box.classList.add('type-3'); }

                const size = Math.random() * 100 + 50; // Range 50px to 150px
                box.style.width = size + 'px';
                box.style.height = size + 'px';

                // Start from random positions across the viewport (can be off-screen initially)
                box.style.left = (Math.random() * 130 - 15) + 'vw'; // -15vw to 115vw
                box.style.top = (Math.random() * 130 - 15) + 'vh';   // -15vh to 115vh

                // CSS variables for varied animation paths within the keyframes
                box.style.setProperty('--startX', Math.random() * 100);
                box.style.setProperty('--endX', Math.random() * 100);
                box.style.setProperty('--i', i); // Can be used for slight variation in keyframes

                // Start animations at different points in their cycle
                const animationDuration = parseFloat(getComputedStyle(box).animationDuration || '20');
                box.style.animationDelay = (Math.random() * animationDuration * -0.8) + 's'; // Start some already in motion

                box.style.backgroundColor = boxColorsArr[Math.floor(Math.random() * boxColorsArr.length)];

                // Some boxes without border for visual variety (excluding circles typically)
                if (Math.random() > 0.5 && !box.classList.contains('type-3')) {
                    box.style.border = 'none';
                }

                bgBoxRegContainer.appendChild(box);
            }
        });
    </script>
    @endPushOnce
</x-filament-panels::page.simple>
