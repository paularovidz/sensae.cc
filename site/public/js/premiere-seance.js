/* premiere-seance.js — Snap-scroll immersive experience */
(function () {
  'use strict';

  const gsap = window.gsap;
  const ScrollTrigger = window.ScrollTrigger;
  const ScrollToPlugin = window.ScrollToPlugin;
  if (!gsap || !ScrollTrigger) return;

  gsap.registerPlugin(ScrollTrigger);
  if (ScrollToPlugin) gsap.registerPlugin(ScrollToPlugin);

  /* ---- Mobile detection ---- */
  const isMobile = matchMedia('(pointer: coarse)').matches;

  /* ---- Disable CSS snap — JS-driven snap on all devices ---- */
  var currentSection = 0;
  var animating = false;

  document.documentElement.style.scrollSnapType = 'none';
  document.body.style.scrollSnapType = 'none';
  document.querySelectorAll('.ps-section').forEach(function (s) {
    s.style.scrollSnapAlign = 'none';
  });

  /* ---- Reduced motion check ---- */
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  /* ---- Custom cursor (desktop only) ---- */
  const cursor = document.querySelector('[data-ps-cursor]');
  if (!isMobile && cursor) {
    gsap.set(cursor, { opacity: 1 });
    const setX = gsap.quickSetter(cursor, 'x', 'px');
    const setY = gsap.quickSetter(cursor, 'y', 'px');
    document.addEventListener('mousemove', (e) => {
      setX(e.clientX);
      setY(e.clientY);
    });
  }

  /* ---- Block scroll until interaction ---- */
  let scrollLocked = true;
  document.documentElement.style.overflow = 'hidden';
  document.body.style.overflow = 'hidden';

  function unlockScroll() {
    if (!scrollLocked) return;
    scrollLocked = false;
    document.documentElement.style.overflow = '';
    document.body.style.overflow = '';
    showNav();
    ScrollTrigger.refresh();
  }

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
    if (navLabel) {
      navLabel.style.opacity = index >= 6 ? '0' : '';
    }
  }

  // Nav hidden until scroll is unlocked (after "Commencer" click)
  function showNav() {
    if (nav) nav.classList.add('is-visible');
    if (navDots[0]) navDots[0].classList.add('is-active');
  }

  /* ---- Nav dots: clickable ---- */
  navDots.forEach((dot) => {
    dot.addEventListener('click', () => {
      const index = parseInt(dot.dataset.nav);
      const target = document.querySelector(`[data-ps="${index}"]`);
      if (!target) return;

      if (scrollLocked) {
        unlockScroll();
        window.dispatchEvent(new CustomEvent('immersive-start'));
      }

      currentSection = index; animating = true;
      gsap.to(window, {
        scrollTo: { y: target, offsetY: 0 },
        duration: 0.8,
        ease: 'power2.inOut',
        onComplete: function () { animating = false; },
      });
    });
  });

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
    requestAnimationFrame(driftParticles);
  }

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
  const s0Orb = s0.querySelector('.ps-orb');
  const s0Start = s0.querySelector('.ps-start');

  gsap.set(s0Title, { y: 30 });
  gsap.set(s0Sub, { y: 15 });

  // Entrance animation (time-based, plays once)
  const introTl = gsap.timeline({ delay: 0.3 });
  introTl
    .to(s0Orb, { opacity: 1, duration: 2.5, ease: 'power2.out' })
    .to(s0Title, { opacity: 1, y: 0, duration: 1.8, ease: 'power3.out' }, 0.6)
    .to(s0Sub, { opacity: 1, y: 0, duration: 1.5, ease: 'power3.out' }, 1.4);

  if (s0Start) {
    introTl.to(s0Start, { opacity: 1, duration: 1.2, ease: 'power2.out' }, 2.2);
  }

  // Start button → unlock + smooth scroll to section 1 + audio
  if (s0Start) {
    s0Start.addEventListener('click', () => {
      // Fade out intro content smoothly before scrolling
      gsap.to([s0Title, s0Sub, s0Start], {
        opacity: 0, y: -20, duration: 0.6, ease: 'power2.in', stagger: 0.05,
      });
      gsap.to(s0Orb, { opacity: 0, scale: 1.2, duration: 0.8, ease: 'power2.in' });

      // Unlock and smooth scroll after fade starts
      setTimeout(() => {
        unlockScroll();
        currentSection = 1; animating = true;
        const s1 = document.querySelector('[data-ps="1"]');
        if (s1) {
          gsap.to(window, {
            scrollTo: { y: s1, offsetY: 0 },
            duration: 1.2,
            ease: 'power3.inOut',
            onComplete: function () { animating = false; },
          });
        }
      }, 300);

      window.dispatchEvent(new CustomEvent('immersive-start'));
    });
  }

  // Section 0 nav tracking + re-entrance
  ScrollTrigger.create({
    trigger: s0,
    start: 'top 60%',
    end: 'bottom 40%',
    onEnter: () => setActiveNav(0),
    onEnterBack: () => {
      setActiveNav(0);
      gsap.to(s0Orb, { opacity: 1, scale: 1, duration: 1, ease: 'power2.out' });
      gsap.to(s0Title, { opacity: 1, y: 0, duration: 0.8, ease: 'power3.out' });
      gsap.to(s0Sub, { opacity: 1, y: 0, duration: 0.8, ease: 'power3.out', delay: 0.1 });
      if (s0Start) gsap.to(s0Start, { opacity: 1, duration: 0.6, ease: 'power2.out', delay: 0.2 });
    },
  });

  /* ---- Helper: section with entrance animation ---- */
  function setupSection(index, buildTimeline) {
    const section = document.querySelector(`[data-ps="${index}"]`);
    if (!section) return;

    const tl = gsap.timeline({ paused: true });
    buildTimeline(section, tl);

    ScrollTrigger.create({
      trigger: section,
      start: 'top 80%',
      end: 'bottom 20%',
      onEnter: () => { setActiveNav(index); tl.invalidate().restart(); },
      onEnterBack: () => { setActiveNav(index); tl.invalidate().restart(); },
      onLeave: () => tl.pause(0),
      onLeaveBack: () => tl.pause(0),
    });
  }

  /* ---- Section 1 — Accueil ---- */
  setupSection(1, (section, tl) => {
    const num = section.querySelector('.ps-num');
    const title = section.querySelector('.ps-title');
    const lines = section.querySelectorAll('.ps-line');
    const small = section.querySelector('.ps-small');
    const orb = section.querySelector('.ps-orb');

    tl
      .fromTo(num, { opacity: 0 }, { opacity: 1, duration: 0.8, ease: 'power2.out' }, 0)
      .fromTo(orb, { opacity: 0, scale: 0.95 }, { opacity: 1, scale: 1.05, duration: 1.4, ease: 'power2.out' }, 0)
      .fromTo(title, { opacity: 0, y: 25 }, { opacity: 1, y: 0, duration: 1, ease: 'power3.out' }, 0.2)
      .fromTo(lines[0], { opacity: 0, y: 15 }, { opacity: 1, y: 0, duration: 0.8, ease: 'power2.out' }, 0.5)
      .fromTo(lines[1], { opacity: 0, y: 15 }, { opacity: 1, y: 0, duration: 0.8, ease: 'power2.out' }, 0.7)
      .fromTo(lines[2], { opacity: 0, y: 15 }, { opacity: 1, y: 0, duration: 0.8, ease: 'power2.out' }, 0.9)
      .fromTo(small, { opacity: 0 }, { opacity: 1, duration: 0.6, ease: 'power2.out' }, 1.15);
  });

  /* ---- Section 2 — Découverte ---- */
  setupSection(2, (section, tl) => {
    const num = section.querySelector('.ps-num');
    const title = section.querySelector('.ps-title');
    const items = section.querySelectorAll('[data-ps-item]');
    const introLine = section.querySelector('.ps-section-inner > .ps-line');
    const lastLines = section.querySelectorAll('.ps-line:not([data-ps-item])');
    const orbs = section.querySelectorAll('.ps-orb');

    tl
      .fromTo(num, { opacity: 0 }, { opacity: 1, duration: 0.8, ease: 'power2.out' }, 0)
      .fromTo(orbs, { opacity: 0 }, { opacity: 1, duration: 1.2, stagger: 0.2, ease: 'power2.out' }, 0)
      .fromTo(title, { opacity: 0, y: 25 }, { opacity: 1, y: 0, duration: 1, ease: 'power3.out' }, 0.15);

    if (introLine) {
      tl.fromTo(introLine, { opacity: 0, y: 12 }, { opacity: 1, y: 0, duration: 0.8, ease: 'power2.out' }, 0.4);
    }

    items.forEach((item, i) => {
      tl.fromTo(item, { opacity: 0, y: 10 }, { opacity: 1, y: 0, duration: 0.6, ease: 'power2.out' }, 0.6 + i * 0.12);
    });

    if (lastLines.length > 1) {
      tl.fromTo(lastLines[lastLines.length - 1], { opacity: 0, y: 12 }, { opacity: 1, y: 0, duration: 0.8, ease: 'power2.out' }, 1.5);
    }
  });

  /* ---- Section 3 — Exploration ---- */
  setupSection(3, (section, tl) => {
    const num = section.querySelector('.ps-num');
    const title = section.querySelector('.ps-title');
    const cards = section.querySelectorAll('.ps-duo-card');
    const sub = section.querySelector('.ps-subtitle');
    const orb = section.querySelector('.ps-orb');

    tl
      .fromTo(num, { opacity: 0 }, { opacity: 1, duration: 0.8, ease: 'power2.out' }, 0)
      .fromTo(orb, { opacity: 0 }, { opacity: 1, duration: 1.2, ease: 'power2.out' }, 0)
      .fromTo(title, { opacity: 0, y: 25 }, { opacity: 1, y: 0, duration: 1, ease: 'power3.out' }, 0.15)
      .fromTo(cards[0], { opacity: 0, scale: 0.7 }, { opacity: 1, scale: 1, duration: 0.9, ease: 'back.out(1.3)' }, 0.45)
      .fromTo(cards[1], { opacity: 0, scale: 0.7 }, { opacity: 1, scale: 1, duration: 0.9, ease: 'back.out(1.3)' }, 0.7)
      .fromTo(sub, { opacity: 0 }, { opacity: 1, duration: 0.7, ease: 'power2.out' }, 1.0);
  });

  /* ---- Section 4 — Apaisement ---- */
  setupSection(4, (section, tl) => {
    const num = section.querySelector('.ps-num');
    const title = section.querySelector('.ps-title');
    const lines = section.querySelectorAll('.ps-line');
    const pulse = section.querySelector('.ps-pulse');
    const pulseOuter = section.querySelector('.ps-pulse-outer');

    // Continuous breathing pulse (always running)
    if (pulse) {
      gsap.to(pulse, { scale: 1.15, duration: 4, ease: 'sine.inOut', repeat: -1, yoyo: true });
    }
    if (pulseOuter) {
      gsap.to(pulseOuter, { scale: 1.08, duration: 5, ease: 'sine.inOut', repeat: -1, yoyo: true, delay: 0.5 });
    }

    tl
      .fromTo(num, { opacity: 0 }, { opacity: 1, duration: 0.8, ease: 'power2.out' }, 0)
      .fromTo(pulse || {}, { opacity: 0 }, { opacity: 0.5, duration: 1.2, ease: 'power2.out' }, 0)
      .fromTo(pulseOuter || {}, { opacity: 0 }, { opacity: 0.3, duration: 1.2, ease: 'power2.out' }, 0.15)
      .fromTo(title, { opacity: 0, y: 25 }, { opacity: 1, y: 0, duration: 1, ease: 'power3.out' }, 0.3)
      .fromTo(lines[0], { opacity: 0, y: 15 }, { opacity: 1, y: 0, duration: 0.8, ease: 'power2.out' }, 0.6)
      .fromTo(lines[1], { opacity: 0, y: 15 }, { opacity: 1, y: 0, duration: 0.8, ease: 'power2.out' }, 0.85)
      .fromTo(lines[2], { opacity: 0, y: 15 }, { opacity: 1, y: 0, duration: 0.8, ease: 'power2.out' }, 1.1);
  });

  /* ---- Section 5 — Retour ---- */
  setupSection(5, (section, tl) => {
    const num = section.querySelector('.ps-num');
    const title = section.querySelector('.ps-title');
    const lines = section.querySelectorAll('.ps-line');
    const pills = section.querySelectorAll('.ps-pill');
    const orb = section.querySelector('.ps-orb');

    tl
      .fromTo(num, { opacity: 0 }, { opacity: 1, duration: 0.8, ease: 'power2.out' }, 0)
      .fromTo(orb, { opacity: 0, scale: 0.95 }, { opacity: 1, scale: 1.05, duration: 1.4, ease: 'power2.out' }, 0)
      .fromTo(title, { opacity: 0, y: 25 }, { opacity: 1, y: 0, duration: 1, ease: 'power3.out' }, 0.15)
      .fromTo(lines[0], { opacity: 0, y: 15 }, { opacity: 1, y: 0, duration: 0.8, ease: 'power2.out' }, 0.4)
      .fromTo(lines[1], { opacity: 0, y: 15 }, { opacity: 1, y: 0, duration: 0.8, ease: 'power2.out' }, 0.6);

    pills.forEach((pill, i) => {
      tl.fromTo(pill, { opacity: 0, y: 12 }, { opacity: 1, y: 0, duration: 0.6, ease: 'power2.out' }, 0.85 + i * 0.15);
    });
  });

  /* ---- Section 6 — Closing ---- */
  setupSection(6, (section, tl) => {
    const title = section.querySelector('.ps-title');
    const sub = section.querySelector('.ps-subtitle');
    const cta = section.querySelector('.ps-cta');
    const orb = section.querySelector('.ps-orb');

    tl
      .fromTo(orb, { opacity: 0 }, { opacity: 1, duration: 1.4, ease: 'power2.out' }, 0)
      .fromTo(title, { opacity: 0, y: 25 }, { opacity: 1, y: 0, duration: 1.2, ease: 'power3.out' }, 0.15)
      .fromTo(sub, { opacity: 0 }, { opacity: 1, duration: 0.9, ease: 'power2.out' }, 0.6)
      .fromTo(cta, { opacity: 0 }, { opacity: 1, duration: 0.8, ease: 'power2.out' }, 1.1);
  });

  /* ---- JS-driven section snap (all devices) ---- */
  var allSections = gsap.utils.toArray('.ps-section');

  function goToSection(index) {
    if (animating || index < 0 || index >= allSections.length) return;
    animating = true;
    currentSection = index;
    gsap.to(window, {
      scrollTo: { y: allSections[index], offsetY: 0 },
      duration: 0.8,
      ease: 'power2.inOut',
      onComplete: function () {
        animating = false;
        ScrollTrigger.refresh();
      },
    });
  }

  if (isMobile) {
    /* ---- Mobile: touch-driven snap ---- */
    var touchStartY = 0;

    document.addEventListener('touchstart', function (e) {
      if (scrollLocked) return;
      touchStartY = e.touches[0].clientY;
    }, { passive: true });

    document.addEventListener('touchmove', function (e) {
      if (!scrollLocked) e.preventDefault();
    }, { passive: false });

    document.addEventListener('touchend', function (e) {
      if (scrollLocked || animating) return;
      var deltaY = touchStartY - e.changedTouches[0].clientY;
      if (deltaY > 40) goToSection(currentSection + 1);
      else if (deltaY < -40) goToSection(currentSection - 1);
    }, { passive: true });

  } else {
    /* ---- Desktop: wheel + keyboard snap ---- */
    var wheelReady = true;

    document.addEventListener('wheel', function (e) {
      if (scrollLocked) return;
      e.preventDefault();
      if (animating || !wheelReady) return;

      wheelReady = false;
      setTimeout(function () { wheelReady = true; }, 1000);

      if (e.deltaY > 0) goToSection(currentSection + 1);
      else if (e.deltaY < 0) goToSection(currentSection - 1);
    }, { passive: false });

    document.addEventListener('keydown', function (e) {
      if (scrollLocked || animating) return;
      if (e.key === 'ArrowDown' || e.key === 'PageDown' || e.key === ' ') {
        e.preventDefault();
        goToSection(currentSection + 1);
      } else if (e.key === 'ArrowUp' || e.key === 'PageUp') {
        e.preventDefault();
        goToSection(currentSection - 1);
      }
    });
  }

})();
