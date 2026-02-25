<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Voyage Sensoriel — sensaë</title>
    <meta name="description" content="Une expérience immersive pour ressentir les sens depuis votre navigateur. Toucher, son, lumière, respiration.">
    <meta property="og:title" content="Voyage Sensoriel — sensaë">
    <meta property="og:description" content="Explorez vos sens dans une expérience interactive unique.">
    <meta property="og:type" content="website">
    <style>
        @font-face {
            font-family: 'Satoshi';
            src: url('/fonts/Satoshi-Regular.woff2') format('woff2');
            font-weight: 400;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Satoshi';
            src: url('/fonts/Satoshi-Medium.woff2') format('woff2');
            font-weight: 500;
            font-style: normal;
            font-display: swap;
        }

        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --xp-bg: #050208;
            --xp-violet-deep: #3a0e6e;
            --xp-violet: #721ad6;
            --xp-rose: #d299ff;
            --xp-warm: #fff0e0;
            --xp-white: #fffaf5;
            --xp-gold: #ffd699;
        }

        html, body {
            width: 100%;
            height: 100%;
            overflow: hidden;
            background: var(--xp-bg);
            color: var(--xp-white);
            font-family: 'Satoshi', system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
            cursor: none;
            -webkit-user-select: none;
            user-select: none;
        }

        /* --- Canvas (each act) --- */
        .xp-canvas {
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100%;
            display: none;
            z-index: 1;
        }
        .xp-canvas.is-active { display: block; }

        /* --- Typography --- */
        .xp-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: clamp(1.2rem, 4vw, 2.4rem);
            font-weight: 400;
            letter-spacing: 0.04em;
            text-align: center;
            pointer-events: none;
            z-index: 10;
            opacity: 0;
            visibility: hidden;
        }

        .xp-text--small {
            font-size: clamp(0.85rem, 2vw, 1.1rem);
            letter-spacing: 0.06em;
            color: rgba(255, 250, 245, 0.5);
        }

        .xp-text--bottom {
            top: auto;
            bottom: 12vh;
        }

        /* --- Halo (Act 1) --- */
        .xp-halo {
            position: fixed;
            width: 280px;
            height: 280px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(114, 26, 214, 0.35) 0%, transparent 70%);
            pointer-events: none;
            transform: translate(-50%, -50%);
            z-index: 2;
            opacity: 0;
            visibility: hidden;
            will-change: transform;
        }

        /* --- Particles (shared pool) --- */
        .xp-particle {
            position: fixed;
            border-radius: 50%;
            pointer-events: none;
            z-index: 3;
            opacity: 0;
            visibility: hidden;
            will-change: transform;
        }

        /* --- Fluid orbs (Act 2) --- */
        .xp-fluid-orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(40px);
            mix-blend-mode: screen;
            pointer-events: none;
            z-index: 3;
            opacity: 0;
            will-change: transform;
        }

        /* --- Ripples (Act 6) --- */
        .xp-ripple {
            position: fixed;
            border-radius: 50%;
            border: none;
            pointer-events: none;
            z-index: 3;
            transform: translate(-50%, -50%);
            will-change: transform, opacity;
        }

        /* --- Sound hint (Act 1) --- */
        .xp-sound-hint {
            position: fixed;
            top: 24px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 0.75rem;
            letter-spacing: 0.06em;
            color: rgba(255, 250, 245, 0.3);
            z-index: 10;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        /* --- Ambient glow (Act 6) --- */
        .xp-ambient-glow {
            position: fixed;
            width: 400px;
            height: 400px;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            border-radius: 50%;
            background: radial-gradient(circle, rgba(114, 26, 214, 0.08) 0%, transparent 70%);
            pointer-events: none;
            z-index: 2;
            opacity: 0;
        }

        /* --- Sound circles (Act 3) --- */
        .xp-circle {
            position: fixed;
            border-radius: 50%;
            mix-blend-mode: screen;
            pointer-events: none;
            z-index: 3;
            opacity: 0;
            visibility: hidden;
            will-change: transform;
        }

        /* --- Mobile pads (Act 3) --- */
        .xp-pads {
            position: fixed;
            bottom: 8vh;
            left: 50%;
            transform: translateX(-50%);
            display: none;
            gap: 12px;
            z-index: 20;
            opacity: 0;
            visibility: hidden;
        }
        .xp-pads.is-visible { display: flex; flex-wrap: wrap; justify-content: center; }

        .xp-pad {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            border: 1px solid rgba(114, 26, 214, 0.5);
            background: rgba(114, 26, 214, 0.15);
            color: var(--xp-white);
            font-size: 0.7rem;
            font-weight: 500;
            letter-spacing: 0.05em;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            -webkit-tap-highlight-color: transparent;
            touch-action: manipulation;
            transition: background 0.15s, border-color 0.15s;
        }
        .xp-pad:active {
            background: rgba(114, 26, 214, 0.4);
            border-color: var(--xp-violet);
        }

        /* --- Orbs (Act 4) --- */
        .xp-orb {
            position: fixed;
            border-radius: 50%;
            mix-blend-mode: screen;
            filter: blur(40px);
            pointer-events: none;
            z-index: 3;
            opacity: 0;
            visibility: hidden;
            will-change: transform;
        }

        /* --- Bubble (Act 4) --- */
        .xp-bubble {
            position: fixed;
            border-radius: 50%;
            background: radial-gradient(circle at 30% 30%, rgba(210, 153, 255, 0.2), transparent 70%);
            pointer-events: none;
            z-index: 3;
            opacity: 0;
            visibility: hidden;
            will-change: transform;
        }

        /* --- Start button (Act 1) — hidden, revealed by halo proximity --- */
        .xp-start {
            position: fixed;
            bottom: 10vh;
            left: 50%;
            transform: translateX(-50%);
            z-index: 20;
            background: none;
            border: 1px solid rgba(255, 250, 245, 0.2);
            border-radius: 999px;
            padding: 12px 28px;
            color: rgba(255, 250, 245, 0.7);
            font-family: inherit;
            font-size: clamp(0.8rem, 2vw, 0.95rem);
            letter-spacing: 0.06em;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: border-color 0.3s;
            -webkit-tap-highlight-color: transparent;
        }
        .xp-start:hover {
            border-color: rgba(255, 250, 245, 0.5);
        }

        /* --- Breathing circles (Act 5) — concentric rings --- */
        .xp-breath-container {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 200px;
            height: 200px;
            z-index: 5;
        }

        .xp-breath-inner {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: transparent;
            border: 1.5px solid rgba(114, 26, 214, 0.5);
            box-shadow: 0 0 50px rgba(114, 26, 214, 0.5), 0 0 100px rgba(114, 26, 214, 0.2), inset 0 0 30px rgba(114, 26, 214, 0.15);
            opacity: 0;
            visibility: hidden;
        }

        .xp-breath-outer {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 1px solid rgba(114, 26, 214, 0.3);
            box-shadow: 0 0 30px rgba(114, 26, 214, 0.15), inset 0 0 30px rgba(114, 26, 214, 0.08);
            opacity: 0;
            visibility: hidden;
        }

        .xp-breath-timer {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 1.4rem;
            font-weight: 400;
            letter-spacing: 0.06em;
            color: rgba(255, 250, 245, 0.2);
            z-index: 10;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        .xp-breath-label {
            position: fixed;
            top: calc(50% + 120px);
            left: 50%;
            transform: translateX(-50%);
            font-size: clamp(0.85rem, 2vw, 1rem);
            letter-spacing: 0.08em;
            color: rgba(255, 250, 245, 0.6);
            z-index: 10;
            opacity: 0;
            visibility: hidden;
        }

        /* --- Insight (revelation) --- */
        .xp-insight {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            max-width: 600px;
            padding: 0 2rem;
            z-index: 10;
        }

        .xp-insight-line {
            font-size: clamp(1.1rem, 3vw, 1.6rem);
            font-weight: 400;
            letter-spacing: 0.03em;
            line-height: 1.6;
            color: var(--xp-white);
            opacity: 0;
            visibility: hidden;
            margin-bottom: 0.8em;
        }

        .xp-insight-line--accent {
            color: var(--xp-rose);
            font-size: clamp(1.2rem, 3.5vw, 1.8rem);
            margin-top: 0.4em;
            margin-bottom: 0;
        }

        /* --- CTA --- */
        .xp-cta {
            position: fixed;
            bottom: 10vh;
            left: 50%;
            transform: translateX(-50%);
            z-index: 20;
            opacity: 0;
            visibility: hidden;
        }
        .xp-cta a {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 14px 32px;
            border-radius: 999px;
            background: var(--xp-violet);
            color: var(--xp-white);
            text-decoration: none;
            font-size: clamp(0.9rem, 2.5vw, 1.1rem);
            font-weight: 500;
            letter-spacing: 0.04em;
            transition: background 0.3s, transform 0.3s;
        }
        .xp-cta a:hover {
            background: var(--xp-violet-deep);
            transform: scale(1.03);
        }

        .xp-cta-star-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.15);
            flex-shrink: 0;
        }

        .xp-cta-star {
            animation: xp-spin 10s linear infinite;
        }

        @keyframes xp-spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* --- Hint (below title) --- */
        .xp-hint {
            position: fixed;
            top: calc(50% + 2.5rem);
            left: 50%;
            transform: translateX(-50%);
            font-size: clamp(0.85rem, 2vw, 1rem);
            letter-spacing: 0.08em;
            color: rgba(255, 250, 245, 0.5);
            pointer-events: none;
            z-index: 10;
            opacity: 0;
            visibility: hidden;
        }

        /* --- Custom cursor --- */
        .xp-cursor {
            position: fixed;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--xp-white);
            pointer-events: none;
            z-index: 100;
            transform: translate(-50%, -50%);
            opacity: 0;
            mix-blend-mode: difference;
            will-change: transform;
        }

        /* --- Skip button --- */
        .xp-skip {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 50;
            background: none;
            border: 1px solid rgba(255, 250, 245, 0.15);
            border-radius: 999px;
            padding: 8px 20px;
            color: rgba(255, 250, 245, 0.4);
            font-family: inherit;
            font-size: 0.7rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            cursor: pointer;
            transition: color 0.3s, border-color 0.3s;
        }
        .xp-skip:hover {
            color: var(--xp-white);
            border-color: rgba(255, 250, 245, 0.4);
        }

        /* --- Reduced motion: static fallback --- */
        @media (prefers-reduced-motion: reduce) {
            html, body { overflow: auto; cursor: auto; height: auto; }
            .xp-canvas {
                display: flex !important;
                position: relative !important;
                min-height: 50vh;
                align-items: center;
                justify-content: center;
            }
            .xp-text {
                position: relative !important;
                top: auto !important;
                left: auto !important;
                bottom: auto !important;
                transform: none !important;
                opacity: 1 !important;
                visibility: visible !important;
                padding: 2rem;
            }
            .xp-cta {
                position: relative !important;
                bottom: auto !important;
                left: auto !important;
                transform: none !important;
                opacity: 1 !important;
                visibility: visible !important;
                padding: 2rem;
            }
            .xp-insight-line {
                opacity: 1 !important;
                visibility: visible !important;
            }
            .xp-halo, .xp-particle, .xp-fluid-orb, .xp-circle, .xp-orb,
            .xp-bubble, .xp-breath-container, .xp-breath-label, .xp-breath-timer,
            .xp-pads, .xp-ripple, .xp-ambient-glow { display: none !important; }
            .xp-cursor, .xp-skip, .xp-hint, .xp-start, .xp-sound-hint { display: none !important; }
        }
    </style>
</head>
<body>
    @yield('content')

    <script src="/js/gsap.min.js"></script>
    <script type="module" src="/js/experience/main.js"></script>
</body>
</html>
