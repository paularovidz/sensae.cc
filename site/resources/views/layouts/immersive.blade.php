<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    @yield('meta')
    <style>
        @font-face { font-family:'Satoshi'; src:url('/fonts/satoshi-regular.woff2') format('woff2'); font-weight:400; font-display:swap; }
        @font-face { font-family:'Satoshi'; src:url('/fonts/satoshi-medium.woff2') format('woff2'); font-weight:500; font-display:swap; }
        @font-face { font-family:'Amandine'; src:url('/fonts/amandine.woff2') format('woff2'); font-weight:400; font-display:swap; }

        *,*::before,*::after { margin:0; padding:0; box-sizing:border-box; }

        :root {
            --ps-bg: #050208;
            --ps-violet-deep: #3a0e6e;
            --ps-violet: #721ad6;
            --ps-rose: #d299ff;
            --ps-teal: #1a6b7a;
            --ps-warm: #fff0e0;
            --ps-white: #fffaf5;
            --ps-gold: #ffd699;
        }

        html { background: var(--ps-bg); }
        body {
            background: var(--ps-bg);
            color: var(--ps-white);
            font-family: 'Satoshi', system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }

        /* --- Sections --- */
        .ps-section {
            position: relative;
            width: 100%;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .ps-section-inner {
            position: relative;
            z-index: 5;
            max-width: 800px;
            width: 100%;
            padding: 0 2rem;
            text-align: center;
        }

        /* --- Numéros décoratifs --- */
        .ps-num {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-family: 'Amandine', serif;
            font-size: clamp(8rem, 20vw, 16rem);
            font-weight: 400;
            color: rgba(114, 26, 214, 0.06);
            pointer-events: none;
            z-index: 1;
            opacity: 0;
        }

        /* --- Typographie --- */
        .ps-title {
            font-family: 'Amandine', serif;
            font-size: clamp(1.8rem, 5vw, 3.2rem);
            font-weight: 400;
            letter-spacing: 0.02em;
            line-height: 1.2;
            margin-bottom: 1.5rem;
            opacity: 0;
        }

        .ps-subtitle {
            font-size: clamp(0.95rem, 2.5vw, 1.15rem);
            font-weight: 400;
            letter-spacing: 0.04em;
            color: rgba(255, 250, 245, 0.55);
            line-height: 1.7;
            opacity: 0;
        }

        .ps-line {
            font-size: clamp(0.9rem, 2vw, 1.05rem);
            letter-spacing: 0.03em;
            color: rgba(255, 250, 245, 0.65);
            line-height: 1.8;
            margin-bottom: 0.75rem;
            opacity: 0;
        }

        .ps-line strong {
            color: var(--ps-white);
            font-weight: 500;
        }

        .ps-small {
            font-size: clamp(0.75rem, 1.5vw, 0.85rem);
            color: rgba(255, 250, 245, 0.3);
            letter-spacing: 0.05em;
            margin-top: 1rem;
            opacity: 0;
        }

        /* --- Orbes (background visuals) --- */
        .ps-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            mix-blend-mode: screen;
            pointer-events: none;
            z-index: 2;
            opacity: 0;
            will-change: transform;
        }

        /* --- Particules --- */
        .ps-particle {
            position: fixed;
            border-radius: 50%;
            pointer-events: none;
            z-index: 3;
            opacity: 0;
            will-change: transform;
        }

        /* --- Scroll indicator --- */
        .ps-scroll-hint {
            position: absolute;
            bottom: 5vh;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            opacity: 0;
            z-index: 10;
        }
        .ps-scroll-hint span {
            font-size: 0.7rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: rgba(255, 250, 245, 0.3);
        }
        .ps-scroll-chevron {
            width: 20px;
            height: 20px;
            border-right: 1.5px solid rgba(255, 250, 245, 0.3);
            border-bottom: 1.5px solid rgba(255, 250, 245, 0.3);
            transform: rotate(45deg);
            animation: ps-bounce 2s ease-in-out infinite;
        }
        @keyframes ps-bounce {
            0%, 100% { transform: rotate(45deg) translateY(0); opacity: 0.6; }
            50% { transform: rotate(45deg) translateY(6px); opacity: 1; }
        }

        /* --- Visual elements (Section 2) --- */
        .ps-visual {
            position: absolute;
            pointer-events: none;
            z-index: 3;
            opacity: 0;
        }

        .ps-bubble-col {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }
        .ps-bubble-dot {
            border-radius: 50%;
            background: radial-gradient(circle at 30% 30%, rgba(210, 153, 255, 0.5), rgba(114, 26, 214, 0.15));
            animation: ps-float 4s ease-in-out infinite;
        }
        @keyframes ps-float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .ps-fiber {
            width: 1px;
            background: linear-gradient(to bottom, transparent, var(--ps-rose), transparent);
            opacity: 0.4;
        }

        .ps-wave-ring {
            border-radius: 50%;
            border: 1px solid rgba(114, 26, 214, 0.2);
            animation: ps-wave-expand 3s ease-out infinite;
        }
        @keyframes ps-wave-expand {
            0% { transform: scale(0.5); opacity: 0.6; }
            100% { transform: scale(1.5); opacity: 0; }
        }

        .ps-visual-label {
            font-size: 0.7rem;
            letter-spacing: 0.08em;
            color: rgba(255, 250, 245, 0.35);
            text-align: center;
            margin-top: 8px;
        }

        /* --- Dual circles (Section 3) --- */
        .ps-duo {
            display: flex;
            gap: clamp(1.5rem, 4vw, 3rem);
            justify-content: center;
            flex-wrap: wrap;
            margin: 2rem 0;
        }
        .ps-duo-card {
            width: clamp(160px, 35vw, 280px);
            aspect-ratio: 1;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            text-align: center;
            opacity: 0;
            transform: scale(0.6);
        }
        .ps-duo-card h3 {
            font-family: 'Amandine', serif;
            font-size: clamp(1rem, 2.5vw, 1.3rem);
            font-weight: 400;
            margin-bottom: 0.5rem;
            color: var(--ps-white);
        }
        .ps-duo-card p {
            font-size: clamp(0.7rem, 1.5vw, 0.8rem);
            color: rgba(255, 250, 245, 0.6);
            line-height: 1.5;
        }

        /* --- Breathing pulse (Section 4) --- */
        .ps-pulse {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 1px solid rgba(114, 26, 214, 0.3);
            box-shadow: 0 0 60px rgba(114, 26, 214, 0.15), inset 0 0 30px rgba(114, 26, 214, 0.08);
            z-index: 2;
            opacity: 0;
        }
        .ps-pulse-outer {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 200px;
            height: 200px;
            border-radius: 50%;
            border: 1px solid rgba(114, 26, 214, 0.15);
            z-index: 2;
            opacity: 0;
        }

        /* --- Pills (Section 5) --- */
        .ps-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
            margin: 1.5rem 0;
        }
        .ps-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 18px;
            border-radius: 999px;
            border: 1px solid rgba(255, 214, 153, 0.2);
            background: rgba(255, 214, 153, 0.05);
            font-size: 0.8rem;
            letter-spacing: 0.04em;
            color: rgba(255, 240, 224, 0.8);
            opacity: 0;
            transform: translateY(10px);
        }
        .ps-pill-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--ps-gold);
            flex-shrink: 0;
        }

        /* --- CTA --- */
        .ps-cta {
            margin-top: 2rem;
            opacity: 0;
        }
        .ps-cta a {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 14px 32px;
            border-radius: 999px;
            background: var(--ps-violet);
            color: var(--ps-white);
            text-decoration: none;
            font-size: clamp(0.9rem, 2.5vw, 1.05rem);
            font-weight: 500;
            letter-spacing: 0.04em;
            transition: background 0.3s, transform 0.3s;
        }
        .ps-cta a:hover {
            background: var(--ps-violet-deep);
            transform: scale(1.03);
        }
        .ps-cta-star {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: rgba(255,255,255,0.15);
        }
        .ps-cta-star svg { animation: ps-spin 10s linear infinite; }
        @keyframes ps-spin { to { transform: rotate(360deg); } }

        /* --- Home link --- */
        .ps-home {
            position: fixed;
            top: 24px;
            left: 24px;
            z-index: 50;
            font-family: 'Amandine', serif;
            font-size: 1.2rem;
            color: rgba(255, 250, 245, 0.4);
            text-decoration: none;
            letter-spacing: 0.04em;
            transition: color 0.3s;
        }
        .ps-home:hover { color: var(--ps-white); }

        /* --- Right-side scroll indicator --- */
        .ps-nav {
            position: fixed;
            right: 24px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 50;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0;
            opacity: 0;
            transition: opacity 0.6s;
        }
        .ps-nav.is-visible { opacity: 1; }
        .ps-nav-track {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0;
        }
        .ps-nav-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: rgba(255, 250, 245, 0.15);
            transition: background 0.4s, transform 0.4s, box-shadow 0.4s;
            flex-shrink: 0;
        }
        .ps-nav-dot.is-active {
            background: rgba(210, 153, 255, 0.7);
            transform: scale(1.5);
            box-shadow: 0 0 10px rgba(210, 153, 255, 0.3);
        }
        .ps-nav-line {
            width: 1px;
            height: 16px;
            background: rgba(255, 250, 245, 0.08);
            flex-shrink: 0;
        }
        .ps-nav-label {
            margin-top: 16px;
            writing-mode: vertical-rl;
            font-size: 0.6rem;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: rgba(255, 250, 245, 0.2);
            animation: ps-nav-pulse 3s ease-in-out infinite;
        }
        @keyframes ps-nav-pulse {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 1; }
        }
        @media (max-width: 640px) {
            .ps-nav { right: 12px; }
            .ps-nav-label { display: none; }
        }

        /* --- Reduced motion --- */
        @media (prefers-reduced-motion: reduce) {
            .ps-section { height: auto; min-height: 60vh; padding: 4rem 0; }
            .ps-num, .ps-title, .ps-subtitle, .ps-line, .ps-small,
            .ps-duo-card, .ps-pill, .ps-cta, .ps-scroll-hint {
                opacity: 1 !important;
                transform: none !important;
                visibility: visible !important;
            }
            .ps-particle, .ps-orb { display: none !important; }
            .ps-pulse, .ps-pulse-outer { display: none !important; }
            .ps-scroll-hint { display: none !important; }
            .ps-nav { display: none !important; }
            @keyframes ps-bounce { 0%,100% { transform: none; } }
            @keyframes ps-float { 0%,100% { transform: none; } }
            @keyframes ps-wave-expand { 0%,100% { transform: none; opacity: 0.3; } }
        }
    </style>
</head>
<body>

    <a href="/" class="ps-home">sensëa</a>

    {{-- Right-side scroll indicator --}}
    <nav class="ps-nav" aria-hidden="true">
        <div class="ps-nav-track">
            <div class="ps-nav-dot" data-nav="0"></div>
            <div class="ps-nav-line"></div>
            <div class="ps-nav-dot" data-nav="1"></div>
            <div class="ps-nav-line"></div>
            <div class="ps-nav-dot" data-nav="2"></div>
            <div class="ps-nav-line"></div>
            <div class="ps-nav-dot" data-nav="3"></div>
            <div class="ps-nav-line"></div>
            <div class="ps-nav-dot" data-nav="4"></div>
            <div class="ps-nav-line"></div>
            <div class="ps-nav-dot" data-nav="5"></div>
            <div class="ps-nav-line"></div>
            <div class="ps-nav-dot" data-nav="6"></div>
        </div>
        <span class="ps-nav-label">Scrollez</span>
    </nav>

    @yield('sections')

    <script src="/js/gsap.min.js"></script>
    <script src="/js/ScrollTrigger.min.js"></script>
    <script src="/js/premiere-seance.js"></script>
</body>
</html>
