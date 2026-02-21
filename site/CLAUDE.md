# Site Vitrine sensëa — Laravel (`site/`)

Site vitrine public remplaçant l'ancien site Astro (`www/`). Admin pilotable via Filament.

## Stack

- **Laravel 12** (PHP 8.4)
- **Filament 5** — admin panel sur `/admin`
- **Tailwind CSS 4** — variables CSS dynamiques depuis la BDD
- **Alpine.js** — composants interactifs (disponibilité)
- **MariaDB** — base `site_db` sur le container existant `snoezelen_db`
- **Docker** — port 8000

## Architecture

```
site/
├── app/
│   ├── Filament/
│   │   ├── Forms/Components/    # MediaPicker (champ custom)
│   │   ├── Pages/               # ManageSettings, ManageMenu
│   │   └── Resources/           # Sens, Article, Review, Faq, Page, Media
│   ├── Http/Controllers/
│   │   ├── Admin/               # MediaApiController (API interne BO)
│   │   └── *.php                # Controllers publics
│   ├── Models/
│   ├── Services/
│   │   ├── ImageService.php     # Conversion AVIF (GD), préservation alpha
│   │   └── SensaeApiService.php # Proxy vers l'API existante (prix, dispo)
│   └── View/
│       ├── Composers/           # SettingsComposer (inject couleurs, contact, social)
│       └── Components/          # ImageComponent (<x-image slug="..." />)
├── resources/
│   ├── css/app.css              # Tailwind + @theme avec CSS vars
│   ├── js/app.js                # Alpine.js + availabilityBadge
│   └── views/
│       ├── layouts/app.blade.php
│       ├── components/          # Blade components
│       ├── filament/            # Vues custom Filament (media-library, media-picker, settings, menu)
│       └── *.blade.php          # Pages publiques
└── database/migrations/
```

## Conventions Filament

- **Vues custom admin** : Filament a son propre pipeline CSS. Les classes Tailwind custom ne sont PAS compilées dans les vues Filament. Utiliser :
  - **Inline styles** avec CSS variables pour le dark mode
  - Pattern : classe `.mp` + `.dark .mp` dans un `<style>` block
  - Jamais `@pushOnce('scripts')` (ne fonctionne pas dans le contexte Livewire de Filament)
  - Alpine.js : tout inline dans `x-data`, pas de `Alpine.data()`
- **`$view`** sur les Pages custom : `protected string $view` (non-static, Filament v5)
- **`$formActionsAreSticky`** : `true` sur toutes les pages Create/Edit
- **`is_published`** : `->default(true)` sur tous les toggles

## Système de Médias

- **Modèle Media** : slug unique, path (stockage public), folder (organisation)
- **ImageService** : toute image uploadée est convertie en AVIF via GD (`imageavif`)
  - Préserve la transparence (alpha) pour PNG et WebP (`imagesavealpha`)
  - Slug = nom du fichier original slugifié
- **`<x-image slug="..." />`** : composant Blade pour afficher une image par son slug
- **MediaPicker** : champ Filament custom (modal explorateur de fichiers, drag & drop, dossiers)
- **Médiathèque** : page Filament standalone avec même UX explorateur, édition inline, suppression
- **API interne** : `admin/media-api/*` (auth requise) — index, upload, update, delete, rename folder

## Couleurs dynamiques

Les couleurs du site sont stockées en BDD (table `settings`, groupe `colors`). Le `SettingsComposer` les injecte dans le layout via des CSS custom properties :

```css
/* app.css */
@theme {
  --color-primary: var(--color-primary-override, #721ad6);
  /* ... */
}

/* layout injecte depuis la BDD */
:root { --color-primary-override: #xxx; }
```

## Routes publiques

| Route | Controller | Description |
|-------|-----------|-------------|
| `/` | HomeController | Page d'accueil |
| `/comprendre/{slug}` | SensController | Pages sens |
| `/conseils` | ArticleController | Liste articles |
| `/conseils/{slug}` | ArticleController | Détail article |
| `/faq` | FaqController | FAQ |
| `/salle-snoezelen-{city}` | PageController | Pages ville |
| `/api/availability/next` | AvailabilityController | Proxy XHR dispo |
| `/{slug}` | PageController | Catch-all pages dynamiques |

## Docker

```yaml
# Service dans docker-compose.yml
site:
  container_name: snoezelen_site
  ports: ["8000:80"]
  depends_on: [db]
  # PHP 8.4 + Apache + GD (AVIF, WebP, Freetype) + Node (Vite)
```

```bash
# Commandes utiles
docker exec snoezelen_site php artisan migrate
docker exec snoezelen_site php artisan db:seed
docker exec snoezelen_site php artisan cache:clear
```

## Base de données (`site_db`)

Tables principales : `settings`, `sens`, `articles`, `reviews`, `faqs`, `pages`, `media`, `users`

Tous les modèles de contenu utilisent des UUID comme clé primaire.
