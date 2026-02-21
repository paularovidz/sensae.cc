import './bootstrap';
import Alpine from 'alpinejs';

Alpine.data('availabilityBadge', () => ({
    loading: true,
    available: false,
    label: '',
    daysUntil: null,

    async init() {
        try {
            const res = await fetch('/api/availability/next');
            const data = await res.json();

            if (data.available) {
                this.available = true;
                this.daysUntil = data.days_until;
                this.label = this.formatLabel(data.days_until, data.next_date);
            }
        } catch (e) {
            console.error('Availability fetch failed:', e);
        } finally {
            this.loading = false;
        }
    },

    formatLabel(days, nextDate) {
        if (days === 0) return 'Disponible aujourd\'hui';
        if (days === 1) return 'Disponible demain';

        const date = new Date(nextDate);
        const dayNames = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];

        if (days <= 6) {
            return `Disponible ${dayNames[date.getDay()]}`;
        }

        return `Disponible dans ${days} jours`;
    }
}));

Alpine.start();

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

// GSAP Animations
import { initAnimations } from './animations/index';

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAnimations);
} else {
    initAnimations();
}
