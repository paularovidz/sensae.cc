import gsap from 'gsap';

export function initFaq() {
  document.querySelectorAll('[data-faq-item]').forEach(details => {
    const summary = details.querySelector('summary');
    const content = details.querySelector('[data-faq-content]');
    const chevron = details.querySelector('[data-faq-chevron]');
    if (!summary || !content) return;

    summary.addEventListener('click', (e) => {
      e.preventDefault();
      const isOpen = details.hasAttribute('open');

      if (isOpen) {
        gsap.to(content, {
          height: 0,
          opacity: 0,
          duration: 0.3,
          ease: 'power2.inOut',
          onComplete: () => details.removeAttribute('open'),
        });
        if (chevron) gsap.to(chevron, { rotation: 0, duration: 0.3, ease: 'power2.inOut' });
      } else {
        details.setAttribute('open', '');
        gsap.fromTo(content,
          { height: 0, opacity: 0 },
          { height: 'auto', opacity: 1, duration: 0.4, ease: 'power3.out' }
        );
        if (chevron) gsap.to(chevron, { rotation: 180, duration: 0.3, ease: 'power2.inOut' });
      }
    });
  });
}
