import gsap from 'gsap';
import { EASE } from '../config';

export function initFooter() {
  const footer = document.querySelector('[data-footer]');
  if (!footer) return;

  const columns = footer.querySelectorAll('[data-footer-col]');
  const copyright = footer.querySelector('[data-footer-copyright]');

  const tl = gsap.timeline({
    scrollTrigger: {
      trigger: footer,
      start: 'top 90%',
      once: true,
    },
  });

  if (columns.length) {
    tl.fromTo(columns, {
      y: 20,
      opacity: 0,
    }, {
      y: 0,
      opacity: 1,
      duration: 0.8,
      stagger: 0.12,
      ease: EASE.reveal,
    });
  }

  if (copyright) {
    tl.fromTo(copyright, {
      opacity: 0,
    }, {
      opacity: 1,
      duration: 0.6,
      ease: 'power2.out',
    }, '-=0.3');
  }
}
