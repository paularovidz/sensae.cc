/**
 * Acte 1 — L'Obscurité
 * Cursor halo + drifting particles. User must click to proceed.
 * Hidden text "Cliquez pour démarrer" only visible when halo passes over it.
 */

import { randomRange, haptic, vh, vw } from '../utils.js';

const PARTICLE_COUNT = 50;

export function act1(gsap, next, mobile) {
    const canvas = document.querySelector('[data-xp-act="1"]');
    const halo = document.querySelector('[data-xp-halo]');
    const particleContainer = document.querySelector('[data-xp-particles]');
    const text = document.querySelector('[data-xp-text="1"]');
    const startBtn = document.querySelector('[data-xp-start]');
    if (!canvas || !halo) return;

    let mouseX = vw() / 2;
    let mouseY = vh() / 2;
    let rafId = null;
    let transitioned = false;
    let aborted = false;

    // Position setters for halo
    const setHaloX = gsap.quickSetter(halo, 'x', 'px');
    const setHaloY = gsap.quickSetter(halo, 'y', 'px');

    // Create particle pool
    const particles = [];
    for (let i = 0; i < PARTICLE_COUNT; i++) {
        const el = document.createElement('div');
        el.className = 'xp-particle';
        const size = randomRange(2, 6);
        el.style.width = size + 'px';
        el.style.height = size + 'px';
        el.style.background = `rgba(210, 153, 255, ${randomRange(0.15, 0.5)})`;
        particleContainer.appendChild(el);
        particles.push({
            el,
            x: randomRange(0, vw()),
            y: randomRange(0, vh()),
            vx: randomRange(-0.3, 0.3),
            vy: randomRange(-0.3, 0.3),
            size,
        });
    }

    const soundHint = document.querySelector('[data-xp-sound-hint]');

    // Intro: fade in main text + sound hint
    gsap.to(text, { autoAlpha: 1, duration: 1.5, delay: 0.5, ease: 'power2.out' });
    if (soundHint) {
        gsap.to(soundHint, { autoAlpha: 1, duration: 1, delay: 1.5, ease: 'power2.out' });
    }

    // The start button is positioned at the bottom, invisible by default.
    // It becomes visible only when the halo (cursor) is near it.
    let startBtnRect = null;
    if (startBtn) {
        // Make it exist in DOM but visually hidden (opacity controlled by proximity)
        gsap.set(startBtn, { autoAlpha: 0 });
        startBtnRect = startBtn.getBoundingClientRect();
    }

    // Show halo on first move
    let haloShown = false;

    function onMove(x, y) {
        mouseX = x;
        mouseY = y;

        if (!haloShown) {
            haloShown = true;
            gsap.to(halo, { autoAlpha: 1, duration: 0.6 });
            if (mobile) haptic(10);
        }
    }

    function onMouseMove(e) { onMove(e.clientX, e.clientY); }
    function onTouchMove(e) {
        const t = e.touches[0];
        if (t) onMove(t.clientX, t.clientY);
    }
    function onTouchStart(e) {
        const t = e.touches[0];
        if (t) onMove(t.clientX, t.clientY);
    }

    document.addEventListener('mousemove', onMouseMove);
    document.addEventListener('touchmove', onTouchMove, { passive: true });
    document.addEventListener('touchstart', onTouchStart, { passive: true });

    // Click anywhere to start
    let startClicked = false;

    function onStartClick() {
        if (transitioned || startClicked) return;
        startClicked = true;
        if (startBtn) gsap.to(startBtn, { autoAlpha: 0, duration: 0.4, ease: 'power2.in' });
        transitioned = true;
        setTimeout(() => transitionOut(), 500);
    }

    canvas.addEventListener('click', onStartClick);
    canvas.addEventListener('touchend', (e) => {
        e.preventDefault();
        onStartClick();
    });

    // Animation loop
    function tick() {
        if (aborted) return;
        rafId = requestAnimationFrame(tick);

        // Smooth halo follow
        setHaloX(mouseX);
        setHaloY(mouseY);

        // Drift particles
        const w = vw();
        const h = vh();
        for (const p of particles) {
            p.x += p.vx;
            p.y += p.vy;

            // Wrap around
            if (p.x < -20) p.x = w + 20;
            if (p.x > w + 20) p.x = -20;
            if (p.y < -20) p.y = h + 20;
            if (p.y > h + 20) p.y = -20;

            // Proximity to cursor: show near, hide far
            const dx = p.x - mouseX;
            const dy = p.y - mouseY;
            const dist = Math.sqrt(dx * dx + dy * dy);
            const maxDist = 180;
            const alpha = dist < maxDist ? (1 - dist / maxDist) * 0.7 : 0;

            p.el.style.transform = `translate(${p.x}px, ${p.y}px)`;
            p.el.style.opacity = alpha;
            if (alpha > 0) p.el.style.visibility = 'visible';
        }

        // Start button: reveal when halo is near
        if (startBtn && haloShown && !startClicked) {
            startBtnRect = startBtn.getBoundingClientRect();
            const btnCx = startBtnRect.left + startBtnRect.width / 2;
            const btnCy = startBtnRect.top + startBtnRect.height / 2;
            const dx = mouseX - btnCx;
            const dy = mouseY - btnCy;
            const dist = Math.sqrt(dx * dx + dy * dy);
            const revealDist = 200;

            const alpha = dist < revealDist ? (1 - dist / revealDist) * 0.8 : 0;
            startBtn.style.opacity = alpha;
            startBtn.style.visibility = alpha > 0 ? 'visible' : 'hidden';
        }
    }

    rafId = requestAnimationFrame(tick);

    function transitionOut() {
        // Fade halo + particles + text + sound hint
        gsap.to(halo, { autoAlpha: 0, duration: 1, ease: 'power2.inOut' });
        gsap.to(text, { autoAlpha: 0, duration: 0.8, ease: 'power2.inOut' });
        if (soundHint) gsap.to(soundHint, { autoAlpha: 0, duration: 0.6, ease: 'power2.inOut' });
        if (startBtn) gsap.to(startBtn, { autoAlpha: 0, duration: 0.5 });
        particles.forEach(p => {
            gsap.to(p.el, {
                autoAlpha: 0,
                duration: randomRange(0.6, 1.2),
                ease: 'power2.inOut',
            });
        });

        // After fade, proceed
        setTimeout(() => {
            cleanup();
            next();
        }, 1400);
    }

    function cleanup() {
        aborted = true;
        if (rafId) cancelAnimationFrame(rafId);
        document.removeEventListener('mousemove', onMouseMove);
        document.removeEventListener('touchmove', onTouchMove);
        document.removeEventListener('touchstart', onTouchStart);
        canvas.removeEventListener('click', onStartClick);
    }

    return cleanup;
}
