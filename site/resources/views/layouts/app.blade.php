<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $seo['seo_title'] ?? 'sensëa - Snoezelen')</title>
    <meta name="description" content="@yield('meta_description', $seo['seo_description'] ?? '')">

    <style>
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
</head>
<body class="bg-body text-text-default min-h-screen flex flex-col">
    @include('partials.header')

    <main class="section-spacing-lg fit-to-screen">
        @yield('content')
    </main>

    @include('partials.footer')
</body>
</html>
