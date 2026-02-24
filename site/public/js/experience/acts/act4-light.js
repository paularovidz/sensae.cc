/**
 * Acte 4 — La Lumière
 * Contemplation: slow orbs, rising bubbles, drone continues. ~12s then transition.
 */

import { randomRange, vh, vw } from '../utils.js';

const ORB_COUNT = 6;
const BUBBLE_COUNT = 12;
const DURATION = 8000; // 8s

const ORB_COLORS = [
    `radial-gradient(circle, rgba(114, 26, 214, 0.4) 0%, transparent 70%)`,
    `radial-gradient(circle, rgba(210, 153, 255, 0.35) 0%, transparent 70%)`,
    `radial-gradient(circle, rgba(155, 77, 224, 0.3) 0%, transparent 70%)`,
    `radial-gradient(circle, rgba(255, 214, 153, 0.25) 0%, transparent 70%)`,
    `radial-gradient(circle, rgba(232, 196, 255, 0.3) 0%, transparent 70%)`,
    `radial-gradient(circle, rgba(58, 14, 110, 0.5) 0%, transparent 70%)`,
];

export function act4(gsap, audio, next) {
    const canvas = document.querySelector('[data-xp-act="4"]');
    const orbsContainer = document.querySelector('[data-xp-orbs]');
    const bubblesContainer = document.querySelector('[data-xp-bubbles]');
    const text = document.querySelector('[data-xp-text="4"]');
    if (!canvas || !orbsContainer) return;

    let aborted = false;
    const tweens = [];

    // Ensure drone is playing
    audio.startDrone();
    audio.setDroneVolume(0.1, 2);

    // Show text — fade out earlier
    gsap.to(text, { autoAlpha: 1, duration: 1.5, delay: 0.3, ease: 'power2.out' });
    gsap.to(text, { autoAlpha: 0, duration: 1, delay: 4, ease: 'power2.inOut' });

    // Create orbs
    const orbs = [];
    for (let i = 0; i < ORB_COUNT; i++) {
        const el = document.createElement('div');
        el.className = 'xp-orb';
        const size = randomRange(150, 300);
        Object.assign(el.style, {
            width: size + 'px',
            height: size + 'px',
            background: ORB_COLORS[i % ORB_COLORS.length],
        });
        orbsContainer.appendChild(el);
        orbs.push(el);

        const startX = randomRange(vw() * 0.1, vw() * 0.9);
        const startY = randomRange(vh() * 0.1, vh() * 0.9);

        // Fade in
        gsap.set(el, { x: startX, y: startY });
        gsap.to(el, {
            autoAlpha: randomRange(0.5, 0.9),
            duration: randomRange(1.5, 2.5),
            delay: i * 0.3,
            ease: 'power2.out',
        });

        // Slow organic sway (x and y independent, different speeds → natural drift)
        const swayX = gsap.to(el, {
            x: `+=${randomRange(-80, 80)}`,
            duration: randomRange(4, 7),
            yoyo: true,
            repeat: -1,
            ease: 'sine.inOut',
        });
        const swayY = gsap.to(el, {
            y: `+=${randomRange(-60, 60)}`,
            duration: randomRange(5, 8),
            yoyo: true,
            repeat: -1,
            ease: 'sine.inOut',
        });
        tweens.push(swayX, swayY);
    }

    // Rising bubbles
    const bubbles = [];
    for (let i = 0; i < BUBBLE_COUNT; i++) {
        const el = document.createElement('div');
        el.className = 'xp-bubble';
        const size = randomRange(8, 24);
        Object.assign(el.style, {
            width: size + 'px',
            height: size + 'px',
        });
        bubblesContainer.appendChild(el);
        bubbles.push(el);

        // Stagger bubble launches
        launchBubble(el, size, randomRange(0, 4));
    }

    function launchBubble(el, size, delay) {
        if (aborted) return;
        const startX = randomRange(vw() * 0.2, vw() * 0.8);
        const startY = vh() + 20;

        gsap.set(el, { x: startX, y: startY, autoAlpha: 0 });

        const tween = gsap.to(el, {
            y: -40,
            x: `+=${randomRange(-40, 40)}`,
            autoAlpha: randomRange(0.2, 0.5),
            duration: randomRange(6, 10),
            delay: delay,
            ease: 'power1.out',
            onComplete: () => {
                // Re-launch for continuous effect
                if (!aborted) launchBubble(el, size, randomRange(0.5, 2));
            },
        });
        tweens.push(tween);
    }

    // Schedule transition
    const timer = setTimeout(() => {
        if (!aborted) transitionOut();
    }, DURATION);

    function transitionOut() {
        // Fade out everything
        [...orbs, ...bubbles].forEach(el => {
            gsap.to(el, {
                autoAlpha: 0,
                duration: randomRange(1, 2),
                ease: 'power2.inOut',
            });
        });

        setTimeout(() => {
            cleanup();
            next();
        }, 1200);
    }

    function cleanup() {
        aborted = true;
        clearTimeout(timer);
        tweens.forEach(t => { try { t.kill(); } catch {} });
    }

    return cleanup;
}
