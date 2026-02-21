import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { prefersReducedMotion } from './utils/reduced-motion';
import { EASE } from './config';

import { initHeader } from './core/header';
import { initScrollReveals } from './core/scroll-reveal';
import { initMagnetic } from './core/magnetic';

import { initHero } from './sections/hero';
import { initCards } from './sections/cards';
import { initFaq } from './sections/faq';
import { initFooter } from './sections/footer';
import { initWheel } from './sections/wheel';

gsap.registerPlugin(ScrollTrigger);

gsap.defaults({
  ease: EASE.reveal,
  overwrite: 'auto',
});

export function initAnimations() {
  if (prefersReducedMotion()) {
    gsap.set('[data-animate], [data-animate-grid] > *, [data-hero-title] .gsap-word, [data-wheel-container]', {
      opacity: 1, y: 0, x: 0, scale: 1,
    });
    return;
  }

  initHeader();
  initScrollReveals();
  initMagnetic();

  initHero();
  initCards();
  initWheel();
  initFaq();
  initFooter();
}
