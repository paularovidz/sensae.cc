/**
 * Acte 5 — L'Invitation
 * Breathing with concentric circles (inner glow + outer ring).
 * Drone runs continuously. Chime on each breath transition (two variants).
 * Color evolves fluidly across cycles: violet → teal → blue.
 * Timer countdown inside circle. 3 complete cycles then transition.
 */

// Color palette — shifts fluidly from violet to cooler tones
const COLOR_STOPS = [
    { r: 114, g: 26,  b: 214 }, // violet
    { r: 26,  g: 148, b: 163 }, // teal
    { r: 40,  g: 80,  b: 200 }, // blue
];

const BG_STOPS = [
    { r: 12, g: 6,  b: 24 },   // #0c0618
    { r: 6,  g: 19, b: 15 },   // #06130f
    { r: 6,  g: 10, b: 24 },   // #060a18
];

function lerpColor(a, b, t) {
    return {
        r: Math.round(a.r + (b.r - a.r) * t),
        g: Math.round(a.g + (b.g - a.g) * t),
        b: Math.round(a.b + (b.b - a.b) * t),
    };
}

function getColorAt(stops, progress) {
    const segments = stops.length - 1;
    const scaled = progress * segments;
    const idx = Math.min(Math.floor(scaled), segments - 1);
    const t = scaled - idx;
    return lerpColor(stops[idx], stops[idx + 1], t);
}

function rgba(c, a) {
    return `rgba(${c.r}, ${c.g}, ${c.b}, ${a})`;
}

const DEFAULT_BG = { r: 5, g: 2, b: 8 }; // #050208

function hex(c) {
    return '#' + [c.r, c.g, c.b].map(v => v.toString(16).padStart(2, '0')).join('');
}

export function act5(gsap, audio, next) {
    const canvas = document.querySelector('[data-xp-act="5"]');
    const breathInner = document.querySelector('[data-xp-breath-inner]');
    const breathOuter = document.querySelector('[data-xp-breath-outer]');
    const breathLabel = document.querySelector('[data-xp-breath-label]');
    const breathTimer = document.querySelector('[data-xp-breath-timer]');
    const text = document.querySelector('[data-xp-text="5"]');
    if (!canvas || !breathInner) return;

    let aborted = false;

    // Ensure drone is running (continuous from act 3/4)
    audio.startDrone();
    audio.setDroneVolume(0.08, 1);

    // Fade in breathing circles
    gsap.to(breathInner, { autoAlpha: 1, duration: 1.5, delay: 0.5, ease: 'power2.out' });
    if (breathOuter) {
        gsap.to(breathOuter, { autoAlpha: 1, duration: 1.8, delay: 0.7, ease: 'power2.out' });
    }
    gsap.to(breathLabel, { autoAlpha: 1, duration: 1, delay: 1, ease: 'power2.out' });
    if (breathTimer) gsap.to(breathTimer, { autoAlpha: 1, duration: 1, delay: 1.2, ease: 'power2.out' });

    // Show text briefly
    const introTl = gsap.timeline();
    introTl
        .to(text, { autoAlpha: 1, duration: 1.2, ease: 'power2.out' }, 0.3)
        .to(text, { autoAlpha: 0, duration: 0.8, ease: 'power2.inOut' }, 3.5);

    // Breathing timing
    const INHALE = 4;
    const HOLD = 2;
    const EXHALE = 4;
    const CYCLE_DURATION = INHALE + HOLD + EXHALE; // 10s
    const CYCLES = 2;
    const TOTAL_DURATION = CYCLE_DURATION * CYCLES; // 20s

    // Apply initial color
    applyColor(0);

    // --- Timer countdown ---
    let timerInterval = null;
    let phaseStart = 0;
    let phaseDuration = 0;

    function startPhaseTimer(duration) {
        phaseDuration = duration;
        phaseStart = performance.now();
        if (timerInterval) clearInterval(timerInterval);
        updateTimer();
        timerInterval = setInterval(updateTimer, 100);
    }

    function updateTimer() {
        if (!breathTimer) return;
        const elapsed = (performance.now() - phaseStart) / 1000;
        const remaining = Math.max(0, phaseDuration - elapsed);
        breathTimer.textContent = Math.ceil(remaining);
    }

    function clearTimer() {
        if (timerInterval) clearInterval(timerInterval);
        if (breathTimer) breathTimer.textContent = '';
    }

    // --- Fluid color system ---
    function applyColor(progress) {
        const c = getColorAt(COLOR_STOPS, progress);

        gsap.to(breathInner, {
            borderColor: rgba(c, 0.5),
            boxShadow: `0 0 50px ${rgba(c, 0.5)}, 0 0 100px ${rgba(c, 0.2)}, inset 0 0 30px ${rgba(c, 0.15)}`,
            duration: 0.5,
            ease: 'none',
            overwrite: 'auto',
        });
        if (breathOuter) {
            gsap.to(breathOuter, {
                borderColor: rgba(c, 0.3),
                boxShadow: `0 0 30px ${rgba(c, 0.15)}, inset 0 0 20px ${rgba(c, 0.08)}`,
                duration: 0.5,
                ease: 'none',
                overwrite: 'auto',
            });
        }
    }

    // Continuous color update via RAF
    let colorRafId = null;
    let globalStartTime = null;

    function tickColor() {
        if (aborted) return;
        colorRafId = requestAnimationFrame(tickColor);
        if (globalStartTime === null) return;

        const elapsed = (performance.now() - globalStartTime) / 1000;
        const progress = Math.min(elapsed / TOTAL_DURATION, 1);
        applyColor(progress);

        // Animate background color fluidly — fade in over 4s from default bg
        const bgFadeIn = Math.min(elapsed / 4, 1);
        const targetBg = getColorAt(BG_STOPS, progress);
        const bg = lerpColor(DEFAULT_BG, targetBg, bgFadeIn);
        document.body.style.backgroundColor = hex(bg);
    }

    colorRafId = requestAnimationFrame(tickColor);

    // --- Build one full cycle as a timeline ---
    function buildCycleTl() {
        const tl = gsap.timeline();

        // --- Inhale ---
        tl.call(() => {
            if (breathLabel) breathLabel.textContent = 'Inspirez...';
            audio.setDroneVolume(0.12, INHALE);
            startPhaseTimer(INHALE);
        }, [], 0);

        tl.to(breathInner, {
            scale: 1.6,
            duration: INHALE,
            ease: 'sine.inOut',
        }, 0);

        if (breathOuter) {
            tl.to(breathOuter, {
                scale: 1.5,
                opacity: 0.7,
                duration: INHALE,
                ease: 'sine.inOut',
            }, 0.3);
        }

        // --- Hold ---
        tl.call(() => {
            if (breathLabel) breathLabel.textContent = 'Retenez...';
            clearTimer();
        }, [], INHALE);

        // --- Exhale ---
        tl.call(() => {
            audio.playChime('low');
            if (breathLabel) breathLabel.textContent = 'Expirez...';
            audio.setDroneVolume(0.06, EXHALE);
            startPhaseTimer(EXHALE);
        }, [], INHALE + HOLD);

        tl.to(breathInner, {
            scale: 1,
            duration: EXHALE,
            ease: 'sine.inOut',
        }, INHALE + HOLD);

        if (breathOuter) {
            tl.to(breathOuter, {
                scale: 1,
                opacity: 0.4,
                duration: EXHALE,
                ease: 'sine.inOut',
            }, INHALE + HOLD + 0.3);
        }

        // Clear label near end of exhale
        tl.call(() => {
            if (breathLabel) breathLabel.textContent = '';
            clearTimer();
        }, [], INHALE + HOLD + EXHALE - 0.5);

        return tl;
    }

    // --- Master timeline ---
    const masterTl = gsap.timeline({
        onComplete: () => {
            if (!aborted) transitionOut();
        },
    });

    globalStartTime = performance.now();

    for (let i = 0; i < CYCLES; i++) {
        if (i > 0) {
            masterTl.call(() => { audio.playChime('high'); });
        }
        masterTl.add(buildCycleTl());
    }

    function transitionOut() {
        audio.playChime('high');
        clearTimer();
        if (colorRafId) cancelAnimationFrame(colorRafId);

        // Fade out breathing elements
        gsap.to(breathInner, { autoAlpha: 0, duration: 1.5, ease: 'power2.inOut' });
        if (breathOuter) gsap.to(breathOuter, { autoAlpha: 0, duration: 1.5, ease: 'power2.inOut' });
        gsap.to(breathLabel, { autoAlpha: 0, duration: 1, ease: 'power2.inOut' });
        if (breathTimer) gsap.to(breathTimer, { autoAlpha: 0, duration: 1, ease: 'power2.inOut' });

        setTimeout(() => {
            cleanup();
            next();
        }, 2000);
    }

    function cleanup() {
        aborted = true;
        masterTl.kill();
        clearTimer();
        if (colorRafId) cancelAnimationFrame(colorRafId);
    }

    return cleanup;
}
