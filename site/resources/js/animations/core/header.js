import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

export function initHeader() {
  const header = document.querySelector('[data-header]');
  if (!header) return;

  let lastY = 0;
  let hidden = false;

  ScrollTrigger.create({
    start: 0,
    end: 'max',
    onUpdate: (self) => {
      const y = self.scroll();

      // Backdrop blur after 80px
      header.classList.toggle('header-scrolled', y > 80);

      // Hide/show after 200px
      if (y < 200) {
        if (hidden) {
          gsap.to(header, { yPercent: 0, duration: 0.4, ease: 'power2.inOut' });
          hidden = false;
        }
        lastY = y;
        return;
      }

      if (y > lastY && !hidden) {
        gsap.to(header, { yPercent: -100, duration: 0.4, ease: 'power2.inOut' });
        hidden = true;
      } else if (y < lastY && hidden) {
        gsap.to(header, { yPercent: 0, duration: 0.4, ease: 'power2.inOut' });
        hidden = false;
      }

      lastY = y;
    },
  });
}
