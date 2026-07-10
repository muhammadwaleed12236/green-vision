<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - {{ \App\Models\Setting::get('company_name', 'Green Vision') }} Software</title>
    <!-- Google Fonts (Poppins) -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* ── Reset & Base ── */
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            width: 100%;
            overflow: hidden;
            font-family: 'Poppins', sans-serif;
        }

        /* ── 60% White — used as base text color on dark bg ── */
        /* ── 30% Dark (#1a1a2e) — hero overlay & card accents ── */
        /* ── 10% Green (#1c9262) — CTA buttons, accents ── */

        /* ── Full-Screen Hero ── */
        .hero {
            position: relative;
            width: 100%;
            height: 100vh;
            background: url('background.jpg') no-repeat center center;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* ── Dark Overlay ── */
        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(160deg, rgba(26, 26, 46, 0.85) 0%, rgba(15, 15, 15, 0.75) 50%, rgba(28, 146, 98, 0.25) 100%);
            z-index: 1;
        }

        /* ── Animated Background Particles (decorative) ── */
        .hero::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                radial-gradient(2px 2px at 20% 30%, rgba(255,255,255,0.15) 0%, transparent 100%),
                radial-gradient(2px 2px at 40% 70%, rgba(255,255,255,0.1) 0%, transparent 100%),
                radial-gradient(2px 2px at 60% 20%, rgba(255,255,255,0.12) 0%, transparent 100%),
                radial-gradient(2px 2px at 80% 50%, rgba(255,255,255,0.08) 0%, transparent 100%),
                radial-gradient(3px 3px at 10% 80%, rgba(28,146,98,0.2) 0%, transparent 100%),
                radial-gradient(3px 3px at 90% 10%, rgba(28,146,98,0.15) 0%, transparent 100%);
            z-index: 1;
            animation: floatParticles 20s ease-in-out infinite alternate;
        }

        @keyframes floatParticles {
            0%   { transform: translateY(0) scale(1); }
            100% { transform: translateY(-15px) scale(1.03); }
        }

        /* ── Glassmorphism Card ── */
        .glass-card {
            position: relative;
            z-index: 2;
            background: rgba(255, 255, 255, 0.07);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 24px;
            padding: 60px 50px;
            text-align: center;
            max-width: 560px;
            width: 90%;
            box-shadow:
                0 8px 32px rgba(0, 0, 0, 0.35),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            animation: cardEntrance 1s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
            transform: translateY(40px) scale(0.96);
        }

        @keyframes cardEntrance {
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* ── Glowing border accent ── */
        .glass-card::before {
            content: '';
            position: absolute;
            top: -1px;
            left: 50%;
            transform: translateX(-50%);
            width: 60%;
            height: 2px;
            background: linear-gradient(90deg, transparent, #1c9262, transparent);
            border-radius: 2px;
        }

        /* ── Logo ── */
        .logo {
            width: 130px;
            height: auto;
            margin-bottom: 28px;
            filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.3));
            animation: logoFloat 3s ease-in-out infinite;
        }

        @keyframes logoFloat {
            0%, 100% { transform: translateY(0); }
            50%      { transform: translateY(-8px); }
        }

        /* ── Title ── */
        .title {
            font-size: 2.4rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 12px;
            letter-spacing: -0.5px;
            line-height: 1.2;
        }

        .title-accent {
            color: #1c9262;
        }

        /* ── Subtitle ── */
        .subtitle {
            font-size: 1rem;
            font-weight: 300;
            color: rgba(255, 255, 255, 0.65);
            margin-bottom: 40px;
            line-height: 1.6;
        }

        /* ── Divider ── */
        .divider {
            width: 50px;
            height: 3px;
            background: #1c9262;
            border-radius: 3px;
            margin: 0 auto 32px auto;
        }

        /* ── CTA Button — 10% Green accent ── */
        .btn-cta {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 16px 40px;
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #ffffff;
            background: linear-gradient(135deg, #1c9262 0%, #15785a 100%);
            border: none;
            border-radius: 50px;
            text-decoration: none;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            box-shadow: 0 4px 15px rgba(28, 146, 98, 0.35);
        }

        .btn-cta:hover {
            transform: translateY(-3px) scale(1.03);
            box-shadow: 0 8px 30px rgba(28, 146, 98, 0.5);
            background: linear-gradient(135deg, #20a76f 0%, #1c9262 100%);
        }

        .btn-cta:active {
            transform: translateY(-1px) scale(1.01);
            box-shadow: 0 4px 12px rgba(28, 146, 98, 0.3);
        }

        /* ── Button Shine Effect ── */
        .btn-cta::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.6s ease;
        }

        .btn-cta:hover::after {
            left: 100%;
        }

        /* ── Button Arrow Icon ── */
        .btn-arrow {
            display: inline-block;
            transition: transform 0.3s ease;
        }

        .btn-cta:hover .btn-arrow {
            transform: translateX(4px);
        }

        /* ── Footer Text ── */
        .footer-text {
            position: absolute;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 2;
            font-size: 0.8rem;
            font-weight: 300;
            color: rgba(255, 255, 255, 0.35);
            letter-spacing: 0.5px;
        }

        /* ── Responsive ── */
        @media (max-width: 600px) {
            .glass-card {
                padding: 40px 28px;
                border-radius: 18px;
            }

            .title {
                font-size: 1.7rem;
            }

            .subtitle {
                font-size: 0.9rem;
            }

            .logo {
                width: 100px;
            }

            .btn-cta {
                padding: 14px 32px;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 400px) {
            .title {
                font-size: 1.4rem;
            }

            .glass-card {
                padding: 32px 20px;
            }
        }
    </style>
</head>

<body>

    <!-- Hero Section -->
    <section class="hero">

        <!-- Glassmorphism Content Card -->
        <div class="glass-card">
            <!-- Logo -->
            @if(isset($appSettings['company_logo']) && $appSettings['company_logo'])
                <img src="{{ asset('storage/' . $appSettings['company_logo']) }}" alt="Logo" class="logo">
            @else
                <img src="welcome-logo.png" alt="Logo" class="logo">
            @endif

            <!-- Title -->
            <h1 class="title">
                Welcome to <span class="title-accent">{{ \App\Models\Setting::get('company_name', 'Green Vision') }}</span> Software
            </h1>

            <!-- Divider -->
            <div class="divider"></div>

            <!-- Subtitle -->
            <p class="subtitle">
                Empowering smarter decisions through innovative technology solutions.
            </p>

            <!-- CTA Buttons with Blade Auth Logic -->
            @if (Route::has('login'))
                @auth
                    <a href="{{ url('/home') }}" class="btn-cta">
                        Dashboard
                        <span class="btn-arrow">→</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn-cta">
                        Login
                        <span class="btn-arrow">→</span>
                    </a>
                @endauth
            @endif
        </div>

        <!-- Footer -->
        <p class="footer-text">© {{ date('Y') }} {{ \App\Models\Setting::get('company_name', 'Green Vision') }} Software. All rights reserved.</p>

    </section>

</body>

</html>
