/**
 * Acte 2 — Orbes Fluides
 * 6 large luminous spheres that drift and react to cursor.
 * When they overlap, mix-blend-mode creates beautiful light mixing.
 * Scroll = color shift. Click = gentle pulse.
 * Minimum 10s before transition.
 */

import { randomRange, clamp, vh, vw, haptic } from '../utils.js';

const MIN_DURATION = 10000; // 10s
const BASE_GRAVITY = 0.08;  // Stronger gravity — orbs follow cursor more clearly
const FRICTION = 0.97;      // More damping — responsive but smooth

const ORB_CONFIGS = [
    { size: 350, h: 270, s: 70, l: 40, a: 0.35 },
    { size: 280, h: 190, s: 65, l: 42, a: 0.30 },
    { size: 320, h: 300, s: 55, l: 48, a: 0.28 },
    { size: 260, h: 220, s: 70, l: 38, a: 0.32 },
    { size: 300, h: 30,  s: 75, l: 55, a: 0.25 },
    { size: 240, h: 160, s: 60, l: 45, a: 0.30 },
];

export function act2(gsap, next, mobile) {
    const canvas = document.querySelector('[data-xp-act="2"]');
    const container = document.querySelector('[data-xp-fluid-orbs]');
    const text = document.querySelector('[data-xp-text="2"]');
    const hint = document.querySelector('[data-xp-hint="2"]');
    if (!canvas || !container) return;

    let aborted = false;
    let mouseX = vw() / 2;
    let mouseY = vh() / 2;
    let hueShift = 0;
    let startTime = performance.now();
    let interactionTime = 0;
    let transitioned = false;
    let lastMoveTime = 0;
    let rafId = null;

    // Intro text — hint disappears with title
    const introTl = gsap.timeline();
    introTl
        .to(text, { autoAlpha: 1, duration: 1.2, ease: 'power2.out' }, 0.3)
        .to(hint, { autoAlpha: 1, duration: 0.8, ease: 'power2.out' }, 1.2)
        .to(text, { autoAlpha: 0, duration: 0.8, ease: 'power2.inOut' }, 3.5)
        .to(hint, { autoAlpha: 0, duration: 0.8, ease: 'power2.inOut' }, 3.5);

    const w = vw();
    const h = vh();

    // Create orbs
    const orbs = ORB_CONFIGS.map((cfg) => {
        const el = document.createElement('div');
        el.className = 'xp-fluid-orb';

        Object.assign(el.style, {
            width: cfg.size + 'px',
            height: cfg.size + 'px',
            opacity: '0',
        });

        container.appendChild(el);

        return {
            el,
            x: randomRange(cfg.size * 0.5, w - cfg.size * 0.5),
            y: randomRange(cfg.size * 0.5, h - cfg.size * 0.5),
            vx: randomRange(-0.3, 0.3),
            vy: randomRange(-0.3, 0.3),
            pulse: 1, // scale multiplier for click pulse
            ...cfg,
        };
    });

    // Apply gradient colors based on hue shift
    function updateColors() {
        for (const o of orbs) {
            const hue = ((o.h + hueShift) % 360 + 360) % 360;
            o.el.style.background =
                `radial-gradient(circle, ` +
                `hsla(${hue}, ${o.s}%, ${o.l}%, ${o.a}) 0%, ` +
                `hsla(${hue}, ${o.s}%, ${o.l}%, ${o.a * 0.3}) 50%, ` +
                `transparent 70%)`;
        }
    }

    updateColors();

    // Staggered fade-in
    orbs.forEach((o, i) => {
        gsap.to(o.el, {
            opacity: 1,
            duration: 1.5,
            delay: 0.3 + i * 0.2,
            ease: 'power2.out',
        });
    });

    // --- Input handlers ---
    function onMove(x, y) {
        mouseX = x;
        mouseY = y;
        lastMoveTime = performance.now();
    }

    function onMouseMove(e) { onMove(e.clientX, e.clientY); }
    function onTouchMove(e) {
        const t = e.touches[0];
        if (t) onMove(t.clientX, t.clientY);
    }

    function onWheel(e) {
        if (aborted || transitioned) return;
        e.preventDefault();
        hueShift = (hueShift + e.deltaY * 0.5) % 360;
        updateColors();
    }

    function onClick(e) {
        if (aborted || transitioned) return;
        haptic(10);

        // Gentle pulse: animate the pulse multiplier (applied in RAF loop)
        for (const o of orbs) {
            gsap.to(o, {
                pulse: 1.3,
                duration: 0.25,
                ease: 'power2.out',
                yoyo: true,
                repeat: 1,
                onComplete: () => { o.pulse = 1; },
            });
        }
    }

    canvas.addEventListener('mousemove', onMouseMove);
    canvas.addEventListener('touchmove', onTouchMove, { passive: true });
    canvas.addEventListener('touchstart', (e) => {
        const t = e.touches[0];
        if (t) onMove(t.clientX, t.clientY);
    }, { passive: true });
    canvas.addEventListener('wheel', onWheel, { passive: false });
    canvas.addEventListener('click', onClick);

    // --- Physics loop ---
    function tick(now) {
        if (aborted) return;
        rafId = requestAnimationFrame(tick);

        const curW = vw();
        const curH = vh();

        if (now - lastMoveTime < 500) interactionTime += 16;

        for (let i = 0; i < orbs.length; i++) {
            const o = orbs[i];

            // Gravity toward cursor
            const dx = mouseX - o.x;
            const dy = mouseY - o.y;
            const dist = Math.sqrt(dx * dx + dy * dy) || 1;

            const pull = BASE_GRAVITY * clamp(dist / 300, 0.1, 1);
            o.vx += (dx / dist) * pull;
            o.vy += (dy / dist) * pull;

            // Repulsion between orbs — prevent clumping
            for (let j = i + 1; j < orbs.length; j++) {
                const other = orbs[j];
                const odx = o.x - other.x;
                const ody = o.y - other.y;
                const odist = Math.sqrt(odx * odx + ody * ody) || 1;
                const minDist = (o.size + other.size) * 0.3;
                if (odist < minDist) {
                    const repel = 0.03 * (1 - odist / minDist);
                    const nx = odx / odist;
                    const ny = ody / odist;
                    o.vx += nx * repel;
                    o.vy += ny * repel;
                    other.vx -= nx * repel;
                    other.vy -= ny * repel;
                }
            }

            // Friction
            o.vx *= FRICTION;
            o.vy *= FRICTION;

            // Update position
            o.x += o.vx;
            o.y += o.vy;

            // Soft bounce at edges
            const margin = o.size * 0.3;
            if (o.x < margin) { o.x = margin; o.vx *= -0.5; }
            if (o.x > curW - margin) { o.x = curW - margin; o.vx *= -0.5; }
            if (o.y < margin) { o.y = margin; o.vy *= -0.5; }
            if (o.y > curH - margin) { o.y = curH - margin; o.vy *= -0.5; }

            // Apply position + pulse scale (center the orb on its coordinates)
            o.el.style.transform = `translate(${o.x - o.size / 2}px, ${o.y - o.size / 2}px) scale(${o.pulse})`;
        }

        // Transition check
        const elapsed = now - startTime;
        if (!transitioned && elapsed >= MIN_DURATION && interactionTime >= 2000) {
            transitioned = true;
            transitionOut();
        }
    }

    rafId = requestAnimationFrame(tick);

    function transitionOut() {
        // Fade hint
        if (hint) gsap.to(hint, { autoAlpha: 0, duration: 0.6, ease: 'power2.inOut' });

        const cx = vw() / 2;
        const cy = vh() / 2;

        // Converge to center and fade
        for (const o of orbs) {
            // Manually push toward center
            o.vx = (cx - o.x) * 0.02;
            o.vy = (cy - o.y) * 0.02;
        }

        orbs.forEach((o, i) => {
            gsap.to(o.el, {
                opacity: 0,
                scale: 0.3,
                duration: 1.5,
                delay: 0.1 + i * 0.1,
                ease: 'power2.inOut',
                onComplete: () => o.el.remove(),
            });
        });

        setTimeout(() => {
            cleanup();
            next();
        }, 2200);
    }

    function cleanup() {
        aborted = true;
        if (rafId) cancelAnimationFrame(rafId);
        canvas.removeEventListener('mousemove', onMouseMove);
        canvas.removeEventListener('touchmove', onTouchMove);
        canvas.removeEventListener('wheel', onWheel);
        canvas.removeEventListener('click', onClick);
    }

    return cleanup;
}
