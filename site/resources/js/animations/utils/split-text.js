export function splitIntoWords(element) {
  const text = element.textContent;
  const nodes = Array.from(element.childNodes);
  element.innerHTML = '';
  element.setAttribute('aria-label', text);

  const wordSpans = [];

  nodes.forEach(node => {
    const isElement = node.nodeType === Node.ELEMENT_NODE;
    const extraClasses = isElement ? node.className : '';
    const content = node.textContent;

    content.split(/\s+/).filter(Boolean).forEach(word => {
      const span = document.createElement('span');
      span.className = `gsap-word animated${extraClasses ? ' ' + extraClasses : ''}`;
      span.style.display = 'inline-block';
      span.setAttribute('aria-hidden', 'true');
      span.textContent = word + '\u00A0';
      element.appendChild(span);
      wordSpans.push(span);
    });
  });

  // Remove trailing nbsp from last word
  if (wordSpans.length) {
    const last = wordSpans[wordSpans.length - 1];
    last.textContent = last.textContent.trimEnd();
  }

  return wordSpans;
}
