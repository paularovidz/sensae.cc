/* premiere-seance.js — Scroll-driven immersive experience */
(function () {
  'use strict';

  const gsap = window.gsap;
  const ScrollTrigger = window.ScrollTrigger;
  if (!gsap || !ScrollTrigger) return;

  gsap.registerPlugin(ScrollTrigger);

  /* ---- Reduced motion check ---- */
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  /* ---- Right-side nav indicator ---- */
  const nav = document.querySelector('.ps-nav');
  const navDots = document.querySelectorAll('.ps-nav-dot');
  const navLabel = document.querySelector('.ps-nav-label');
  let activeSection = 0;

  function setActiveNav(index) {
    if (index === activeSection) return;
    activeSection = index;
    navDots.forEach((dot, i) => {
      dot.classList.toggle('is-active', i === index);
    });
    // Hide label on last section (CTA visible)
    if (navLabel) {
      navLabel.style.opacity = index >= 6 ? '0' : '';
    }
  }

  // Show nav after a short delay
  setTimeout(() => {
    if (nav) nav.classList.add('is-visible');
    if (navDots[0]) navDots[0].classList.add('is-active');
  }, 2500);

  /* ---- Particles (shared, always drifting) ---- */
  const PARTICLE_COUNT = 25;
  const particles = [];
  const particleContainer = document.createElement('div');
  particleContainer.style.cssText = 'position:fixed;inset:0;pointer-events:none;z-index:3;';
  document.body.appendChild(particleContainer);

  for (let i = 0; i < PARTICLE_COUNT; i++) {
    const el = document.createElement('div');
    const size = 2 + Math.random() * 3;
    el.className = 'ps-particle';
    el.style.cssText = `width:${size}px;height:${size}px;background:rgba(210,153,255,${0.15 + Math.random() * 0.2});left:${Math.random() * 100}vw;top:${Math.random() * 100}vh;`;
    particleContainer.appendChild(el);
    particles.push({
      el,
      x: Math.random() * window.innerWidth,
      y: Math.random() * window.innerHeight,
      vx: (Math.random() - 0.5) * 0.4,
      vy: (Math.random() - 0.5) * 0.3 - 0.1,
    });
  }

  /* Particle drift loop */
  let driftRaf;
  function driftParticles() {
    const w = window.innerWidth;
    const h = window.innerHeight;
    for (const p of particles) {
      p.x += p.vx;
      p.y += p.vy;
      if (p.x < -10) p.x = w + 10;
      if (p.x > w + 10) p.x = -10;
      if (p.y < -10) p.y = h + 10;
      if (p.y > h + 10) p.y = -10;
      p.el.style.transform = `translate(${p.x}px, ${p.y}px)`;
    }
    driftRaf = requestAnimationFrame(driftParticles);
  }

  /* Fade particles based on scroll visibility */
  gsap.to(particles.map(p => p.el), {
    opacity: 0.6,
    duration: 2,
    stagger: { each: 0.05, from: 'random' },
    delay: 1,
  });

  driftParticles();

  /* ---- Section 0 — Intro ---- */
  const s0 = document.querySelector('[data-ps="0"]');
  const s0Title = s0.querySelector('.ps-title');
  const s0Sub = s0.querySelector('.ps-subtitle');
  const s0Hint = s0.querySelector('.ps-scroll-hint');
  const s0Orb = s0.querySelector('.ps-orb');

  // Entrance animation (not scroll-driven)
  const introTl = gsap.timeline({ delay: 0.3 });
  introTl
    .to(s0Orb, { opacity: 1, duration: 2, ease: 'power2.out' })
    .to(s0Title, { opacity: 1, y: 0, duration: 1.2, ease: 'power3.out' }, 0.5)
    .to(s0Sub, { opacity: 1, duration: 1.2, ease: 'power2.out' }, 1.2)
    .to(s0Hint, { opacity: 1, duration: 1, ease: 'power2.out' }, 2);

  // Section 0 nav tracking
  ScrollTrigger.create({
    trigger: s0,
    start: 'top 60%',
    end: 'bottom 40%',
    onEnter: () => setActiveNav(0),
    onEnterBack: () => setActiveNav(0),
  });

  // Scroll out
  gsap.timeline({
    scrollTrigger: {
      trigger: s0,
      start: 'top top',
      end: 'bottom top',
      scrub: 1,
    },
  })
    .to(s0Title, { y: -60, opacity: 0, ease: 'none' }, 0)
    .to(s0Sub, { y: -40, opacity: 0, ease: 'none' }, 0)
    .to(s0Hint, { opacity: 0, ease: 'none' }, 0)
    .to(s0Orb, { scale: 1.5, opacity: 0, ease: 'none' }, 0);

  /* ---- Helper: create section ScrollTrigger ---- */
  function createSection(index, opts = {}) {
    const section = document.querySelector(`[data-ps="${index}"]`);
    if (!section) return null;

    const pinDuration = opts.pinDuration || '100%';

    const tl = gsap.timeline({
      scrollTrigger: {
        trigger: section,
        start: 'top top',
        end: `+=${pinDuration}`,
        pin: true,
        scrub: 1,
        anticipatePin: 1,
        onEnter: () => setActiveNav(index),
        onEnterBack: () => setActiveNav(index),
      },
    });

    return { section, tl };
  }

  /* ---- Section 1 — Accueil ---- */
  const ctx1 = createSection(1, { pinDuration: '300%' });
  if (ctx1) {
    const { section, tl } = ctx1;
    const num = section.querySelector('.ps-num');
    const title = section.querySelector('.ps-title');
    const lines = section.querySelectorAll('.ps-line');
    const small = section.querySelector('.ps-small');
    const orb = section.querySelector('.ps-orb');

    tl
      // Fade in (0 → 0.25)
      .to(num, { opacity: 1, duration: 0.15, ease: 'none' }, 0)
      .to(orb, { opacity: 1, scale: 1.1, duration: 0.25, ease: 'none' }, 0)
      .to(title, { opacity: 1, duration: 0.1, ease: 'none' }, 0.03)
      .to(lines[0], { opacity: 1, duration: 0.08, ease: 'none' }, 0.08)
      .to(lines[1], { opacity: 1, duration: 0.08, ease: 'none' }, 0.13)
      .to(lines[2], { opacity: 1, duration: 0.08, ease: 'none' }, 0.18)
      .to(small, { opacity: 1, duration: 0.06, ease: 'none' }, 0.22)
      // Hold — content stays fully visible for a long scroll distance
      .to({}, { duration: 0.6 })
      // Fade out (brief)
      .to([title, ...lines, small, num], { opacity: 0, y: -30, duration: 0.1, stagger: 0.01, ease: 'none' })
      .to(orb, { opacity: 0, scale: 1.3, duration: 0.1, ease: 'none' }, '<');
  }

  /* ---- Section 2 — Découverte ---- */
  const ctx2 = createSection(2, { pinDuration: '350%' });
  if (ctx2) {
    const { section, tl } = ctx2;
    const num = section.querySelector('.ps-num');
    const title = section.querySelector('.ps-title');
    const items = section.querySelectorAll('[data-ps-item]');
    const lastLine = section.querySelectorAll('.ps-line:not([data-ps-item])');
    const orbs = section.querySelectorAll('.ps-orb');

    tl
      .to(num, { opacity: 1, duration: 0.1, ease: 'none' }, 0)
      .to(orbs, { opacity: 1, duration: 0.15, stagger: 0.05, ease: 'none' }, 0)
      .to(title, { opacity: 1, duration: 0.08, ease: 'none' }, 0.03);

    // First ps-line (intro text)
    const introLine = section.querySelector('.ps-section-inner > .ps-line');
    if (introLine) {
      tl.to(introLine, { opacity: 1, duration: 0.06, ease: 'none' }, 0.08);
    }

    // Items staggered
    items.forEach((item, i) => {
      tl.to(item, { opacity: 1, duration: 0.05, ease: 'none' }, 0.12 + i * 0.04);
    });

    // Last line
    if (lastLine.length > 1) {
      tl.to(lastLine[lastLine.length - 1], { opacity: 1, duration: 0.06, ease: 'none' }, 0.38);
    }

    // Hold — long pause for reading
    tl.to({}, { duration: 0.5 });

    // Fade out
    tl.to(section.querySelector('.ps-section-inner'), { opacity: 0, y: -30, duration: 0.08, ease: 'none' })
      .to([num, ...orbs], { opacity: 0, duration: 0.08, ease: 'none' }, '<');
  }

  /* ---- Section 3 — Exploration ---- */
  const ctx3 = createSection(3, { pinDuration: '300%' });
  if (ctx3) {
    const { section, tl } = ctx3;
    const num = section.querySelector('.ps-num');
    const title = section.querySelector('.ps-title');
    const cards = section.querySelectorAll('.ps-duo-card');
    const sub = section.querySelector('.ps-subtitle');
    const orb = section.querySelector('.ps-orb');

    tl
      .to(num, { opacity: 1, duration: 0.1, ease: 'none' }, 0)
      .to(orb, { opacity: 1, duration: 0.15, ease: 'none' }, 0)
      .to(title, { opacity: 1, duration: 0.08, ease: 'none' }, 0.03)
      .to(cards[0], { opacity: 1, scale: 1, duration: 0.12, ease: 'back.out(1.4)' }, 0.1)
      .to(cards[1], { opacity: 1, scale: 1, duration: 0.12, ease: 'back.out(1.4)' }, 0.18)
      .to(sub, { opacity: 1, duration: 0.08, ease: 'none' }, 0.26)
      // Hold
      .to({}, { duration: 0.55 })
      // Fade out
      .to(section.querySelector('.ps-section-inner'), { opacity: 0, y: -30, duration: 0.08, ease: 'none' })
      .to([num, orb], { opacity: 0, duration: 0.08, ease: 'none' }, '<');
  }

  /* ---- Section 4 — Apaisement ---- */
  const ctx4 = createSection(4, { pinDuration: '300%' });
  if (ctx4) {
    const { section, tl } = ctx4;
    const num = section.querySelector('.ps-num');
    const title = section.querySelector('.ps-title');
    const lines = section.querySelectorAll('.ps-line');
    const pulse = section.querySelector('.ps-pulse');
    const pulseOuter = section.querySelector('.ps-pulse-outer');

    // Continuous breathing pulse (not scroll-driven)
    gsap.to(pulse, {
      scale: 1.15,
      duration: 4,
      ease: 'sine.inOut',
      repeat: -1,
      yoyo: true,
      paused: false,
    });
    gsap.to(pulseOuter, {
      scale: 1.08,
      duration: 5,
      ease: 'sine.inOut',
      repeat: -1,
      yoyo: true,
      delay: 0.5,
      paused: false,
    });

    tl
      .to(num, { opacity: 1, duration: 0.1, ease: 'none' }, 0)
      .to(pulse, { opacity: 0.5, duration: 0.15, ease: 'none' }, 0)
      .to(pulseOuter, { opacity: 0.3, duration: 0.15, ease: 'none' }, 0.03)
      .to(title, { opacity: 1, duration: 0.08, ease: 'none' }, 0.06)
      .to(lines[0], { opacity: 1, duration: 0.06, ease: 'none' }, 0.12)
      .to(lines[1], { opacity: 1, duration: 0.06, ease: 'none' }, 0.18)
      .to(lines[2], { opacity: 1, duration: 0.06, ease: 'none' }, 0.24)
      // Hold
      .to({}, { duration: 0.55 })
      // Fade everything including pulse
      .to([title, ...lines, num], { opacity: 0, y: -20, duration: 0.08, stagger: 0.01, ease: 'none' })
      .to([pulse, pulseOuter], { opacity: 0, scale: 0.8, duration: 0.1, ease: 'none' }, '<');
  }

  /* ---- Section 5 — Retour ---- */
  const ctx5 = createSection(5, { pinDuration: '300%' });
  if (ctx5) {
    const { section, tl } = ctx5;
    const num = section.querySelector('.ps-num');
    const title = section.querySelector('.ps-title');
    const lines = section.querySelectorAll('.ps-line');
    const pills = section.querySelectorAll('.ps-pill');
    const orb = section.querySelector('.ps-orb');

    tl
      .to(num, { opacity: 1, duration: 0.1, ease: 'none' }, 0)
      .to(orb, { opacity: 1, scale: 1.1, duration: 0.2, ease: 'none' }, 0)
      .to(title, { opacity: 1, duration: 0.08, ease: 'none' }, 0.03)
      .to(lines[0], { opacity: 1, duration: 0.06, ease: 'none' }, 0.09)
      .to(lines[1], { opacity: 1, duration: 0.06, ease: 'none' }, 0.14);

    pills.forEach((pill, i) => {
      tl.to(pill, { opacity: 1, y: 0, duration: 0.05, ease: 'power2.out' }, 0.2 + i * 0.04);
    });

    tl
      // Hold
      .to({}, { duration: 0.55 })
      // Fade out
      .to(section.querySelector('.ps-section-inner'), { opacity: 0, y: -20, duration: 0.08, ease: 'none' })
      .to([num, orb], { opacity: 0, duration: 0.08, ease: 'none' }, '<');
  }

  /* ---- Section 6 — Closing ---- */
  const ctx6 = createSection(6, { pinDuration: '150%' });
  if (ctx6) {
    const { section, tl } = ctx6;
    const title = section.querySelector('.ps-title');
    const sub = section.querySelector('.ps-subtitle');
    const cta = section.querySelector('.ps-cta');
    const orb = section.querySelector('.ps-orb');

    tl
      .to(orb, { opacity: 1, duration: 0.2, ease: 'none' }, 0)
      .to(title, { opacity: 1, duration: 0.15, ease: 'none' }, 0.05)
      .to(sub, { opacity: 1, duration: 0.15, ease: 'none' }, 0.2)
      .to(cta, { opacity: 1, duration: 0.15, ease: 'none' }, 0.35)
      // No fade out — stays visible
      .to({}, { duration: 0.3 });
  }

})();
