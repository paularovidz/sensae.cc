import gsap from 'gsap';
import { EASE, STAGGER } from '../config';
import { splitIntoWords } from '../utils/split-text';

export function initHero() {
  const title = document.querySelector('[data-hero-title]');
  if (!title) return;

  const h1 = document.querySelector('[data-hero-h1]');
  const subtitle = document.querySelector('[data-hero-subtitle]');
  const cta = document.querySelector('[data-hero-cta]');
  const heroImage = document.querySelector('[data-hero-image]');
  const heroGlow = heroImage?.querySelector('[data-hero-image-glow]');

  const tl = gsap.timeline({ defaults: { ease: EASE.hero } });

  // H1 small label
  if (h1) {
    tl.fromTo(h1, {
      y: 10,
      opacity: 0,
    }, {
      y: 0,
      opacity: 1,
      duration: 0.6,
      onComplete: () => gsap.set(h1, { clearProps: 'all' }),
    });
  }

  // Split title into words and animate
  const words = splitIntoWords(title);
  tl.fromTo(words, {
    y: 40,
    opacity: 0,
  }, {
    y: 0,
    opacity: 1,
    duration: 0.9,
    stagger: STAGGER.words,
  }, h1 ? '-=0.3' : '0');

  if (subtitle) {
    tl.fromTo(subtitle, {
      y: 20,
      opacity: 0,
    }, {
      y: 0,
      opacity: 1,
      duration: 0.7,
    }, '-=0.4');
  }

  if (cta) {
    tl.fromTo(cta.children, {
      y: 8,
      opacity: 0,
    }, {
      y: 0,
      opacity: 1,
      duration: 0.6,
      stagger: 0.1,
    }, '-=0.2');
  }

  if (heroImage) {
    tl.fromTo(heroImage, {
      y: 40,
      opacity: 0,
      scale: 0.97,
    }, {
      y: 0,
      opacity: 1,
      scale: 1,
      duration: 1,
      ease: EASE.reveal || 'power2.out',
      onComplete: () => gsap.set(heroImage, { clearProps: 'transform' }),
    }, '-=0.5');

    if (heroGlow) {
      tl.fromTo(heroGlow, {
        opacity: 0,
      }, {
        opacity: 1,
        duration: 1.2,
        ease: 'power1.inOut',
      }, '-=0.6');

      const glowObj = { angle: 0 };
      gsap.to(glowObj, {
        angle: 360,
        duration: 8,
        repeat: -1,
        ease: 'none',
        onUpdate: () => {
          heroGlow.style.setProperty('--hero-glow-angle', `${glowObj.angle}deg`);
        },
      });
    }
  }
}
