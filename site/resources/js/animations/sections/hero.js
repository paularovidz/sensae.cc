import gsap from 'gsap';
import { EASE } from '../config';

export function initHero() {
  const section = document.querySelector('[data-hero]');
  if (!section) return;

  const h1 = section.querySelector('[data-hero-h1]');
  const title = section.querySelector('[data-hero-title]');
  const subtitle = section.querySelector('[data-hero-subtitle]');
  const cta = section.querySelector('[data-hero-cta]');
  const heroImage = section.querySelector('[data-hero-image]');
  const heroGlow = heroImage?.querySelector('[data-hero-image-glow]');

  // Hide via GSAP (autoAlpha = visibility:hidden + opacity:0)
  const text = [h1, title, subtitle, cta].filter(Boolean);
  gsap.set(text, { autoAlpha: 0, y: 12 });
  if (heroImage) gsap.set(heroImage, { autoAlpha: 0, y: 20, scale: 0.98 });
  if (heroGlow) gsap.set(heroGlow, { autoAlpha: 0 });

  const tl = gsap.timeline({ defaults: { ease: EASE.hero } });

  // Text block — tight overlap, all visible within ~0.5s
  if (h1)       tl.to(h1,       { autoAlpha: 1, y: 0, duration: 0.5 }, 0);
  if (title)    tl.to(title,    { autoAlpha: 1, y: 0, duration: 0.6 }, 0.1);
  if (subtitle) tl.to(subtitle, { autoAlpha: 1, y: 0, duration: 0.5 }, 0.15);
  if (cta)      tl.to(cta,      { autoAlpha: 1, y: 0, duration: 0.5 }, 0.2);

  // Image (LCP) — starts almost immediately
  if (heroImage) {
    tl.to(heroImage, {
      autoAlpha: 1, y: 0, scale: 1,
      duration: 0.8, ease: EASE.reveal,
    }, 0.15);

    if (heroGlow) {
      tl.to(heroGlow, {
        autoAlpha: 1,
        duration: 1.2,
        ease: 'power1.inOut',
      }, 0.4);
    }
  }
}
