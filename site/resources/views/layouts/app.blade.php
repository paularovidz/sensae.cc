<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="icon" type="image/png" href="/favicon/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/favicon/favicon.svg" />
    <link rel="shortcut icon" href="/favicon/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="sensaë" />
    <link rel="manifest" href="/favicon/site.webmanifest" />

    @php
        $pageTitle = View::yieldContent('title', $seo['seo_title'] ?? 'sensaë - Snoezelen');
        $pageDescription = View::yieldContent('meta_description', $seo['seo_description'] ?? '');
        $canonicalBase = rtrim($seo['seo_canonical_url'] ?? 'https://sensae.cc', '/');
        $canonicalUrl = $canonicalBase . request()->getPathInfo();
        $ogImage = $seo['seo_og_image'] ?? '';
    @endphp

    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">

    {{-- Open Graph --}}
    <meta property="og:type" content="website">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:site_name" content="{{ $seo['seo_title'] ?? 'sensaë' }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    @if($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
    @endif

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="{{ $ogImage ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    @if($ogImage)
        <meta name="twitter:image" content="{{ $ogImage }}">
    @endif

    {{-- JSON-LD : WebSite + LocalBusiness --}}
    @php
        $websiteSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $seo['seo_title'] ?? 'sensaë',
            'url' => $canonicalBase,
            'description' => $seo['seo_description'] ?? '',
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($websiteSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>

    @php
        $schemaType = $seo['seo_schema_type'] ?? 'LocalBusiness';
        $schemaData = [
            '@context' => 'https://schema.org',
            '@type' => $schemaType,
            'name' => $seo['seo_title'] ?? 'sensaë',
            'url' => $canonicalBase,
            'description' => $seo['seo_description'] ?? '',
        ];

        if (!empty($ogImage)) {
            $schemaData['image'] = $ogImage;
        }
        if (!empty($contact['contact_phone'] ?? '')) {
            $schemaData['telephone'] = $contact['contact_phone'];
        }
        if (!empty($contact['contact_email'] ?? '')) {
            $schemaData['email'] = $contact['contact_email'];
        }
        if (!empty($contact['contact_address'] ?? '')) {
            $schemaData['address'] = [
                '@type' => 'PostalAddress',
                'streetAddress' => $contact['contact_address'],
                'addressCountry' => 'FR',
            ];
        }
        if (!empty($seo['seo_schema_price_range'] ?? '')) {
            $schemaData['priceRange'] = $seo['seo_schema_price_range'];
        }

        $sameAs = array_filter([
            $social['social_facebook'] ?? '',
            $social['social_instagram'] ?? '',
            $social['social_linkedin'] ?? '',
        ]);
        if (!empty($sameAs)) {
            $schemaData['sameAs'] = array_values($sameAs);
        }
    @endphp
    <script type="application/ld+json">{!! json_encode($schemaData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>

    <style>
        @font-face { font-display: swap; font-family: 'Satoshi'; font-weight: 400; src: url('/fonts/satoshi-regular.woff2') format('woff2'); }
        @font-face { font-display: swap; font-family: 'Satoshi'; font-weight: 500; src: url('/fonts/satoshi-medium.woff2') format('woff2'); }
        @font-face { font-display: swap; font-family: 'Amandine'; font-weight: 400; src: url('/fonts/amandine.woff2') format('woff2'); }
        :root {
            @isset($colors)
                --color-primary-override: {{ $colors['color_primary'] ?? '#721ad6' }};
                --color-secondary-override: {{ $colors['color_secondary'] ?? '#D299FF' }};
                --color-accent-override: {{ $colors['color_accent'] ?? '#1f122e' }};
                --color-body-override: {{ $colors['color_body'] ?? '#0e0c0c' }};
                --color-border-override: {{ $colors['color_border'] ?? '#242222' }};
                --color-text-default-override: {{ $colors['color_text_default'] ?? '#C8C1C1' }};
                --color-text-light-override: {{ $colors['color_text_light'] ?? '#fff6f6' }};
            @endisset
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="//embed.typeform.com/next/embed.js" defer></script>

    {{-- Plausible Analytics --}}
    @if(!empty($seo['seo_plausible_domain'] ?? ''))
        <script defer data-domain="{{ $seo['seo_plausible_domain'] }}" src="https://plausible.io/js/script.js"></script>
        <script>window.plausible=window.plausible||function(){(window.plausible.q=window.plausible.q||[]).push(arguments)};</script>
        <script>
            window.addEventListener('message', function (e) {
                if (e.data.type === 'form-submit' && sessionStorage.getItem('Contact') !== '1') {
                    sessionStorage.setItem('Contact', '1');
                    plausible('Contact');
                } else if (e.data.type === 'form-ready') {
                    plausible('Ouverture formulaire contact');
                }
            });
        </script>
    @endif
</head>
<body class="bg-body text-text-default min-h-screen flex flex-col">
    <a href="#main-content" class="skip-link">Aller au contenu principal</a>

    @include('partials.header')

    <main id="main-content" class="section-spacing-lg fit-to-screen">
        @yield('content')
    </main>

    @include('partials.footer')

    {{-- Plausible opt-out toggle (used in legal pages) --}}
    <script>
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-plausible-toggle]');
            if (!btn) return;
            var ignored = localStorage.getItem('plausible_ignore') === 'true';
            if (ignored) localStorage.removeItem('plausible_ignore');
            else localStorage.setItem('plausible_ignore', 'true');
            updatePlausibleToggle(!ignored);
        });
        function updatePlausibleToggle(ignored) {
            document.querySelectorAll('[data-plausible-toggle]').forEach(function (btn) {
                btn.textContent = ignored ? 'Réactiver les statistiques' : 'Désactiver les statistiques';
            });
            document.querySelectorAll('[data-plausible-status]').forEach(function (el) {
                el.textContent = ignored ? 'Statistiques désactivées.' : 'Statistiques activées.';
                el.style.color = ignored ? '#e57373' : '#81c784';
            });
        }
        document.addEventListener('DOMContentLoaded', function () {
            updatePlausibleToggle(localStorage.getItem('plausible_ignore') === 'true');
        });
    </script>
</body>
</html>
