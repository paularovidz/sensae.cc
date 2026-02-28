import gsap from 'gsap';
import { ScrollToPlugin } from 'gsap/ScrollToPlugin';
gsap.registerPlugin(ScrollToPlugin);
// GSAP Animations — run first, hero is above the fold
import { initAnimations } from './animations/index';

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}

function init() {
    initAnimations();
    initMobileMenu();
    initDesktopDropdowns();
    initAvailabilityBadge();
}

// Mobile menu — fullscreen clip-path reveal
function initMobileMenu() {
    const toggle = document.querySelector('[data-mobile-toggle]');
    const menu = document.querySelector('[data-mobile-menu]');
    if (!toggle || !menu) return;

    const closeBtn = menu.querySelector('[data-mobile-close]');
    const items = menu.querySelectorAll('[data-mobile-item]');
    let isOpen = false;

    function getClipOrigin() {
        const rect = toggle.getBoundingClientRect();
        const x = rect.left + rect.width / 2;
        const y = rect.top + rect.height / 2;
        return `${x}px ${y}px`;
    }

    function open() {
        if (isOpen) return;
        isOpen = true;

        toggle.setAttribute('aria-expanded', 'true');
        menu.classList.remove('hidden');
        document.documentElement.style.overflow = 'hidden';

        const origin = getClipOrigin();
        const diagonal = Math.hypot(window.innerWidth, window.innerHeight);

        const tl = gsap.timeline();

        // Circle clip-path reveal from burger position
        tl.fromTo(menu,
            { clipPath: `circle(0px at ${origin})` },
            { clipPath: `circle(${diagonal}px at ${origin})`, duration: 0.6, ease: 'power3.inOut' }
        );

        // Close button spins in
        tl.fromTo(closeBtn, { opacity: 0, rotation: -90 }, {
            opacity: 1, rotation: 0, duration: 0.3, ease: 'back.out(2)'
        }, '-=0.3');

        // Items stagger with blur
        tl.fromTo(items,
            { opacity: 0, y: 30, filter: 'blur(8px)' },
            { opacity: 1, y: 0, filter: 'blur(0px)', duration: 0.4, stagger: 0.06, ease: 'power2.out' },
            '-=0.25'
        );
    }

    function close() {
        if (!isOpen) return;
        isOpen = false;

        toggle.setAttribute('aria-expanded', 'false');
        const origin = getClipOrigin();

        const tl = gsap.timeline({
            onComplete() {
                menu.classList.add('hidden');
                document.documentElement.style.overflow = '';
                gsap.set([menu, closeBtn, ...items], { clearProps: 'all' });
            }
        });

        tl.to(items, {
            opacity: 0, y: -15, filter: 'blur(4px)',
            duration: 0.2, stagger: 0.02, ease: 'power2.in'
        });
        tl.to(menu,
            { clipPath: `circle(0px at ${origin})`, duration: 0.45, ease: 'power3.inOut' },
            '-=0.1'
        );
    }

    toggle.addEventListener('click', open);
    closeBtn?.addEventListener('click', close);
    menu.querySelectorAll('a[href]').forEach(link => {
        link.addEventListener('click', close);
    });
}

// Desktop megamenu — GSAP hover + Enter key for keyboard
function initDesktopDropdowns() {
    const dropdowns = document.querySelectorAll('[data-dropdown]');

    dropdowns.forEach(wrapper => {
        const panel = wrapper.querySelector('[data-dropdown-panel]');
        const chevron = wrapper.querySelector('[data-dropdown-chevron]');
        const children = panel?.querySelectorAll(':scope > div');
        if (!panel) return;

        const trigger = wrapper.querySelector('button, a');
        let openTl = null;
        let closeTimer = null;
        let isOpen = false;

        function show() {
            if (isOpen) return;
            clearTimeout(closeTimer);
            if (openTl) openTl.kill();
            isOpen = true;

            gsap.set(panel, { visibility: 'visible', opacity: 0 });

            openTl = gsap.timeline();

            openTl.fromTo(panel,
                { opacity: 0, scale: 0.96, y: -4, filter: 'blur(6px)' },
                { opacity: 1, scale: 1, y: 0, filter: 'blur(0px)', duration: 0.3, ease: 'power2.out' }
            );

            if (children?.length) {
                openTl.fromTo(children,
                    { opacity: 0, y: 8 },
                    { opacity: 1, y: 0, duration: 0.25, stagger: 0.04, ease: 'power2.out' },
                    '-=0.15'
                );
            }

            if (chevron) {
                gsap.to(chevron, { rotation: 180, duration: 0.3, ease: 'power2.out' });
            }

            if (trigger) trigger.setAttribute('aria-expanded', 'true');
        }

        function hide() {
            clearTimeout(closeTimer);
            closeTimer = setTimeout(() => {
                if (!isOpen) return;
                if (openTl) openTl.kill();
                isOpen = false;

                gsap.to(panel, {
                    opacity: 0, scale: 0.97, y: -4, filter: 'blur(4px)',
                    duration: 0.2, ease: 'power2.in',
                    onComplete() {
                        gsap.set(panel, { visibility: 'hidden', clearProps: 'transform,filter' });
                        if (children?.length) gsap.set(children, { clearProps: 'all' });
                    }
                });

                if (chevron) {
                    gsap.to(chevron, { rotation: 0, duration: 0.2, ease: 'power2.in' });
                }

                if (trigger) trigger.setAttribute('aria-expanded', 'false');
            }, 80);
        }

        function hideImmediate() {
            clearTimeout(closeTimer);
            if (openTl) openTl.kill();
            isOpen = false;
            gsap.set(panel, { visibility: 'hidden', clearProps: 'all' });
            if (children?.length) gsap.set(children, { clearProps: 'all' });
            if (chevron) gsap.set(chevron, { rotation: 0 });
            if (trigger) trigger.setAttribute('aria-expanded', 'false');
        }

        // Mouse: hover to open/close
        wrapper.addEventListener('mouseenter', show);
        wrapper.addEventListener('mouseleave', hide);

        // Mouse: click on chevron toggles (prevents <a> navigation)
        if (chevron) {
            chevron.closest('svg').addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                if (isOpen) { hideImmediate(); } else { show(); }
            });
        }

        // Keyboard: Enter/Space on trigger toggles dropdown
        trigger?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                if (isOpen) { hideImmediate(); } else { show(); }
            }
        });

        // Close when focus leaves wrapper entirely
        wrapper.addEventListener('focusout', (e) => {
            if (isOpen && !wrapper.contains(e.relatedTarget)) {
                hideImmediate();
            }
        });

        // Escape closes immediately
        wrapper.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && isOpen) {
                hideImmediate();
                trigger?.focus();
            }
        });
    });
}

// Availability badge
async function initAvailabilityBadge() {
    const badge = document.querySelector('[data-availability-badge]');
    if (!badge) return;

    try {
        const res = await fetch('/api/availability/next');
        const data = await res.json();

        if (data.available) {
            const label = formatAvailability(data.days_until, data.next_date);
            badge.innerHTML = `
                <a href="https://sensae.cc/booking" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary text-text-light text-sm font-medium hover:opacity-90 transition">
                    <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                    <span>${label}</span>
                </a>`;
        } else {
            badge.innerHTML = '';
        }
    } catch {
        badge.innerHTML = '';
    }
}

function formatAvailability(days, nextDate) {
    if (days === 0) return 'Disponible aujourd\'hui';
    if (days === 1) return 'Disponible demain';

    const date = new Date(nextDate);
    const dayNames = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];

    if (days <= 6) return `Disponible ${dayNames[date.getDay()]}`;
    return `Disponible dans ${days} jours`;
}

// Smooth anchor scrolling via GSAP (replaces CSS scroll-behavior: smooth)
document.addEventListener('click', (e) => {
    const anchor = e.target.closest('a[href^="#"]');
    if (!anchor) return;
    const target = document.querySelector(anchor.getAttribute('href'));
    if (!target) return;
    e.preventDefault();
    gsap.to(window, { scrollTo: { y: target, offsetY: 0 }, duration: 0.8, ease: 'power2.inOut' });
});

// Obfuscated links
document.addEventListener('click', (e) => {
    const el = e.target.closest('[data-lk],[data-lkb]');
    if (!el) return;

    e.preventDefault();
    const encoded = el.dataset.lkb || el.dataset.lk;
    const url = atob(encoded);

    if (el.dataset.lkb) {
        window.open(url, '_blank', 'noopener,noreferrer');
    } else {
        window.location.href = url;
    }
});
