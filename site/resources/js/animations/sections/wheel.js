import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { DURATION, EASE, SCROLL } from '../config';
import { prefersReducedMotion } from '../utils/reduced-motion';

const WHEEL = {
  orbitDuration: 30,
  glowDuration: 6,
  entranceDuration: 1.2,
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

  // Position active bubbles along the circular path
  function positionBubbles(progress) {
    allBubbles.forEach((bubble, i) => {
      if (i >= activeCount) return;
      const angle = progress * Math.PI * 2 + (i / activeCount) * Math.PI * 2;
      gsap.set(bubble, {
        x: orbitR * Math.cos(angle),
        y: -orbitR * Math.sin(angle),
      });
    });
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

  // Glow — animate the conic-gradient starting angle
  let glowTween;
  if (glow) {
    const glowObj = { angle: 0 };
    glowTween = gsap.to(glowObj, {
      angle: 360,
      duration: WHEEL.glowDuration,
      repeat: -1,
      ease: 'none',
      paused: true,
      onUpdate: () => {
        glow.style.setProperty('--glow-angle', `${glowObj.angle}deg`);
      },
    });
  }

  // Entrance timeline
  const entranceTl = gsap.timeline({
    scrollTrigger: {
      trigger: section,
      start: SCROLL.startEager,
      once: true,
    },
    onComplete: () => {
      orbitTween.play();
      if (glowTween) glowTween.play();
    },
  });

  entranceTl.fromTo(container, { opacity: 0 }, {
    opacity: 1,
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
    duration: 0.6,
    stagger: WHEEL.bubbleStagger,
    ease: 'back.out(1.4)',
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
      if (glowTween) glowTween.play();
    } else {
      orbitTween.pause();
      if (glowTween) glowTween.pause();
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
