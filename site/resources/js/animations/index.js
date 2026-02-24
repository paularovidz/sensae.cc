import gsap from 'gsap';
import { prefersReducedMotion } from './utils/reduced-motion';
import { EASE } from './config';
import { initHero } from './sections/hero';

gsap.defaults({
  ease: EASE.reveal,
  overwrite: 'auto',
});

export function initAnimations() {
  if (prefersReducedMotion()) {
    gsap.set('[data-animate], [data-animate-grid] > *, [data-wheel-container], [data-hero-h1], [data-hero-title], [data-hero-subtitle], [data-hero-cta], [data-hero-image]', {
      autoAlpha: 1, y: 0, x: 0, scale: 1,
    });
    return;
  }

  // Hero runs immediately — above the fold, no ScrollTrigger needed
  initHero();

  // Defer scroll-based animations to free the main thread for the hero entrance
  const initDeferred = async () => {
    const { ScrollTrigger } = await import('gsap/ScrollTrigger');
    gsap.registerPlugin(ScrollTrigger);

    const [
      { initHeader },
      { initScrollReveals },
      { initMagnetic },
      { initCards },
      { initWheel },
      { initFooter },
    ] = await Promise.all([
      import('./core/header'),
      import('./core/scroll-reveal'),
      import('./core/magnetic'),
      import('./sections/cards'),
      import('./sections/wheel'),
      import('./sections/footer'),
    ]);

    initHeader();
    initScrollReveals();
    initMagnetic();
    initCards();
    initWheel();
    initFooter();
  };

  // Wait one frame so the hero timeline starts painting, then load the rest
  requestAnimationFrame(() => initDeferred());
}
