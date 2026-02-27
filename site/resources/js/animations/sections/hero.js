import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { EASE } from '../config';

gsap.registerPlugin(ScrollTrigger);

export function initHero() {
  // Hero section text animation (home only)
  const section = document.querySelector('[data-hero]');
  if (section) {
    const h1 = section.querySelector('[data-hero-h1]');
    const title = section.querySelector('[data-hero-title]');
    const subtitle = section.querySelector('[data-hero-subtitle]');
    const cta = section.querySelector('[data-hero-cta]');

    const text = [h1, title, subtitle, cta].filter(Boolean);
    gsap.set(text, { opacity: 0, y: 8 });

    const tl = gsap.timeline({ defaults: { ease: EASE.hero } });
    if (h1)       tl.to(h1,       { opacity: 1, y: 0, duration: 0.7 }, 0);
    if (title)    tl.to(title,    { opacity: 1, y: 0, duration: 0.8 }, 0.1);
    if (subtitle) tl.to(subtitle, { opacity: 1, y: 0, duration: 0.7 }, 0.15);
    if (cta)      tl.to(cta,      { opacity: 1, y: 0, duration: 0.7 }, 0.2);
  }

  // Hero images — reusable, works anywhere on any page
  document.querySelectorAll('[data-hero-image]').forEach(heroImage => {
    const heroGlow = heroImage.querySelector('[data-hero-image-glow]');

    gsap.set(heroImage, { opacity: 0, y: 14, scale: 0.985 });
    if (heroGlow) gsap.set(heroGlow, { opacity: 0 });

    ScrollTrigger.create({
      trigger: heroImage,
      start: 'top 90%',
      once: true,
      onEnter: () => {
        const tl = gsap.timeline();
        tl.to(heroImage, {
          opacity: 1, y: 0, scale: 1,
          duration: 1.0, ease: EASE.reveal,
        });
        if (heroGlow) {
          tl.to(heroGlow, {
            opacity: 1,
            duration: 1.5,
            ease: 'power1.inOut',
          }, 0.25);
        }
      },
    });
  });
}
