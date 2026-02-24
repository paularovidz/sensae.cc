/**
 * Acte 3 — Le Son
 * Keyboard plays bell notes, each creates an expanding colored circle.
 * Mobile: touch pads. Drone starts after 3rd note.
 * Transition after 15s minimum + 12 notes + 500ms silence after last note.
 */

import { noteColor, randomRange, vh, vw, haptic } from '../utils.js';

const DRONE_AFTER = 3;
const MIN_DURATION = 10000; // 10s minimum
const SILENCE_DELAY = 500;  // 500ms after last note

export function act3(gsap, audio, next, mobile) {
    const canvas = document.querySelector('[data-xp-act="3"]');
    const circlesContainer = document.querySelector('[data-xp-circles]');
    const padsContainer = document.querySelector('[data-xp-pads]');
    const text = document.querySelector('[data-xp-text="3"]');
    const hint = document.querySelector('[data-xp-hint="3"]');
    if (!canvas || !circlesContainer) return;

    let noteCount = 0;
    let transitioned = false;
    let aborted = false;
    let startTime = performance.now();
    let transitionTimer = null;
    const activeKeys = new Set();

    // Intro text
    const introTl = gsap.timeline();
    introTl
        .to(text, { autoAlpha: 1, duration: 1.2, ease: 'power2.out' }, 0.3)
        .to(hint, { autoAlpha: 1, duration: 0.8, ease: 'power2.out' }, 1.2)
        .to(text, { autoAlpha: 0, duration: 0.8, ease: 'power2.inOut' }, 3.5)
        .to(hint, { autoAlpha: 0, duration: 0.6, ease: 'power2.inOut' }, 4);

    // Mobile pads
    if (mobile && padsContainer) {
        padsContainer.classList.add('is-visible');
        gsap.to(padsContainer, { autoAlpha: 1, duration: 0.8, delay: 1.5, ease: 'power2.out' });

        padsContainer.querySelectorAll('.xp-pad').forEach(pad => {
            const noteIdx = parseInt(pad.dataset.note, 10);
            pad.addEventListener('touchstart', (e) => {
                e.preventDefault();
                triggerNote(noteIdx);
                haptic(12);
            }, { passive: false });
        });
    }

    // Desktop keyboard
    function onKeyDown(e) {
        if (aborted || transitioned) return;
        const key = e.key.toLowerCase();
        if (activeKeys.has(key)) return;
        activeKeys.add(key);

        const noteIdx = audio.keyMap[key];
        if (noteIdx !== undefined) {
            triggerNote(noteIdx);
        }
    }

    function onKeyUp(e) {
        activeKeys.delete(e.key.toLowerCase());
    }

    function triggerNote(index) {
        if (transitioned) return;
        noteCount++;

        // Play audio
        audio.playBell(index);

        // Start drone after N notes
        if (noteCount === DRONE_AFTER) {
            audio.startDrone();
        }

        // Visual: expanding circle
        spawnCircle(index);

        // Reset debounce timer on each note
        if (transitionTimer) clearTimeout(transitionTimer);

        // After minimum duration elapsed, wait for silence to transition
        if (performance.now() - startTime >= MIN_DURATION) {
            transitionTimer = setTimeout(() => {
                if (!transitioned) {
                    transitioned = true;
                    transitionOut();
                }
            }, SILENCE_DELAY);
        }
    }

    function spawnCircle(index) {
        const el = document.createElement('div');
        el.className = 'xp-circle';
        const color = noteColor(index);
        const size = randomRange(60, 120);

        Object.assign(el.style, {
            width: size + 'px',
            height: size + 'px',
            background: `radial-gradient(circle, ${color} 0%, transparent 70%)`,
            left: randomRange(vw() * 0.15, vw() * 0.85) + 'px',
            top: randomRange(vh() * 0.15, vh() * 0.7) + 'px',
        });

        circlesContainer.appendChild(el);

        gsap.fromTo(el,
            { scale: 0.3, autoAlpha: 0.8 },
            {
                scale: randomRange(2.5, 4),
                autoAlpha: 0,
                duration: randomRange(2, 3),
                ease: 'power1.out',
                onComplete: () => el.remove(),
            }
        );
    }

    function transitionOut() {
        // Fade pads
        if (padsContainer) gsap.to(padsContainer, { autoAlpha: 0, duration: 0.8 });

        // Merge remaining circles to center as a bright flash
        const cx = vw() / 2;
        const cy = vh() / 2;

        const flash = document.createElement('div');
        flash.className = 'xp-circle';
        Object.assign(flash.style, {
            width: '200px',
            height: '200px',
            background: 'radial-gradient(circle, rgba(255,240,224,0.6) 0%, transparent 70%)',
            left: cx + 'px',
            top: cy + 'px',
        });
        circlesContainer.appendChild(flash);

        gsap.fromTo(flash,
            { scale: 0.5, autoAlpha: 0 },
            {
                scale: 4,
                autoAlpha: 0.8,
                duration: 1.2,
                ease: 'power2.out',
                onComplete: () => {
                    gsap.to(flash, {
                        autoAlpha: 0,
                        duration: 0.8,
                        onComplete: () => flash.remove(),
                    });
                },
            }
        );

        setTimeout(() => {
            cleanup();
            next();
        }, 2200);
    }

    document.addEventListener('keydown', onKeyDown);
    document.addEventListener('keyup', onKeyUp);

    function cleanup() {
        aborted = true;
        if (transitionTimer) clearTimeout(transitionTimer);
        document.removeEventListener('keydown', onKeyDown);
        document.removeEventListener('keyup', onKeyUp);
    }

    return cleanup;
}
