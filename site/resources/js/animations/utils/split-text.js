export function splitIntoWords(element) {
  const text = element.textContent;
  element.innerHTML = '';
  element.setAttribute('aria-label', text);

  return text.split(/\s+/).filter(Boolean).map((word, i, arr) => {
    const span = document.createElement('span');
    span.className = 'gsap-word';
    span.style.display = 'inline-block';
    span.setAttribute('aria-hidden', 'true');
    span.textContent = word + (i < arr.length - 1 ? '\u00A0' : '');
    element.appendChild(span);
    return span;
  });
}
