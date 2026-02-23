import { ScrollTrigger } from 'gsap/ScrollTrigger';

export function initHeader() {
  const header = document.querySelector('[data-header]');
  if (!header) return;

  let lastY = 0;

  ScrollTrigger.create({
    start: 0,
    end: 'max',
    onUpdate: (self) => {
      const y = self.scroll();

      if (y <= 0) {
        header.classList.remove('scroll-down', 'scroll-up');
        lastY = 0;
        return;
      }

      if (y > lastY) {
        header.classList.add('scroll-down');
        header.classList.remove('scroll-up');
      } else if (y < lastY) {
        header.classList.add('scroll-up');
        header.classList.remove('scroll-down');
      }

      lastY = y;
    },
  });
}
