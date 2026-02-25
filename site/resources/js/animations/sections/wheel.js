import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { DURATION, EASE, SCROLL } from '../config';
import { prefersReducedMotion } from '../utils/reduced-motion';

const WHEEL = {
  orbitDuration: 30,
  entranceDuration: 1.4,
  bubbleStagger: 0.04,
};

export function initWheel() {
  const section = document.querySelector('[data-wheel]');
  if (!section) return;

  const container = section.querySelector('[data-wheel-container]');
  const orbit = section.querySelector('[data-wheel-orbit]');
  const bubbles = section.querySelectorAll('[data-wheel-bubble]');
  const glow = section.querySelector('[data-wheel-glow]');
  const fallback = section.querySelector('[data-wheel-fallback]');
  const hasLinks = container?.dataset.wheelLinks === '1';

  if (!container || !orbit || !bubbles.length) return;

  if (prefersReducedMotion()) {
    container.style.display = 'none';
    if (fallback) fallback.style.display = 'block';
    return;
  }

  // Read circular orbit radius from CSS custom property
  const allBubbles = Array.from(bubbles);
  const totalCount = allBubbles.length;
  const maxTablet = parseInt(container.dataset.wheelMaxTablet) || totalCount;
  const maxMobile = parseInt(container.dataset.wheelMaxMobile) || totalCount;
  let orbitR;
  let activeCount = totalCount;

  // Pre-cache transform writers — avoids gsap.set() overhead per frame
  const setX = allBubbles.map(b => gsap.quickSetter(b, 'x', 'px'));
  const setY = allBubbles.map(b => gsap.quickSetter(b, 'y', 'px'));

  function readOrbitDimensions() {
    const raw = getComputedStyle(container).getPropertyValue('--wh-orbit').trim();
    if (raw.endsWith('vw')) {
      orbitR = (parseFloat(raw) / 100) * window.innerWidth;
    } else if (raw.endsWith('vh')) {
      orbitR = (parseFloat(raw) / 100) * window.innerHeight;
    } else {
      orbitR = parseFloat(raw);
    }

    // Determine active bubble count based on viewport
    const w = window.innerWidth;
    const newCount = w <= 640 ? maxMobile : w <= 1250 ? maxTablet : totalCount;

    if (newCount !== activeCount) {
      activeCount = newCount;
      allBubbles.forEach((b, i) => {
        b.style.display = i < activeCount ? '' : 'none';
      });
    }
  }

  readOrbitDimensions();

  // Position active bubbles along the circular path (using quickSetters)
  const TWO_PI = Math.PI * 2;
  function positionBubbles(progress) {
    const baseAngle = progress * TWO_PI;
    const step = TWO_PI / activeCount;
    for (let i = 0; i < activeCount; i++) {
      const angle = baseAngle + i * step;
      setX[i](orbitR * Math.cos(angle));
      setY[i](-orbitR * Math.sin(angle));
    }
  }

  positionBubbles(0);

  // Circular orbit — animate a progress value, reposition on each frame
  const orbitProgress = { value: 0 };
  const orbitTween = gsap.to(orbitProgress, {
    value: 1,
    duration: WHEEL.orbitDuration,
    repeat: -1,
    ease: 'none',
    paused: true,
    onUpdate: () => positionBubbles(orbitProgress.value),
  });

  // Glow — animated via pure CSS @keyframes (no JS needed)

  // Entrance timeline
  const entranceTl = gsap.timeline({
    scrollTrigger: {
      trigger: section,
      start: SCROLL.startEager,
      once: true,
    },
    onComplete: () => {
      orbitTween.play();
    },
  });

  entranceTl.fromTo(container, { autoAlpha: 0 }, {
    autoAlpha: 1,
    duration: DURATION.slow,
    ease: EASE.reveal,
  });

  const center = section.querySelector('[data-wheel-center]');
  if (center) {
    entranceTl.fromTo(center, { scale: 0.6, opacity: 0 }, {
      scale: 1, opacity: 1,
      duration: WHEEL.entranceDuration,
      ease: EASE.reveal,
      onComplete: () => gsap.set(center, { clearProps: 'all' }),
    }, '<');
  }

  // Start CSS rotation immediately so it's already spinning when it fades in
  if (glow) glow.classList.add('wheel-glow-active');

  // Glow fades in after center appears
  if (glow) {
    entranceTl.fromTo(glow, { opacity: 0 }, {
      opacity: 1,
      duration: 1,
      ease: EASE.reveal,
    }, '-=0.4');
  }

  entranceTl.fromTo(allBubbles.slice(0, activeCount), { scale: 0, opacity: 0 }, {
    scale: 1, opacity: 1,
    duration: 0.8,
    stagger: WHEEL.bubbleStagger,
    ease: EASE.reveal,
  }, '-=0.6');

  // Hover pause (only when links mode)
  if (hasLinks) {
    allBubbles.forEach(bubble => {
      bubble.addEventListener('mouseenter', () => {
        gsap.to(orbitTween, { timeScale: 0, duration: 0.5, ease: EASE.smooth });
      });
      bubble.addEventListener('mouseleave', () => {
        gsap.to(orbitTween, { timeScale: 1, duration: 0.8, ease: EASE.smooth });
      });
    });
  }

  // Pause when off-screen
  const observer = new IntersectionObserver(([entry]) => {
    if (entry.isIntersecting) {
      orbitTween.play();
    } else {
      orbitTween.pause();
    }
  }, { threshold: 0.1 });
  observer.observe(section);

  // Re-read orbit dimensions on resize (immediate), debounce only ScrollTrigger
  let resizeTimer;
  const onResize = () => {
    readOrbitDimensions();
    positionBubbles(orbitProgress.value);
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => ScrollTrigger.refresh(), 300);
  };
  window.addEventListener('resize', onResize);
}
