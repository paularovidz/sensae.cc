<x-filament-panels::page>
    <style>
        /* Use Filament's own CSS variables for seamless integration */
        .st-tabs { display: flex; gap: 2px; border-bottom: 1px solid rgba(var(--gray-200), 1); padding-bottom: 0; margin-bottom: 24px; overflow-x: auto; }
        .dark .st-tabs { border-color: rgba(255,255,255,0.1); }
        .st-tab { display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; font-size: 14px; font-weight: 500; color: rgba(var(--gray-500), 1); background: none; border: none; border-bottom: 2px solid transparent; cursor: pointer; white-space: nowrap; transition: all 0.15s; }
        .st-tab:hover { color: rgba(var(--gray-700), 1); }
        .dark .st-tab { color: rgba(255,255,255,0.45); }
        .dark .st-tab:hover { color: rgba(255,255,255,0.7); }
        .st-tab.active { color: rgb(var(--primary-600)); border-bottom-color: rgb(var(--primary-600)); }
        .dark .st-tab.active { color: rgb(var(--primary-400)); border-bottom-color: rgb(var(--primary-400)); }
        .st-tab svg { width: 18px; height: 18px; }
        .st-panel { display: none; }
        .st-panel.active { display: block; }

        /* Fields */
        .st-field { margin-bottom: 20px; }
        .st-label { display: block; font-size: 13px; font-weight: 600; color: rgba(var(--gray-700), 1); margin-bottom: 6px; }
        .dark .st-label { color: rgba(255,255,255,0.8); }
        .st-hint { display: block; font-size: 12px; color: rgba(var(--gray-400), 1); margin-bottom: 6px; }
        .dark .st-hint { color: rgba(255,255,255,0.35); }

        /* Inputs — match Filament native inputs */
        .st-input { display: block; width: 100%; padding: 9px 13px; font-size: 14px; color: rgba(var(--gray-900), 1); background: rgba(var(--gray-50), 1); border: 1px solid rgba(var(--gray-300), 1); border-radius: 8px; transition: border-color 0.15s; }
        .st-input:focus { outline: none; border-color: rgb(var(--primary-500)); box-shadow: 0 0 0 1px rgb(var(--primary-500)); }
        .st-input::placeholder { color: rgba(var(--gray-400), 1); }
        .dark .st-input { color: #fff; background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.1); }
        .dark .st-input:focus { border-color: rgb(var(--primary-500)); box-shadow: 0 0 0 1px rgb(var(--primary-500)); }
        .dark .st-input::placeholder { color: rgba(255,255,255,0.3); }
        .st-textarea { resize: vertical; min-height: 80px; }
        .st-select { appearance: none; padding-right: 36px; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%236b7280' viewBox='0 0 16 16'%3E%3Cpath d='M4.646 5.646a.5.5 0 0 1 .708 0L8 8.293l2.646-2.647a.5.5 0 0 1 .708.708l-3 3a.5.5 0 0 1-.708 0l-3-3a.5.5 0 0 1 0-.708z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; background-size: 16px; }

        /* Grids */
        .st-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; }
        .st-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        @media (max-width: 640px) { .st-grid-2 { grid-template-columns: 1fr; } }

        /* Color picker cards */
        .st-color { display: flex; align-items: center; gap: 10px; padding: 8px 12px; background: rgba(var(--gray-50), 1); border: 1px solid rgba(var(--gray-200), 1); border-radius: 10px; transition: border-color 0.15s; }
        .st-color:focus-within { border-color: rgb(var(--primary-500)); }
        .dark .st-color { background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1); }
        .st-color-swatch { width: 36px; height: 36px; border-radius: 8px; border: 2px solid rgba(var(--gray-200), 1); cursor: pointer; flex-shrink: 0; }
        .dark .st-color-swatch { border-color: rgba(255,255,255,0.15); }
        .st-color-swatch::-webkit-color-swatch-wrapper { padding: 0; }
        .st-color-swatch::-webkit-color-swatch { border: none; border-radius: 6px; }
        .st-color-info { flex: 1; min-width: 0; }
        .st-color-label { font-size: 13px; font-weight: 500; color: rgba(var(--gray-700), 1); }
        .dark .st-color-label { color: rgba(255,255,255,0.8); }
        .st-color-value { display: block; width: 100%; padding: 0; font-size: 12px; font-family: ui-monospace, monospace; color: rgba(var(--gray-500), 1); background: none; border: none; outline: none; }
        .dark .st-color-value { color: rgba(255,255,255,0.4); }

        /* Section desc */
        .st-section-desc { font-size: 13px; color: rgba(var(--gray-500), 1); margin-bottom: 20px; line-height: 1.5; }
        .dark .st-section-desc { color: rgba(255,255,255,0.4); }

        /* Sub headings inside tabs */
        .st-sub { font-size: 15px; font-weight: 600; color: rgba(var(--gray-800), 1); margin: 28px 0 12px; padding-bottom: 8px; border-bottom: 1px solid rgba(var(--gray-100), 1); }
        .st-sub:first-child { margin-top: 0; }
        .dark .st-sub { color: rgba(255,255,255,0.85); border-color: rgba(255,255,255,0.06); }

        /* Social rows */
        .st-social-row { display: flex; align-items: center; gap: 10px; padding: 10px 14px; background: rgba(var(--gray-50), 1); border: 1px solid rgba(var(--gray-200), 1); border-radius: 10px; margin-bottom: 10px; }
        .st-social-row:focus-within { border-color: rgb(var(--primary-500)); }
        .dark .st-social-row { background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1); }
        .st-social-icon { width: 20px; height: 20px; color: rgba(var(--gray-400), 1); flex-shrink: 0; }
        .dark .st-social-icon { color: rgba(255,255,255,0.35); }
        .st-social-input { flex: 1; padding: 0; font-size: 14px; color: rgba(var(--gray-900), 1); background: none; border: none; outline: none; }
        .st-social-input::placeholder { color: rgba(var(--gray-400), 1); }
        .dark .st-social-input { color: #fff; }
        .dark .st-social-input::placeholder { color: rgba(255,255,255,0.3); }

        /* Info box */
        .st-info { display: flex; gap: 10px; padding: 12px 16px; font-size: 13px; line-height: 1.5; color: rgba(var(--gray-600), 1); background: rgba(var(--gray-50), 1); border: 1px solid rgba(var(--gray-200), 1); border-radius: 10px; margin-bottom: 16px; }
        .dark .st-info { color: rgba(255,255,255,0.5); background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.08); }
        .st-info svg { width: 18px; height: 18px; flex-shrink: 0; margin-top: 1px; color: rgba(var(--gray-400), 1); }
        .dark .st-info svg { color: rgba(255,255,255,0.3); }

        /* Sticky save */
        .st-save { position: sticky; bottom: 0; z-index: 20; display: flex; justify-content: flex-end; padding: 16px 0; }
    </style>

    <form wire:submit="save">
        <div x-data="{ tab: 'colors' }">
            {{-- Tabs --}}
            <div class="st-tabs">
                <button type="button" class="st-tab" :class="{ active: tab === 'colors' }" @click="tab = 'colors'">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.098 19.902a3.75 3.75 0 0 0 5.304 0l6.401-6.402M6.75 21A3.75 3.75 0 0 1 3 17.25V4.125C3 3.504 3.504 3 4.125 3h5.25c.621 0 1.125.504 1.125 1.125v4.072M6.75 21a3.75 3.75 0 0 0 3.75-3.75V8.197M6.75 21h13.125c.621 0 1.125-.504 1.125-1.125v-5.25c0-.621-.504-1.125-1.125-1.125h-4.072M10.5 8.197l2.88-2.88c.438-.439 1.15-.439 1.59 0l3.712 3.713c.44.44.44 1.152 0 1.59l-2.879 2.88M6.75 17.25h.008v.008H6.75v-.008Z" /></svg>
                    Couleurs
                </button>
                <button type="button" class="st-tab" :class="{ active: tab === 'contact' }" @click="tab = 'contact'">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
                    Contact
                </button>
                <button type="button" class="st-tab" :class="{ active: tab === 'social' }" @click="tab = 'social'">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z" /></svg>
                    Réseaux
                </button>
                <button type="button" class="st-tab" :class="{ active: tab === 'rating' }" @click="tab = 'rating'">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" /></svg>
                    Avis
                </button>
                <button type="button" class="st-tab" :class="{ active: tab === 'seo' }" @click="tab = 'seo'">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                    SEO
                </button>
            </div>

            {{-- Colors --}}
            <div class="st-panel" :class="{ active: tab === 'colors' }">
                <p class="st-section-desc">Personnalisez l'apparence du site. Les changements sont visibles immédiatement après sauvegarde.</p>
                <div class="st-grid">
                    @foreach([
                        'color_primary' => ['Primaire', 'Boutons, liens, accents'],
                        'color_secondary' => ['Secondaire', 'Éléments secondaires'],
                        'color_accent' => ['Accent', 'Fond des sections mises en avant'],
                        'color_body' => ['Fond', 'Arrière-plan principal'],
                        'color_border' => ['Bordures', 'Séparateurs et contours'],
                        'color_text_default' => ['Texte', 'Texte courant'],
                        'color_text_light' => ['Texte clair', 'Titres et texte important'],
                    ] as $key => [$label, $desc])
                        <div class="st-color">
                            <input type="color" wire:model.live="data.{{ $key }}" class="st-color-swatch">
                            <div class="st-color-info">
                                <div class="st-color-label">{{ $label }}</div>
                                <input type="text" wire:model.live="data.{{ $key }}" class="st-color-value" spellcheck="false">
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Contact --}}
            <div class="st-panel" :class="{ active: tab === 'contact' }">
                <p class="st-section-desc">Informations affichées dans le footer et les pages de contact.</p>
                <div class="st-grid-2">
                    <div class="st-field">
                        <label class="st-label">Email</label>
                        <input type="email" wire:model="data.contact_email" class="st-input" placeholder="bonjour@sensae.cc">
                    </div>
                    <div class="st-field">
                        <label class="st-label">Téléphone</label>
                        <input type="text" wire:model="data.contact_phone" class="st-input" placeholder="06 12 34 56 78">
                    </div>
                </div>
                <div class="st-field">
                    <label class="st-label">Adresse</label>
                    <input type="text" wire:model="data.contact_address" class="st-input" placeholder="123 rue de la Paix, 62370 Audruicq">
                </div>
            </div>

            {{-- Social --}}
            <div class="st-panel" :class="{ active: tab === 'social' }">
                <p class="st-section-desc">Liens vers vos profils. Laissez vide pour masquer l'icône sur le site.</p>
                @foreach([
                    'social_facebook' => ['Facebook', 'M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z'],
                    'social_instagram' => ['Instagram', 'M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z'],
                    'social_linkedin' => ['LinkedIn', 'M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z'],
                ] as $key => [$label, $path])
                    <div class="st-social-row">
                        <svg class="st-social-icon" viewBox="0 0 24 24" fill="currentColor"><path d="{{ $path }}"/></svg>
                        <input type="url" wire:model="data.{{ $key }}" class="st-social-input" placeholder="{{ $label }} — https://...">
                    </div>
                @endforeach
            </div>

            {{-- Rating --}}
            <div class="st-panel" :class="{ active: tab === 'rating' }">
                <p class="st-section-desc">Affiché dans le bloc d'appel à l'action en bas de page. Laissez les champs vides pour masquer le bloc.</p>
                <div class="st-grid-2">
                    <div class="st-field">
                        <label class="st-label">Note</label>
                        <span class="st-hint">Ex : 4.95/5</span>
                        <input type="text" wire:model="data.rating_score" class="st-input" placeholder="4.95/5">
                    </div>
                    <div class="st-field">
                        <label class="st-label">Libellé</label>
                        <span class="st-hint">Ex : Avis Google</span>
                        <input type="text" wire:model="data.rating_label" class="st-input" placeholder="Avis Google">
                    </div>
                </div>
            </div>

            {{-- SEO --}}
            <div class="st-panel" :class="{ active: tab === 'seo' }">

                {{-- Métadonnées --}}
                <h3 class="st-sub">Métadonnées</h3>
                <p class="st-section-desc">Balises meta par défaut. Chaque page peut surcharger ces valeurs.</p>
                <div class="st-field">
                    <label class="st-label">Titre du site</label>
                    <span class="st-hint">Apparaît dans l'onglet du navigateur et les résultats Google (50-60 caractères)</span>
                    <input type="text" wire:model="data.seo_title" class="st-input" placeholder="sensaë - Salle Snoezelen">
                </div>
                <div class="st-field">
                    <label class="st-label">Description</label>
                    <span class="st-hint">Résumé affiché dans les résultats de recherche (150-160 caractères)</span>
                    <textarea wire:model="data.seo_description" rows="3" class="st-input st-textarea" placeholder="Description du site pour les moteurs de recherche..."></textarea>
                </div>
                <div class="st-field">
                    <label class="st-label">URL canonique</label>
                    <span class="st-hint">URL de base du site, sans slash final</span>
                    <input type="url" wire:model="data.seo_canonical_url" class="st-input" placeholder="https://sensae.cc">
                </div>

                {{-- Open Graph --}}
                <h3 class="st-sub">Open Graph</h3>
                <p class="st-section-desc">Contrôle l'aperçu affiché lors du partage sur Facebook, LinkedIn, WhatsApp, etc.</p>
                <div class="st-info">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" /></svg>
                    <span>Le titre et la description Open Graph reprennent par défaut les métadonnées ci-dessus. L'image est obligatoire pour un bon rendu (1200×630px recommandé).</span>
                </div>
                <div class="st-field">
                    <label class="st-label">Image de partage</label>
                    <span class="st-hint">URL complète de l'image (1200×630px, JPG ou PNG, &lt; 5 Mo)</span>
                    <input type="url" wire:model="data.seo_og_image" class="st-input" placeholder="https://sensae.cc/storage/og-image.jpg">
                </div>

                {{-- Rich Snippets / Schema.org --}}
                <h3 class="st-sub">Données structurées (Schema.org)</h3>
                <p class="st-section-desc">Enrichit la fiche Google avec type d'activité, coordonnées et fourchette de prix. Les informations de contact et réseaux sociaux de l'onglet Contact sont incluses automatiquement.</p>
                <div class="st-grid-2">
                    <div class="st-field">
                        <label class="st-label">Type d'activité</label>
                        <span class="st-hint">Type Schema.org pour les résultats enrichis</span>
                        <select wire:model="data.seo_schema_type" class="st-input st-select">
                            <option value="LocalBusiness">Commerce local (LocalBusiness)</option>
                            <option value="HealthAndBeautyBusiness">Santé & Bien-être (HealthAndBeautyBusiness)</option>
                            <option value="MedicalBusiness">Établissement médical (MedicalBusiness)</option>
                            <option value="ProfessionalService">Service professionnel (ProfessionalService)</option>
                        </select>
                    </div>
                    <div class="st-field">
                        <label class="st-label">Fourchette de prix</label>
                        <span class="st-hint">Indicateur affiché dans la fiche Google</span>
                        <select wire:model="data.seo_schema_price_range" class="st-input st-select">
                            <option value="€">€ — Économique</option>
                            <option value="€€">€€ — Modéré</option>
                            <option value="€€€">€€€ — Haut de gamme</option>
                        </select>
                    </div>
                </div>

                {{-- Tracking --}}
                <h3 class="st-sub">Tracking</h3>
                <p class="st-section-desc">Le script Plausible est injecté automatiquement si le domaine est renseigné.</p>
                <div class="st-field">
                    <label class="st-label">Domaine Plausible</label>
                    <span class="st-hint">Ex : sensae.cc</span>
                    <input type="text" wire:model="data.seo_plausible_domain" class="st-input" placeholder="sensae.cc">
                </div>
            </div>

            {{-- Spacer --}}
            <div style="height: 56px;"></div>

            {{-- Sticky save --}}
            <div class="st-save">
                <x-filament::button type="submit">
                    Sauvegarder
                </x-filament::button>
            </div>
        </div>
    </form>
</x-filament-panels::page>
