# Système d'animations GSAP — sensaë

Guide de référence pour animer les composants du site. Toutes les animations sont pilotées par **data-attributes** — pas de JS custom nécessaire pour les nouveaux composants.

## Data-attributes disponibles

### Scroll Reveal — `data-animate`

Ajouter sur n'importe quel élément pour l'animer à l'entrée dans le viewport.

| Attribut | Effet |
|----------|-------|
| `data-animate="fade-up"` | Monte de 30px + fade in |
| `data-animate="fade-in"` | Fade in simple |
| `data-animate="fade-left"` | Glisse depuis la gauche |
| `data-animate="fade-right"` | Glisse depuis la droite |
| `data-animate="scale-in"` | Scale 0.95 → 1 + fade in |

**Délai optionnel** : `data-animate-delay="0.2"` (en secondes)

```html
<h2 data-animate="fade-up">Titre</h2>
<p data-animate="fade-up" data-animate-delay="0.1">Sous-titre</p>
```

### Stagger Grid — `data-animate-grid`

Sur un conteneur : ses enfants directs apparaissent en stagger (0.12s entre chaque).

```html
<div data-animate-grid class="grid grid-cols-3 gap-6">
    <div>Card 1</div>  {{-- apparaît en premier --}}
    <div>Card 2</div>  {{-- 0.12s après --}}
    <div>Card 3</div>  {{-- 0.24s après --}}
</div>
```

### Card Tilt 3D — `data-tilt`

Effet de rotation subtile (±3°) au survol de la souris. Desktop uniquement.

```html
<div data-tilt class="border rounded-2xl p-6">
    Contenu de la carte
</div>
```

### Magnetic — `data-magnetic`

Le bouton suit le curseur avec un effet d'attraction. La valeur est la force (0.2 = subtil, 0.3 = moyen). Desktop uniquement.

```html
<a href="#" data-magnetic="0.3" class="px-8 py-3 bg-primary rounded-xl">
    CTA
</a>
```

### Bordure animée — `data-animate-border`

La bordure `border-t` de la section grandit du centre vers l'extérieur au scroll.

```html
<section data-animate-border class="py-16 border-t border-border">
    ...
</section>
```

### Hero (page d'accueil uniquement)

Timeline séquentielle au chargement (pas au scroll) :

```html
<section data-hero>
    <h1 data-hero-title>Mots animés un par un</h1>
    <p data-hero-subtitle>Sous-titre</p>
    <div data-hero-cta>
        <a href="#">CTA 1</a>
        <a href="#">CTA 2</a>
    </div>
</section>
```

### FAQ — `data-faq-item`

Open/close animé par GSAP (height + opacity). Remplace le snap natif des `<details>`.

```html
<details data-faq-item>
    <summary>Question ?</summary>
    <div data-faq-content>Réponse</div>
</details>
```

Chevron optionnel : `data-faq-chevron` sur un SVG pour la rotation animée.

### Footer

```html
<footer data-footer>
    <div data-footer-col>Colonne 1</div>
    <div data-footer-col>Colonne 2</div>
    <div data-footer-copyright>© 2026</div>
</footer>
```

### Header

```html
<header data-header>
    ...
</header>
```

Comportement automatique : se cache au scroll down, réapparaît au scroll up, backdrop blur après 80px.

## Patterns par type de composant

### Carte (sens, article, review)

```html
<div data-tilt class="border rounded-2xl">
    ...
</div>
```

→ `data-tilt` pour le 3D hover. Placer dans un `data-animate-grid` parent pour le stagger.

### Section avec titre + grille

```html
<section data-animate-border class="py-16 border-t border-border">
    <h2 data-animate="fade-up">Titre</h2>
    <p data-animate="fade-up" data-animate-delay="0.1">Sous-titre</p>
    <div data-animate-grid class="grid gap-6">
        ...enfants animés en stagger...
    </div>
</section>
```

### Page de détail (article, sens)

```html
<span data-animate="fade-in">Badge</span>
<h1 data-animate="fade-up">Titre</h1>
<div data-animate="fade-up" data-animate-delay="0.1">Meta (date, auteur)</div>
<div data-animate="fade-up" data-animate-delay="0.2">Image</div>
<div data-animate="fade-up" data-animate-delay="0.3">Contenu prose</div>
```

### CTA / Bouton

```html
<a data-magnetic="0.3" class="bg-primary rounded-xl">CTA principal</a>
<a data-magnetic="0.2" class="border rounded-xl">CTA secondaire</a>
```

## CSS requis

Le CSS dans `app.css` gère :
- **FOUC** : `[data-animate], [data-animate-grid] > *` → `opacity: 0` (GSAP les révèle)
- **Reduced motion** : `@media (prefers-reduced-motion: reduce)` → tout visible, pas d'animation
- **Header** : `position: sticky`, `.header-scrolled` backdrop blur
- **Tilt** : `transform-style: preserve-3d`
- **Bordures** : `border-image` avec `--border-scale`

## Architecture JS

```
resources/js/animations/
├── index.js              # Orchestrateur (register plugins, init all)
├── config.js             # Constantes (easings, durées, staggers, scroll starts)
├── utils/
│   ├── reduced-motion.js # prefers-reduced-motion check
│   └── split-text.js     # Split h1 en mots (hero)
├── core/
│   ├── scroll-reveal.js  # data-animate système
│   ├── header.js         # Hide/show header
│   └── magnetic.js       # data-magnetic effet
└── sections/
    ├── hero.js           # Timeline hero page load
    ├── cards.js          # Stagger grids + tilt 3D
    ├── faq.js            # Open/close GSAP
    └── footer.js         # Footer columns stagger
```

## Checklist nouveau composant

1. Carte interactive → ajouter `data-tilt`
2. Bouton/CTA → ajouter `data-magnetic="0.2"` ou `"0.3"`
3. Grille de cartes → ajouter `data-animate-grid` sur le parent
4. Section avec bordure → ajouter `data-animate-border` + `border-t border-border`
5. Titre de section → ajouter `data-animate="fade-up"`
6. Contenu séquentiel → `data-animate="fade-up"` avec `data-animate-delay` incrémental
7. Pas besoin de toucher au JS — tout est piloté par les data-attributes
