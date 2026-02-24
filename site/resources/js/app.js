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
    initAvailabilityBadge();
}

// Mobile menu toggle
function initMobileMenu() {
    const toggle = document.querySelector('[data-mobile-toggle]');
    const menu = document.querySelector('[data-mobile-menu]');
    if (!toggle || !menu) return;

    const iconOpen = toggle.querySelector('[data-icon-open]');
    const iconClose = toggle.querySelector('[data-icon-close]');

    toggle.addEventListener('click', () => {
        const isOpen = menu.classList.toggle('is-open');
        iconOpen?.classList.toggle('hidden', isOpen);
        iconClose?.classList.toggle('hidden', !isOpen);
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
