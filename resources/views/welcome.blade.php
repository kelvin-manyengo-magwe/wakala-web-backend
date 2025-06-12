<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Wakala') }} - Usimamizi BORA wa Miamala ya wakala</title> {{-- Changed title slightly --}}

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />

    {{-- Placeholder for Favicon --}}
    {{-- <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon"> --}}

    <style>
        :root {
            --brand-primary: #D90429; /* A strong, confident Red - Adjust to your brand */
            --brand-secondary: #EF233C; /* A slightly lighter, vibrant Red */
            --brand-dark: #2B2D42;    /* Darker text, less harsh than pure black */
            --brand-light: #FFFFFF;
            --brand-accent: #8D99AE;  /* A cool gray for accents or secondary text */
            --brand-background: #EDF2F4; /* Very light gray for body background */
            --font-family-main: 'Instrument Sans', sans-serif;
            --box-shadow-light: 0 4px 15px rgba(0,0,0,0.07);
            --box-shadow-medium: 0 8px 25px rgba(0,0,0,0.1);
            --border-radius-main: 8px;
        }

        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: var(--font-family-main);
            background-color: var(--brand-background);
            color: var(--brand-dark);
            line-height: 1.7;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            font-size: 16px; /* Base font size */
        }

        .container {
            width: 90%;
            max-width: 1160px; /* Slightly wider max-width */
            margin: 0 auto;
            padding: 0 15px;
        }

        /* Header */
        .main-header {
            padding: 1rem 0;
            background-color: var(--brand-light);
            position: fixed; /* Fixed header */
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            box-shadow: var(--box-shadow-light);
        }
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo a {
            font-size: 2rem; /* Increased size */
            font-weight: 700;
            color: var(--brand-primary);
            text-decoration: none;
            letter-spacing: -1px;
        }
        .main-nav ul { list-style: none; padding: 0; margin: 0; display: flex; align-items: center; gap: 0.5rem; }
        .main-nav a {
            color: var(--brand-dark); font-size: 0.95rem; font-weight: 500; text-decoration: none;
            padding: 0.6rem 1rem; border-radius: var(--border-radius-main); transition: all 0.2s ease;
        }
        .main-nav a:hover { background-color: var(--brand-background); color: var(--brand-primary); }
        .main-nav a.nav-button {
            background-color: var(--brand-primary); color: var(--brand-light) !important;
            box-shadow: 0 2px 8px rgba(217, 4, 41, 0.3);
        }
        .main-nav a.nav-button:hover { background-color: #B70322; /* Darker primary */ }
        .menu-toggle { display: none; font-size: 1.8rem; cursor: pointer; background: none; border: none; color: var(--brand-dark); padding: 0; }

        /* Main content needs top padding due to fixed header */
        main {
            padding-top: 90px; /* Adjust based on actual header height */
        }

        /* Hero Section */
        .hero-section {
            padding: 80px 0 100px 0; /* More bottom padding */
            background: linear-gradient(135deg, var(--brand-light) 60%, var(--brand-background) 100%);
            overflow: hidden; /* Contain decorative elements */
        }
        .hero-grid { display: grid; grid-template-columns: repeat(2, 1fr); align-items: center; gap: 3rem; }
        .hero-text { animation: fadeInFromLeft 1s ease-out forwards; }
        .hero-text h1 {
            font-size: 3.2rem; font-weight: 700; line-height: 1.25; color: var(--brand-dark);
            margin-bottom: 1.5rem;
        }
        .hero-text h1 .highlight { color: var(--brand-primary); }
        .hero-text p.subtitle {
            font-size: 1.2rem; color: var(--brand-accent); margin-bottom: 2rem; max-width: 520px;
        }
        .cta-button {
            background-color: var(--brand-primary); color: var(--brand-light); padding: 1rem 2.2rem;
            font-size: 1.1rem; font-weight: 600; text-decoration: none; border-radius: var(--border-radius-main);
            transition: all 0.3s ease; display: inline-block; box-shadow: 0 5px 20px rgba(217, 4, 41, 0.35);
        }
        .cta-button:hover { background-color: #B70322; transform: translateY(-4px); box-shadow: 0 8px 25px rgba(217, 4, 41, 0.45); }

        /* Image Slider */
        .hero-slider {
            width: 100%; max-width: 550px; height: 400px; /* Adjust height */
            position: relative; overflow: hidden; border-radius: 12px;
            box-shadow: var(--box-shadow-medium);
            animation: fadeInFromRight 1s ease-out 0.3s forwards; /* Delayed animation */
            opacity: 0; /* Start hidden for animation */
        }
        .slide {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0;
            animation: crossfadeEffect 28s infinite ease-in-out; /* 7s per image * 4 images */
            background-size: cover; background-position: center center; /* Ensure image is centered */
        }
        .slide:nth-child(1) { animation-delay: 0s;  background-image: url("{{ asset('images/mno/wakala-shops1.webp') }}"); }
        .slide:nth-child(2) { animation-delay: 7s;  background-image: url("{{ asset('images/mno/wakala-shops2.jpg') }}"); }
        .slide:nth-child(3) { animation-delay: 14s; background-image: url("{{ asset('images/mno/wakala-shops5.jpg') }}"); }
        .slide:nth-child(4) { animation-delay: 21s; background-image: url("{{ asset('images/mno/wakala-shops6.jpg') }}"); }

        /* Section Styling */
        .content-section { padding: 80px 0; }
        .content-section.bg-light { background-color: var(--brand-light); }
        .section-title {
            text-align: center; font-size: 2.5rem; font-weight: 700;
            color: var(--brand-dark); margin-bottom: 1.5rem; position: relative;
        }
        .section-subtitle {
            text-align: center; font-size: 1.15rem; color: var(--brand-gray);
            max-width: 650px; margin: 0 auto 60px auto;
        }

        /* Features Grid */
        .features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2.5rem; }
        .feature-card {
            background-color: var(--brand-light); padding: 2.5rem 2rem; border-radius: var(--border-radius-main);
            box-shadow: var(--box-shadow-light); text-align: center; transition: all 0.3s ease;
        }
        .feature-card:hover { transform: translateY(-8px); box-shadow: var(--box-shadow-medium); }
        .feature-icon img { /* Expecting <img> tag for icons now */
            height: 50px; width: auto; margin-bottom: 1.5rem;
        }
        .feature-card h3 {
            font-size: 1.5rem; font-weight: 600; color: var(--brand-dark); margin-bottom: 0.8rem;
        }
        .feature-card p { font-size: 0.95rem; color: var(--brand-gray); line-height: 1.6; }

        /* How It Works Section */
        .how-it-works-content { display: flex; align-items: center; gap: 3rem; flex-wrap: wrap; }
        .how-it-works-text { flex: 1; min-width: 300px; }
        .how-it-works-image { flex: 1; text-align: center; min-width: 300px; }
        .how-it-works-image img { max-width: 100%; height: auto; border-radius: var(--border-radius-main); box-shadow: var(--box-shadow-medium); }
        .how-it-works-section ol { list-style: none; padding-left: 0; counter-reset: step-counter; }
        .how-it-works-section li {
            font-size: 1.1rem; margin-bottom: 1.5rem; padding-left: 45px; position: relative;
        }
        .how-it-works-section li::before {
            content: counter(step-counter); counter-increment: step-counter;
            position: absolute; left: 0; top: -2px; /* Adjust top for alignment */
            color: var(--brand-light); background-color: var(--brand-primary);
            width: 30px; height: 30px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center; /* Center number */
            font-weight: bold; font-size: 1rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .how-it-works-section li strong { font-weight: 600; color: var(--brand-dark); display: block; margin-bottom: 0.25rem; }

        /* Footer */
        .main-footer {
            background-color: var(--brand-dark); color: #A9B4C2; /* Lighter gray for footer text */
            text-align: center; padding: 50px 0;
        }
        .main-footer p { margin: 0; font-size: 0.9rem; }
        .main-footer a { color: var(--brand-secondary); text-decoration: none; }
        .main-footer a:hover { text-decoration: underline; }

        /* Animations Keyframes */
        @keyframes crossfadeEffect {
            0%, 25%, 100% { opacity: 0; } /* Each image shown for 20% of total (5% in, 10% visible, 5% out) */
            3.57%  { opacity: 1; } /* (1s / 28s) * 100 - Fade in */
            21.42% { opacity: 1; } /* ( (1s+5s) / 28s ) * 100 - Visible duration */
        }
        @keyframes fadeInFromLeft { from { opacity: 0; transform: translateX(-40px); } to { opacity: 1; transform: translateX(0); } }
        @keyframes fadeInFromRight { from { opacity: 0; transform: translateX(40px); } to { opacity: 1; transform: translateX(0); } }

        /* Responsive Adjustments */
        @media (max-width: 991px) { /* Tablet and below */
            .main-header { padding: 0.8rem 0; }
            main { padding-top: 70px; } /* Adjust based on new header height */
            .menu-toggle { display: inline-flex; align-items:center; } /* Use inline-flex for better alignment */
            .main-nav {
                display: none; flex-direction: column; position: absolute;
                top: 100%; left: 0; width: 100%; background-color: var(--brand-light);
                box-shadow: 0 5px 10px rgba(0,0,0,0.1); padding: 1rem 0;
            }
            .main-nav.is-open { display: flex; }
            .main-nav ul { flex-direction: column; width: 100%; }
            .main-nav ul li { width: 100%; text-align: center; }
            .main-nav ul li a { display: block; padding: 0.8rem 1rem; width: 100%; border-radius: 0;}

            .hero-grid { grid-template-columns: 1fr; text-align: center; }
            .hero-text { margin-bottom: 3rem; animation-name:none; } /* Remove slide-in for mobile on text if too jumpy*/
            .hero-slider { max-width: 90%; height: 300px; margin: 0 auto; animation-name:none; opacity:1; /* Remove slide-in */ }
            .hero-text h1 { font-size: 2.5rem; }
            .hero-text p.subtitle { font-size: 1.1rem; max-width: 100%; }
            .how-it-works-content { flex-direction: column-reverse; }
            .how-it-works-image { margin-bottom: 2rem; }
        }
        @media (max-width: 575px) { /* Small mobile */
            .hero-text h1 { font-size: 2rem; }
            .section-title { font-size: 1.8rem; }
            .hero-slider { height: 220px; }
            .cta-button { padding: 0.8rem 1.8rem; font-size: 0.95rem; }
            .feature-card { padding: 1.5rem; }
            .feature-card h3 {font-size: 1.2rem;}
        }
    </style>
</head>
<body>
    <header class="main-header" id="pageHeader">
        <div class="container header-content">
            <a href="{{ url('/') }}" class="logo">Wakala</a> {{-- Using App Name --}}

            <button class="menu-toggle" id="menuToggleBtn" aria-label="Fungua Menyu" aria-expanded="false" aria-controls="mainNav">
                ☰ {{-- Burger Icon --}}
            </button>

            @if (Route::has('login'))
                <nav class="main-nav" id="mainNav">
                    <ul>
                    {{-- Add Home/Features links if desired --}}
                    {{-- <li><a href="#features">Sifa Muhimu</a></li> --}}
                    {{-- <li><a href="#howitworks">Jinsi Inavyofanya Kazi</a></li> --}}
                    @auth
                        <li><a href="{{ url(config('filament.home_url', '/admin')) }}">Dashibodi</a></li>
                    @else
                        <li><a href="{{ route('filament.admin.auth.login') }}">Ingia</a></li>
                        @if (Route::has('register'))
                            <li><a href="{{ route('filament.admin.auth.login') }}" class="nav-button">Anza Bure</a></li>
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
                        <h1>Imarisha Biashara Yako ya <span class="highlight">Uwakala</span> kwa Teknolojia!</h1>
                        <p class="subtitle">
                            Wakala inakupa zana za kisasa za kufuatilia miamala, kusimamia mapato na matumizi,
                            na kupata ripoti sahihi za kukuza faida yako kama wakala wa fedha.
                        </p>
                        @if (Route::has('register'))
                            <a href="{{ route('filament.admin.auth.login') }}" class="cta-button">Anza Kutumia Leo</a>
                        @else
                            <a href="{{ url(config('filament.home_url', '/admin')) }}" class="cta-button">Nenda Dashibodi</a>
                        @endif
                    </div>
                    <div class="hero-slider">
                        <div class="slide"></div> {{-- Images set by CSS background --}}
                        <div class="slide"></div>
                        <div class="slide"></div>
                        <div class="slide"></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="content-section bg-light" id="features">
            <div class="container">
                <h2 class="section-title">Sifa Muhimu za Wakala</h2>
                <p class="section-subtitle">
                    Gundua jinsi Wakala inavyoweza kubadilisha usimamizi wa biashara yako ya uwakala na kuongeza ufanisi wako.
                </p>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">
                              <img src="https://img.icons8.com/color/96/cash-in-hand.png" width="30" height="30" alt="Send Cash" />

                        </div>
                        <h3>Ufuatiliaji Halisi wa Miamala</h3>
                        <p>Pata taarifa papo hapo kwa kila muamala unaofanyika - iwe kuweka, kutoa, au malipo - kwa mitandao yote mikuu.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                              <img src="https://img.icons8.com/color/96/business-report.png" width="40" />
                        </div>
                        <h3>Ripoti za Kina na Uchambuzi</h3>
                        <p>Fahamu faida yako, kamisheni, na mienendo ya mtiririko wa pesa kupitia ripoti zilizo rahisi kusoma na kuelewa.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                                <img src="https://img.icons8.com/color/96/money-transfer.png" width="40" />
                        </div>
                        <h3>Usimamizi Bora wa Float</h3>
                        <p>Jua salio lako la float kwa kila mtandao wakati wowote, saidia kuepuka usumbufu wa kukosa float.</p>
                    </div>
                     <div class="feature-card">
                        <div class="feature-icon">
                                <img src="https://img.icons8.com/color/96/group.png" width="40" />
                        </div>
                        <h3>Huduma kwa Wakala Wengi</h3>
                        <p>Kama una maduka mengi au wasaidizi, unaweza kusimamia akaunti zao zote kutoka sehemu moja kwa urahisi.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="content-section" id="howitworks">
            <div class="container">
                <h2 class="section-title">Jinsi Wakala Inavyofanya Kazi</h2>
                <div class="how-it-works-content">
                    <div class="how-it-works-text">
                        <ol>
                            <li><strong>Jisajili Chapchap:</strong> Fungua akaunti yako ya Wakala kwa hatua chache na rahisi.</li>
                            <li><strong>Unganisha Simu Yako:</strong> Weka app yetu ya simu na uiruhusu kusoma SMS za miamala kwa usalama.</li>
                            <li><strong>Pata Taarifa Papo Hapo:</strong> Kila SMS ya muamala inapopokelewa, data inaonekana moja kwa moja kwenye dashibodi yako ya Wakala.</li>
                            <li><strong>Chambua na Ukuze:</strong> Tumia ripoti na takwimu zetu kufanya maamuzi bora na kukuza biashara yako ya uwakala.</li>
                        </ol>
                    </div>
                    <div class="how-it-works-image">
                        {{-- Using wakala-shops.jpg as the primary example, other images are for slider --}}
                        <img src="{{ asset('images/mno/wakala-shops.jpg') }}" alt="Dashibodi ya Wakala ikionyesha miamala">
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="main-footer">
        <div class="container">
            <p>© {{ date('Y') }} Wakala. Haki Zote Zimehifadhiwa.</p>
            {{-- <p><a href="#">Sera ya Faragha</a> | <a href="#">Vigezo na Masharti</a></p> --}}
        </div>
    </footer>

    <script>
        // Sticky Header (No change, should work)
        const pageHeader = document.getElementById('pageHeader');
        let lastScrollTop = 0;
        if (pageHeader) {
            window.addEventListener('scroll', function() {
                let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                if (scrollTop > lastScrollTop && scrollTop > 80) { // Scrolling Down
                    pageHeader.style.top = '-100px'; // Hide header
                } else if (scrollTop < lastScrollTop || scrollTop <= 80) { // Scrolling Up or at top
                     pageHeader.style.top = '0'; // Show header
                }
                 if (scrollTop <= 80) { // Always show at top or when very little scroll
                     pageHeader.style.boxShadow = 'none';
                } else {
                     pageHeader.style.boxShadow = 'var(--box-shadow-light)';
                }
                lastScrollTop = scrollTop <= 0 ? 0 : scrollTop; // For Mobile or negative scrolling
            }, false);
        }


        // Mobile Menu Toggle
        const menuToggleBtn = document.getElementById('menuToggleBtn');
        const mainNavElement = document.getElementById('mainNav');
        if (menuToggleBtn && mainNavElement) {
            menuToggleBtn.addEventListener('click', function() {
                const isOpen = mainNavElement.classList.toggle('is-open');
                menuToggleBtn.setAttribute('aria-expanded', isOpen.toString());
                 if(isOpen){
                    mainNavElement.style.display = 'flex'; // If you are using class to hide/show, ensure CSS matches
                 } else {
                    mainNavElement.style.display = 'none';
                 }
            });
        }
    </script>

</body>
</html>
