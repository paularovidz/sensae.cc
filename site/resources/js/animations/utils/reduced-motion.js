const mq = window.matchMedia('(prefers-reduced-motion: reduce)');
let reduced = mq.matches;

mq.addEventListener('change', (e) => { reduced = e.matches; });

export const prefersReducedMotion = () => reduced;
