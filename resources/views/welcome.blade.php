<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'WakalaTEL') }} - Inua Biashara Yako ya Uwakala</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
    {{-- <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon"> --}}

    <style>
        :root {
            --brand-primary: #D7263D; /* Main Red */
            --brand-primary-dark: #B81D31; /* Darker Red for hover/depth */
            --brand-secondary: #F46036; /* Vibrant Orange/Red accent */
            --brand-dark: #1A1C22;    /* Near Black for text */
            --brand-light: #FFFFFF;
            --brand-bg-main: #FDFDFD; /* Very light, almost white background */
            --brand-bg-alt: #F4F6F8; /* Slightly off-white for alternating sections */
            --brand-text-muted: #525C6B;  /* Softer gray for text */
            --font-family-main: 'Instrument Sans', sans-serif;
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.04);
            --shadow-md: 0 5px 15px rgba(0,0,0,0.08);
            --shadow-lg: 0 15px 35px rgba(0,0,0,0.1), 0 5px 15px rgba(0,0,0,0.07);
            --border-radius-md: 10px;   /* Increased default radius */
            --border-radius-lg: 16px;  /* Large radius for containers like slider */
            --transition-main: all 0.35s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        *, *::before, *::after { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            font-family: var(--font-family-main); background-color: var(--brand-bg-main);
            color: var(--brand-dark); line-height: 1.8; margin: 0; padding: 0;
            overflow-x: hidden; font-size: 17px; /* Slightly larger base for better readability */
            -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;
        }
        .container { width: 92%; max-width: 1320px; margin: 0 auto; padding: 0 1rem; }

        /* Header */
        .main-header {
            padding: 1rem 0; background-color: transparent; position: fixed;
            top: 0; left: 0; width: 100%; z-index: 1000;
            transition: background-color 0.3s ease, box-shadow 0.3s ease, padding 0.3s ease;
        }
        .main-header.scrolled {
            background-color: rgba(255, 255, 255, 0.98); backdrop-filter: blur(12px);
            box-shadow: var(--shadow-md); padding: 0.75rem 0;
        }
        .header-content { display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 2.1rem; font-weight: 800; color: var(--brand-primary); text-decoration: none; letter-spacing: -1px; }
        .main-nav ul { list-style: none; padding: 0; margin: 0; display: flex; align-items: center; gap: 1rem; }
        .main-nav a {
            color: var(--brand-dark); font-size: 1rem; font-weight: 600; text-decoration: none;
            padding: 0.5rem 0.8rem; border-radius: var(--border-radius-sm); transition: var(--transition-main); position: relative;
        }
        .main-nav a::after { /* Underline hover effect */
            content: ''; position: absolute; bottom: -2px; left: 50%; transform: translateX(-50%);
            width: 0; height: 2px; background-color: var(--brand-primary); transition: width 0.3s ease;
        }
        .main-nav a:hover::after, .main-nav a.active::after { width: 80%; }
        .main-nav a.nav-button {
            background-image: linear-gradient(to right, var(--brand-secondary) 0%, var(--brand-primary) 70%, var(--brand-secondary) 100%);
            background-size: 200% auto; color: var(--brand-light) !important; padding: 0.7rem 1.8rem;
            box-shadow: 0 4px 10px rgba(217, 4, 41, 0.25); border: none; font-weight: 600;
        }
        .main-nav a.nav-button::after { display:none; } /* No underline for button */
        .main-nav a.nav-button:hover { background-position: right center; transform: translateY(-2px); box-shadow: 0 6px 15px rgba(217, 4, 41, 0.35); }
        .menu-toggle { display: none; /* ... same as before ... */ }
        main { padding-top: 110px; /* Updated for potentially larger header initially */ }

        /* Hero Section */
        .hero-section {
            padding: 100px 0 120px 0; background: var(--brand-light);
            position: relative; overflow: hidden; /* Essential for ::before/::after elements */
        }
        /* --- CURVED DECORATIVE BACKGROUND ELEMENT --- */
        .hero-section::before {
            content: '';
            position: absolute;
            bottom: -150px; /* Position it to look like it's rising from bottom */
            left: -10%;
            width: 120%;
            height: 400px; /* Height of the curve */
            background-color: var(--brand-primary);
            border-radius: 50% / 100px; /* Creates an asymmetric curve */
            transform: rotate(-4deg); /* Slight rotation for dynamism */
            z-index: 0; /* Behind content */
            opacity: 0.1; /* Very subtle */
            animation: subtleWave 15s ease-in-out infinite alternate;
        }
        .hero-grid { display: grid; grid-template-columns: minmax(400px, 1fr) 1fr; align-items: center; gap: 4rem; position: relative; z-index: 1;}
        .hero-text { animation: fadeInUpSlight 0.8s ease-out 0.2s forwards; opacity:0; }
        .hero-text .eyebrow {
            display: inline-block; padding: 0.3rem 0.8rem;
            font-size: 0.85rem; font-weight: 600; color: var(--brand-primary);
            text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1rem;
            background-color: rgba(217, 4, 41, 0.1); border-radius: var(--border-radius-sm);
        }
        .hero-text h1 {
            font-size: 3.8rem; /* Even larger for impact on big screens */
            font-weight: 800; line-height: 1.2; color: var(--brand-dark);
            margin-bottom: 1.5rem;
        }
        .hero-text h1 .highlight {
            /* More subtle highlight if needed, or keep bright red */
            /* background: linear-gradient(to top, rgba(239, 35, 54, 0.2) 30%, transparent 30%); */
            color: var(--brand-primary);
        }
        .hero-text p.subtitle {
            font-size: 1.25rem; color: var(--brand-text-muted); margin-bottom: 2.5rem; max-width: 580px;
        }
        .cta-button { /* Copied from nav-button for consistency, can be unique */
            background-image: linear-gradient(to right, var(--brand-secondary) 0%, var(--brand-primary) 70%, var(--brand-secondary) 100%);
            background-size: 200% auto; color: var(--brand-light); padding: 1.2rem 2.8rem; /* Larger CTA */
            font-size: 1.15rem; font-weight: 600; text-decoration: none; border-radius: var(--border-radius-md);
            transition: var(--transition-main); display: inline-block; border: none;
            box-shadow: 0 6px 20px rgba(217, 4, 41, 0.3);
        }
        .cta-button:hover { background-position: right center; transform: scale(1.03) translateY(-3px); box-shadow: 0 8px 25px rgba(217, 4, 41, 0.4); }

        /* Image Slider */
        .hero-slider {
            width: 100%; max-width: 650px; /* Increased max-width */
            height: 500px; /* Significantly increased height */
            position: relative; overflow: hidden; border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-lg);
            animation: fadeInScaleUp 0.8s ease-out 0.5s forwards; opacity:0;
            border: 8px solid var(--brand-light); /* Thicker frame */
        }
        .slide {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            opacity: 0;
            animation: smootherCrossfadeEffect 32s infinite ease-in-out; /* 8s per image total (1s in, 6s visible, 1s out) */
            background-size: cover; background-position: center center;
        }
        /* IMPORTANT: Ensure one slide is visible from the start to prevent white flash */
        .slide:first-child { opacity: 1; animation-name: firstSlideFadeIn, smootherCrossfadeEffect; animation-delay: 0s, 0s; animation-duration: 0s, 32s; } /* Make first immediately visible then participate in fade */

        .slide:nth-child(1) { background-image: url("{{ asset('images/mno/wakala-shops1.webp') }}"); animation-delay: 0s; } /* Keep delay for cycling */
        .slide:nth-child(2) { background-image: url("{{ asset('images/mno/wakala-shops2.jpg') }}"); animation-delay: 8s; }
        .slide:nth-child(3) { background-image: url("{{ asset('images/mno/wakala-shops3.jpg') }}"); animation-delay: 16s; }
        .slide:nth-child(4) { background-image: url("{{ asset('images/mno/wakala-shops4.jpg') }}"); animation-delay: 24s; }

        /* Section Styling - Reusable */
        .section-padding { padding: 100px 0; }
        .section-bg-alt { background-color: var(--brand-bg-alt); }
        .section-heading .eyebrow { /* same as hero eyebrow */ }
        .section-heading .section-title { /* same as before */ }
        .section-heading .section-subtitle { /* same as before */ }

        /* Features Grid - Reusable Card Style */
        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; }
        .info-card {
            background-color: var(--brand-light); padding: 2rem; border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-md); text-align: left; transition: var(--transition-main);
            border-left: 5px solid transparent;
        }
        .info-card:hover { transform: translateY(-10px) scale(1.02); box-shadow: var(--shadow-lg); border-left-color: var(--brand-secondary); }
        .info-icon img { height: 50px; width: auto; margin-bottom: 1.25rem; }
        .info-card h3 { font-size: 1.35rem; font-weight: 600; color: var(--brand-dark); margin-bottom: 0.75rem; }
        .info-card p { font-size: 1rem; color: var(--brand-text-muted); line-height: 1.7; }

        /* How It Works Section - Reusable Step Style */
        .steps-list ol { list-style: none; padding-left: 0; counter-reset: step-counter; margin-top: 2rem; }
        .steps-list li {
            background-color: var(--brand-light); padding: 1.5rem; border-radius: var(--border-radius-md);
            font-size: 1.05rem; margin-bottom: 1.5rem; position: relative;
            box-shadow: var(--shadow-sm); display: flex; align-items: flex-start;
            transition: var(--transition-main);
        }
        .steps-list li:hover { box-shadow: var(--shadow-md); transform: translateX(5px); }
        .steps-list li::before { /* Step number */
            content: counter(step-counter); counter-increment: step-counter;
            color: var(--brand-light); background-color: var(--brand-primary);
            min-width: 36px; height: 36px; border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 1.1rem; margin-right: 1.5rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.15); flex-shrink: 0; /* Prevent shrinking */
        }
        .steps-list li div strong { font-weight: 700; color: var(--brand-dark); display: block; margin-bottom: 0.3rem; font-size:1.2rem; }
        .steps-list li div span { color: var(--brand-text-muted); }

        /* Footer */
        .main-footer { background-color: var(--brand-dark); color: #BECADA; text-align: center; padding: 3.5rem 0; margin-top: 0; /* Let sections define bottom margin */ }
        .main-footer p { margin-bottom: 0.5rem; font-size: 0.95rem; }
        .main-footer .footer-links { margin-top: 1rem; }
        .main-footer .footer-links a { color: var(--brand-accent); text-decoration: none; margin: 0 0.75rem; font-size:0.85rem; }
        .main-footer .footer-links a:hover { color: var(--brand-secondary); }

        /* Animations */
        @keyframes smootherCrossfadeEffect { /* Smoother, ensures one is always visible */
            0%   { opacity: 0; animation-timing-function: ease-in; } /* Start hidden */
            3.125% { opacity: 1; } /* Fade in (1s / 32s total duration * 100) */
            21.875% { opacity: 1; } /* Visible for 6s ((1s in + 6s visible)/32s * 100) */
            25%  { opacity: 0; animation-timing-function: ease-out; } /* Fade out (1s out) */
            100% { opacity: 0; } /* Remain hidden for rest of its 8s cycle */
        }
        /* Special animation for first slide to be immediately visible then join cycle */
        @keyframes firstSlideFadeIn { 0% { opacity: 1; } 100% { opacity: 1; } }

        @keyframes fadeInUpSlight { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeInScaleUp { from { opacity: 0; transform: scale(0.92); } to { opacity: 1; transform: scale(1); } }
        @keyframes subtleWave {
            0% { transform: rotate(-4deg) translateY(0px); }
            50% { transform: rotate(-3deg) translateY(-15px) translateX(10px); }
            100% { transform: rotate(-4deg) translateY(0px); }
        }

        /* Responsive */
        @media (max-width: 991px) { /* Tablet */
            main { padding-top: calc(2.5rem + 30px); } /* Rough adjustment */
            .menu-toggle { display: inline-flex; }
            .main-nav {
                /* ... (Mobile nav styles from before, ensure .is-open class used by JS toggles display) ... */
                display: none; /* Default for mobile */
                /* Add these to style mobile menu */
                flex-direction: column; position: absolute; top: calc(100% + 1px); /* Ensure it's just below header */
                left: 0; right: 0; background-color: var(--brand-light);
                box-shadow: var(--shadow-lg); padding: 1rem 0; border-top: 1px solid var(--brand-bg-light);
                max-height: calc(100vh - 70px); overflow-y: auto;
            }
            .main-nav.is-open { display: flex; } /* JS toggles this */

            .hero-grid { grid-template-columns: 1fr; text-align: center; gap: 2rem; }
            .hero-text { order: 2; }
            .hero-slider { order: 1; max-width: 80%; height: 350px; margin: 0 auto 2rem auto; }
            .hero-text h1 { font-size: 2.8rem; }
            .section-heading .section-title { font-size: 2.3rem; }
            .info-grid { grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); }
        }
        @media (max-width: 576px) { /* Mobile */
            main { padding-top: calc(2rem + 28px); }
            .hero-text h1 { font-size: 2.1rem; }
            .section-heading .section-title { font-size: 1.9rem; }
            .hero-slider { height: 280px; }
            .info-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <header class="main-header" id="pageHeader">
        <div class="container header-content">
            <a href="{{ url('/') }}" class="logo">WakalaTEL</a>
            <button class="menu-toggle" id="menuToggleBtn" aria-label="Fungua Menyu" aria-expanded="false" aria-controls="mainNav">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
            </button>
            @if (Route::has('login'))
                <nav class="main-nav" id="mainNav">
                    <ul>
                        <li><a href="#hero">Nyumbani</a></li>
                        <li><a href="#features">Sifa Zetu</a></li>
                        <li><a href="#howitworks">Jinsi Inavyofanya Kazi</a></li>
                    @auth
                        <li><a href="{{ url(config('filament.home_url', '/admin')) }}">Dashibodi</a></li>
                    @else
                        <li><a href="{{ route('filament.admin.auth.login') }}">Ingia</a></li>
                        @if (Route::has('register'))
                            <li><a href="{{ route('register') }}" class="nav-button">Anza Bure Sasa</a></li>
                        @endif
                    @endauth
                    </ul>
                </nav>
            @endif
        </div>
    </header>

    <main>
        <section class="hero-section" id="hero">
            <div class="container">
                <div class="hero-grid">
                    <div class="hero-text">
                        <span class="eyebrow">Teknolojia kwa Wakala Imara</span>
                        <h1>Badilisha Biashara Yako ya <span class="highlight">Uwakala</span> kuwa ya Kisasa na <span class="highlight">WakalaTEL</span>!</h1>
                        <p class="subtitle">
                            Pata udhibiti kamili wa miamala, fuatilia float kiurahisi, na utengeneze ripoti za faida kwa haraka.
                            WakalaTEL ni msaidizi wako mkuu kuelekea mafanikio.
                        </p>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="cta-button">Jaribu Bila Malipo Leo</a>
                        @else
                             <a href="{{ url(config('filament.home_url', '/admin')) }}" class="cta-button">Nenda Kwenye Dashibodi</a>
                        @endif
                    </div>
                    <div class="hero-slider">
                        <div class="slide"></div>
                        <div class="slide"></div>
                        <div class="slide"></div>
                        <div class="slide"></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="content-section section-bg-alt" id="features">
            <div class="container">
                <div class="section-heading">
                    <span class="eyebrow">Nguvu ya Kidijitali</span>
                    <h2 class="section-title">Vipengele Vya Kipekee Vitakavyokuinua</h2>
                    <p class="section-subtitle">
                        Tumeweka pamoja zana zote muhimu unazohitaji ili kurahisisha kazi zako za kila siku na kukuza biashara yako ya uwakala.
                    </p>
                </div>
                <div class="info-grid">
                    <div class="info-card">
                        <div class="info-icon"><img src="{{ asset('images/icons/icon_realtime_sync.svg') }}" alt="Usawazishaji"></div> {{-- REPLACE --}}
                        <h3>Usawazishaji Papo Hapo</h3>
                        <p>Unganisha simu yako na upate taarifa za miamala yote moja kwa moja kwenye mfumo bila kuchelewa.</p>
                    </div>
                    <div class="info-card">
                        <div class="info-icon"><img src="{{ asset('images/icons/icon_analytics.svg') }}" alt="Ripoti"></div> {{-- REPLACE --}}
                        <h3>Ripoti za Kina za Biashara</h3>
                        <p>Fahamu faida, kamisheni, na mienendo ya pesa zako kupitia ripoti zenye uchambuzi wa kina na rahisi kuelewa.</p>
                    </div>
                    <div class="info-card">
                        <div class="info-icon"><img src="{{ asset('images/icons/icon_float.svg') }}" alt="Float"></div> {{-- REPLACE --}}
                        <h3>Usimamizi Mahiri wa Float</h3>
                        <p>Jua salio la float kwa kila mtandao (Airtel, Halotel, n.k.) kwa wakati halisi na upange vizuri mtaji wako.</p>
                    </div>
                     <div class="info-card">
                        <div class="info-icon"><img src="{{ asset('images/icons/icon_multi_agent_shop.svg') }}" alt="Maduka Mengi"></div> {{-- REPLACE --}}
                        <h3>Huduma kwa Maduka/Wakala Wengi</h3>
                        <p>Kama una zaidi ya duka moja au wahudumu tofauti, simamia utendaji wao wote kupitia akaunti moja kuu.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="content-section" id="howitworks">
            <div class="container">
                 <div class="section-heading">
                    <span class="eyebrow">Rahisi na Haraka</span>
                    <h2 class="section-title">Anza Kutumia WakalaTEL Katika Hatua Chache</h2>
                </div>
                <div class="how-it-works-content">
                    <div class="how-it-works-text steps-list">
                        <ol>
                            <li><div><strong>Fungua Akaunti Yako:</strong> Mchakato rahisi wa kujisajili mtandaoni unaochukua dakika chache tu.</div></li>
                            <li><div><strong>Pakua Programu ya Simu:</strong> Weka app yetu ya Android kwenye simu unayotumia kwa miamala ya uwakala.</div></li>
                            <li><div><strong>Ruhusu Usomaji SMS:</strong> Programu itasoma SMS zako za miamala kiusalama na kuzituma kwenye mfumo wako wa WakalaTEL.</div></li>
                            <li><div><strong>Tazama na Uchambue:</strong> Ingia kwenye dashibodi yako ya wavuti kuona miamala, salio la float, na ripoti za kina za biashara.</div></li>
                        </ol>
                    </div>
                    <div class="how-it-works-image">
                        <img src="{{ asset('images/mno/wakala-shops.jpg') }}" alt="Dashibodi ya WakalaTEL inayoonyesha urahisi wa matumizi">
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="main-footer">
        <div class="container">
            <p>© {{ date('Y') }} WakalaTEL. Imebuniwa Kurahisisha Uwakala.</p>
            <div class="footer-links">
                {{-- <a href="#">Sera ya Faragha</a>
                <a href="#">Vigezo na Masharti</a>
                <a href="#">Msaada</a> --}}
            </div>
        </div>
    </footer>

    <script>
        const pageHeaderEl = document.getElementById('pageHeader');
        let lastScrollPosition = 0;
        let scrollTimeout;

        if (pageHeaderEl) {
            window.addEventListener('scroll', function() {
                let currentScroll = window.pageYOffset || document.documentElement.scrollTop;
                if (currentScroll <= 50) {
                    pageHeaderEl.classList.remove('scrolled');
                    pageHeaderEl.style.transform = 'translateY(0px)';
                } else {
                    pageHeaderEl.classList.add('scrolled');
                    if (currentScroll > lastScrollPosition) { // Scrolling Down
                        pageHeaderEl.style.transform = 'translateY(-100%)';
                    } else { // Scrolling Up
                        pageHeaderEl.style.transform = 'translateY(0px)';
                    }
                }
                lastScrollPosition = currentScroll <= 0 ? 0 : currentScroll;
            }, false);
        }

        const menuToggleBtnEl = document.getElementById('menuToggleBtn');
        const mainNavEl = document.getElementById('mainNav');
        if (menuToggleBtnEl && mainNavEl) {
            menuToggleBtnEl.addEventListener('click', function() {
                const isOpen = mainNavEl.classList.toggle('is-open');
                menuToggleBtnEl.setAttribute('aria-expanded', isOpen.toString());
                // The CSS primarily handles display: none/flex with .is-open
                // Ensure your CSS for .main-nav and .main-nav.is-open is correct
            });
        }
    </script>
</body>
</html>
