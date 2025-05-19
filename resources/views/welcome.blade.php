<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Wakala') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <style>
            body {
                font-family: 'Instrument Sans', sans-serif;
                background-color: #fff; /* Light pink background */
                color: #1b1b18; /* Very dark brown text, almost black */
                display: flex;
                flex-direction: column;
                align-items: center; /* Center content horizontally */
                justify-content: flex-start; /* Align items to the start (top) */
                min-height: 100vh;
                margin: 0;
                padding: 24px; /* Padding, using the base value */
            }

            header {
                width: 100%;
                max-width: 80rem; /* lg breakpoint equivalent in rems */
                margin-bottom: 24px; /* mb-6 equivalent */
                display: flex; /* Use flexbox for layout */
                justify-content: space-between; /* Space out logo and nav */
                align-items: center; /* Vertically center items */
            }

            .logo {
                font-size: 1.5rem; /* text-xl */
                font-weight: 600;  /* font-semibold */
                color: #1b1b18; /* Very dark brown, almost black */
            }

            nav {
                display: flex;
                gap: 16px; /* gap-4 converted to pixels */
            }

            nav a {
                padding: 6px 20px; /* py-1.5 px-5  */
                color: #1b1b18; /* Very dark brown, almost black */
                border-radius: 0.25rem; /* rounded-sm */
                font-size: 0.875rem; /* text-sm */
                line-height: 1.4;
                text-decoration: none; /* Remove underline */
                border: 1px solid transparent; /* Start with transparent border */
                transition: border-color 0.15s ease; /* Smooth transition */
            }

            nav a:hover {
                border-color: #1914004d;  /* #191400 with 30% opacity */
            }

            a.login {
                border: 1,
            }

            main {
                display: flex;
                flex-direction: column;
                align-items: center;
                width: 100%;
                max-width: 80rem; /* lg breakpoint */
                flex-grow: 1;
            }

            .content-section {
                /* Your main content here */
                text-align: center;
            }

            @media (min-width: 64rem) {
                header {
                    margin-bottom: 0; /* mb-0 */
                }

                nav {
                    margin-left: auto; /* Push nav to the right */
                }
            }
        </style>
    </head>
    <body class="bg-[#f7c7c7] text-[#1b1b18]">
        <header class="w-full lg:max-w-4xl flex items-center justify-between">
            <div class="logo">Wakala</div>
            @if (Route::has('login'))
                <nav>
                    @auth
                        <a href="{{ url('/dashboard') }}">Dashibodi</a>
                    @else
                        <a href="{{ route('login') }}" class="login">Ingia</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}">Jisajili</a>
                        @endif
                    @endauth
                </nav>
            @endif
        </header>
        <main class="w-full flex-grow">
            <div class="content-section">
                <h1>Karibu Wakala</h1>
                <p>...</p>
            </div>
        </main>
    </body>
</html>
