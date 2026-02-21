import gsap from 'gsap';
import { EASE, STAGGER } from '../config';
import { splitIntoWords } from '../utils/split-text';

export function initHero() {
  const title = document.querySelector('[data-hero-title]');
  if (!title) return;

  const subtitle = document.querySelector('[data-hero-subtitle]');
  const cta = document.querySelector('[data-hero-cta]');

  const tl = gsap.timeline({ defaults: { ease: EASE.hero } });

  // Split h1 into words and animate
  const words = splitIntoWords(title);
  tl.fromTo(words, {
    y: 40,
    opacity: 0,
  }, {
    y: 0,
    opacity: 1,
    duration: 0.9,
    stagger: STAGGER.words,
  });

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
      y: 20,
      opacity: 0,
    }, {
      y: 0,
      opacity: 1,
      duration: 0.6,
      stagger: 0.1,
    }, '-=0.3');
  }
}
