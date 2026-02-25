/**
 * Acte Insight — Revelation
 * "We just stimulated your senses through a screen.
 *  Imagine a full session at sensaë."
 * Progressive text reveal, pause, then transition.
 */

import { isMobile } from '../utils.js';

const PAUSE_AFTER_REVEAL = 4000; // 4s to let it sink in

export function actInsight(gsap, audio, next) {
    const canvas = document.querySelector('[data-xp-act="insight"]');
    const line1 = document.querySelector('[data-xp-insight="1"]');
    const line2 = document.querySelector('[data-xp-insight="2"]');
    const line3 = document.querySelector('[data-xp-insight="3"]');
    if (!canvas) return;

    let aborted = false;

    // Keep drone going softly
    if (audio) audio.setDroneVolume(0.06, 2);

    const device = isMobile() ? 'un téléphone' : 'un écran';
    if (line2) line2.textContent = `À travers ${device}.`;

    const tl = gsap.timeline();

    tl.to(line1, { autoAlpha: 1, duration: 1.5, ease: 'power2.out' }, 0.5)
      .to(line2, { autoAlpha: 1, duration: 1.2, ease: 'power2.out' }, 2.2)
      .to(line3, { autoAlpha: 1, duration: 1.5, ease: 'power2.out' }, 4)
      .to([line1, line2, line3], { autoAlpha: 0, duration: 1.2, ease: 'power2.inOut' }, `+=${PAUSE_AFTER_REVEAL / 1000}`);

    const totalDuration = 4000 + 1500 + PAUSE_AFTER_REVEAL + 1200 + 800;
    const timer = setTimeout(() => {
        if (!aborted) {
            cleanup();
            next();
        }
    }, totalDuration);

    function cleanup() {
        aborted = true;
        clearTimeout(timer);
        tl.kill();
    }

    return cleanup;
}
