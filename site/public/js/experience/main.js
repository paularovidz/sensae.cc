/**
 * Voyage Sensoriel — Orchestrator
 * Vanilla ES modules, GSAP loaded locally (window.gsap)
 */

import { reducedMotion, isMobile } from './utils.js';
import { createAudio } from './audio.js';
import { act1 } from './acts/act1-darkness.js';
import { act2 } from './acts/act2-touch.js';
import { act3 } from './acts/act3-sound.js';
import { act4 } from './acts/act4-light.js';
import { act5 } from './acts/act5-breathing.js';
import { actInsight } from './acts/act-insight.js';
import { act6 } from './acts/act6-ink.js';

const gsap = window.gsap;

// Reduced motion: show all text + CTA, skip animations
if (reducedMotion()) {
    document.querySelectorAll('.xp-canvas').forEach(el => el.classList.add('is-active'));
    document.querySelectorAll('.xp-text, .xp-cta').forEach(el => {
        el.style.opacity = '1';
        el.style.visibility = 'visible';
    });
    // Skip the rest
} else {
    init();
}

function init() {
    const audio = createAudio();
    const cursor = document.querySelector('[data-xp-cursor]');
    const skip = document.querySelector('[data-xp-skip]');
    const mobile = isMobile();

    // Custom cursor (desktop only)
    if (!mobile && cursor) {
        gsap.set(cursor, { autoAlpha: 1 });
        const setX = gsap.quickSetter(cursor, 'x', 'px');
        const setY = gsap.quickSetter(cursor, 'y', 'px');
        document.addEventListener('mousemove', (e) => {
            setX(e.clientX);
            setY(e.clientY);
        });
    }

    // Act sequence
    // Act 1's click will serve as user gesture for AudioContext
    const acts = [
        { key: 1, run: (next) => act1(gsap, () => { audio.ensureCtx(); next(); }, mobile) },
        { key: 2, run: (next) => act2(gsap, next, mobile) },
        { key: 3, run: (next) => act3(gsap, audio, next, mobile) },
        { key: 4, run: (next) => act4(gsap, audio, next) },
        { key: 5, run: (next) => act5(gsap, audio, next) },
        { key: 'insight', run: (next) => actInsight(gsap, audio, next) },
        { key: 6, run: () => act6(gsap, audio) },
    ];

    let currentIndex = 0;
    let currentCleanup = null;

    function runAct(index) {
        if (index >= acts.length) return;
        currentIndex = index;

        const act = acts[index];

        // Show this act's canvas
        document.querySelectorAll('.xp-canvas').forEach(el => el.classList.remove('is-active'));
        const canvas = document.querySelector(`[data-xp-act="${act.key}"]`);
        if (canvas) canvas.classList.add('is-active');

        // Run act, passing a "next" callback
        currentCleanup = act.run(() => {
            // Clean up current act
            if (typeof currentCleanup === 'function') currentCleanup();
            currentCleanup = null;
            runAct(index + 1);
        });
    }

    // Skip button: advance to next act
    if (skip) {
        skip.addEventListener('click', () => {
            const nextIdx = currentIndex + 1;
            if (nextIdx >= acts.length) return;
            if (typeof currentCleanup === 'function') currentCleanup();
            currentCleanup = null;
            audio.ensureCtx();
            audio.resume();
            runAct(nextIdx);
        });
    }

    // Start
    runAct(0);
}
