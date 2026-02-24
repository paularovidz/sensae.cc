// Utility helpers — no dependencies

export const lerp = (a, b, t) => a + (b - a) * t;
export const clamp = (v, min, max) => Math.min(Math.max(v, min), max);
export const randomRange = (min, max) => Math.random() * (max - min) + min;
export const randomInt = (min, max) => Math.floor(randomRange(min, max + 1));

export const isMobile = () =>
    'ontouchstart' in window || navigator.maxTouchPoints > 0;

export const reducedMotion = () =>
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

export const vh = () => window.innerHeight;
export const vw = () => window.innerWidth;

// Pick a random item from an array
export const pick = (arr) => arr[Math.floor(Math.random() * arr.length)];

// Color palette for the experience
export const PALETTE = {
    violetDeep: '#3a0e6e',
    violet:     '#721ad6',
    rose:       '#d299ff',
    warm:       '#fff0e0',
    white:      '#fffaf5',
    gold:       '#ffd699',
};

// Map note index (0-7) to a color (grave = violet, aigu = gold)
export function noteColor(index) {
    const colors = [
        '#5a1aaa', '#721ad6', '#9b4de0', '#b77ae8',
        '#d299ff', '#e8c4ff', '#ffd699', '#fff0e0',
    ];
    return colors[clamp(index, 0, 7)];
}

// Haptic vibration (mobile)
export function haptic(ms = 15) {
    if (navigator.vibrate) navigator.vibrate(ms);
}
