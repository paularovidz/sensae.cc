/**
 * Acte 6 — Ondulations
 * Dark surface like water. Cursor/touch creates expanding ripple rings.
 * Click = larger ripple. Text + CTA shown directly with ripples as backdrop.
 * Gentle ambient glow in center.
 */

import { randomRange, vh, vw, haptic } from '../utils.js';

const SPAWN_THROTTLE = 120;
const MAX_RIPPLES = 40;

const RING_COLORS = [
    { r: 114, g: 26,  b: 214 },
    { r: 155, g: 77,  b: 224 },
    { r: 26,  g: 148, b: 163 },
    { r: 210, g: 153, b: 255 },
    { r: 40,  g: 80,  b: 200 },
];

export function act6(gsap, audio) {
    const canvas = document.querySelector('[data-xp-act="6"]');
    const container = document.querySelector('[data-xp-ripples]');
    const text = document.querySelector('[data-xp-text="6"]');
    const cta = document.querySelector('[data-xp-cta]');
    const skip = document.querySelector('[data-xp-skip]');
    if (!canvas || !container) return;

    let aborted = false;
    let lastSpawnTime = 0;
    let rippleCount = 0;

    // Hide skip for final act
    if (skip) gsap.to(skip, { autoAlpha: 0, duration: 0.5 });

    // Gentle drone fade + stop
    if (audio) {
        audio.setDroneVolume(0.03, 2);
        audio.stopDrone(8);
    }

    // Subtle ambient glow
    const glow = document.createElement('div');
    glow.className = 'xp-ambient-glow';
    container.appendChild(glow);
    gsap.to(glow, { opacity: 1, duration: 3, ease: 'power2.out' });
    gsap.to(glow, {
        scale: 1.3,
        duration: 4,
        ease: 'sine.inOut',
        yoyo: true,
        repeat: -1,
    });

    // Show text and CTA directly
    gsap.to(text, {
        autoAlpha: 1,
        duration: 1.8,
        delay: 1,
        ease: 'power2.out',
    });

    gsap.to(cta, {
        autoAlpha: 1,
        y: 0,
        duration: 1.2,
        delay: 2.5,
        ease: 'power2.out',
    });

    // Restore cursor for CTA
    setTimeout(() => { document.body.style.cursor = 'auto'; }, 2500);

    // Play a soft final chime
    if (audio) {
        setTimeout(() => { if (!aborted) audio.playChime('high'); }, 1500);
    }

    // --- Ripple spawning (visual backdrop) ---
    function spawnRipple(x, y, large) {
        if (rippleCount >= MAX_RIPPLES) return;

        const el = document.createElement('div');
        el.className = 'xp-ripple';
        const c = RING_COLORS[Math.floor(Math.random() * RING_COLORS.length)];
        const size = large ? randomRange(40, 60) : randomRange(15, 30);
        const alpha = large ? 0.2 : 0.12;

        Object.assign(el.style, {
            width: size + 'px',
            height: size + 'px',
            left: x + 'px',
            top: y + 'px',
            background: `radial-gradient(circle, transparent 40%, rgba(${c.r},${c.g},${c.b},${alpha}) 50%, transparent 60%)`,
            boxShadow: `0 0 15px rgba(${c.r},${c.g},${c.b},${alpha * 0.5})`,
        });

        container.appendChild(el);
        rippleCount++;

        const targetScale = large ? randomRange(12, 18) : randomRange(6, 12);

        gsap.fromTo(el,
            { scale: 0.5, opacity: large ? 0.8 : 0.6 },
            {
                scale: targetScale,
                opacity: 0,
                duration: randomRange(3, 5),
                ease: 'power1.out',
                onComplete: () => { el.remove(); rippleCount--; },
            }
        );
    }

    // --- Input ---
    function onMove(x, y) {
        const now = performance.now();
        if (now - lastSpawnTime > SPAWN_THROTTLE) {
            spawnRipple(x, y, false);
            lastSpawnTime = now;
        }
    }

    function onMouseMove(e) { onMove(e.clientX, e.clientY); }
    function onTouchMove(e) {
        const t = e.touches[0];
        if (t) onMove(t.clientX, t.clientY);
    }

    function onClick(e) {
        if (aborted) return;
        haptic(10);
        const cx = e.clientX ?? vw() / 2;
        const cy = e.clientY ?? vh() / 2;
        spawnRipple(cx, cy, true);
        for (let i = 0; i < 3; i++) {
            setTimeout(() => {
                if (!aborted) {
                    spawnRipple(
                        cx + randomRange(-60, 60),
                        cy + randomRange(-60, 60),
                        false
                    );
                }
            }, i * 100);
        }
    }

    canvas.addEventListener('mousemove', onMouseMove);
    canvas.addEventListener('touchmove', onTouchMove, { passive: true });
    canvas.addEventListener('touchstart', (e) => {
        const t = e.touches[0];
        if (t) onMove(t.clientX, t.clientY);
    }, { passive: true });
    canvas.addEventListener('click', onClick);

    // Double-tap for mobile
    let lastTapTime = 0;
    canvas.addEventListener('touchend', (e) => {
        const now = performance.now();
        if (now - lastTapTime < 300) {
            const t = e.changedTouches[0];
            if (t) onClick({ clientX: t.clientX, clientY: t.clientY });
        }
        lastTapTime = now;
    }, { passive: true });

    function cleanup() {
        aborted = true;
        canvas.removeEventListener('mousemove', onMouseMove);
        canvas.removeEventListener('touchmove', onTouchMove);
        canvas.removeEventListener('click', onClick);
    }

    return cleanup;
}
