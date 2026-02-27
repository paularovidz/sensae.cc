import gsap from 'gsap';
import { DURATION, EASE, STAGGER, SCROLL } from '../config';

export function initCards() {
  initCardGrids();
  initCardTilt();
}

function initCardGrids() {
  gsap.utils.toArray('[data-animate-grid]').forEach(grid => {
    const items = grid.children;
    if (!items.length) return;

    gsap.fromTo(items, {
      y: 24,
      opacity: 0,
    }, {
      y: 0,
      opacity: 1,
      duration: DURATION.normal,
      stagger: STAGGER.cards,
      ease: EASE.reveal,
      scrollTrigger: {
        trigger: grid,
        start: SCROLL.startEager,
        once: true,
      },
    });
  });
}

function initCardTilt() {
  if (window.innerWidth < 768) return;

  const maxTilt = 3;

  document.querySelectorAll('[data-tilt]').forEach(card => {
    card.addEventListener('mousemove', (e) => {
      const rect = card.getBoundingClientRect();
      const x = (e.clientX - rect.left) / rect.width - 0.5;
      const y = (e.clientY - rect.top) / rect.height - 0.5;

      gsap.to(card, {
        rotateY: x * maxTilt,
        rotateX: -y * maxTilt,
        duration: 0.4,
        ease: 'power2.out',
        transformPerspective: 1000,
      });
    });

    card.addEventListener('mouseleave', () => {
      gsap.to(card, {
        rotateY: 0,
        rotateX: 0,
        duration: 0.6,
        ease: 'power2.out',
      });
    });
  });
}
