import gsap from 'gsap';
import { DURATION, EASE, SCROLL } from '../config';

const ANIMATION_MAP = {
  'fade-up':    { y: 30, opacity: 0 },
  'fade-in':    { opacity: 0 },
  'fade-left':  { x: -30, opacity: 0 },
  'fade-right': { x: 30, opacity: 0 },
  'scale-in':   { scale: 0.95, opacity: 0 },
};

const TO_MAP = {
  'fade-up':    { y: 0, opacity: 1 },
  'fade-in':    { opacity: 1 },
  'fade-left':  { x: 0, opacity: 1 },
  'fade-right': { x: 0, opacity: 1 },
  'scale-in':   { scale: 1, opacity: 1 },
};

export function initScrollReveals() {
  gsap.utils.toArray('[data-animate]').forEach(el => {
    const type = el.dataset.animate;
    const delay = parseFloat(el.dataset.animateDelay) || 0;
    const duration = parseFloat(el.dataset.animateDuration) || DURATION.normal;
    const from = ANIMATION_MAP[type] || ANIMATION_MAP['fade-up'];
    const to = TO_MAP[type] || TO_MAP['fade-up'];

    gsap.fromTo(el, from, {
      ...to,
      duration,
      delay,
      ease: EASE.reveal,
      scrollTrigger: {
        trigger: el,
        start: SCROLL.start,
        once: true,
      },
    });
  });
}
