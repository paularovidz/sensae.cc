@php
    $pricing = app(\App\Services\SensaeApiService::class)->getPricing();
    $sessions = $pricing['sessions'] ?? [];
    $packs = $pricing['packs'] ?? [];

    $discoveryPrice = (int) ($sessions['discovery']['price'] ?? 55);
    $discoveryDuration = (int) ($sessions['discovery']['duration'] ?? 75);
    $regularPrice = (int) ($sessions['regular']['price'] ?? 45);
    $regularDuration = (int) ($sessions['regular']['duration'] ?? 45);

    $canonicalBase = rtrim($seo['seo_canonical_url'] ?? 'https://sensae.cc', '/');

    $offers = [
        [
            '@type' => 'Offer',
            'name' => 'Séance découverte',
            'description' => "Première séance de {$discoveryDuration} minutes pour découvrir l'environnement Snoezelen et adapter l'accompagnement sensoriel.",
            'price' => (string) $discoveryPrice,
            'priceCurrency' => 'EUR',
            'url' => $canonicalBase . '/tarifs',
            'availability' => 'https://schema.org/InStock',
        ],
        [
            '@type' => 'Offer',
            'name' => 'Séance classique',
            'description' => "Séance Snoezelen de {$regularDuration} minutes personnalisée selon vos besoins sensoriels.",
            'price' => (string) $regularPrice,
            'priceCurrency' => 'EUR',
            'url' => $canonicalBase . '/tarifs',
            'availability' => 'https://schema.org/InStock',
        ],
    ];

    if (!empty($packs['pack_2'])) {
        $pack2Price = (int) ($packs['pack_2']['price'] ?? 110);
        $pack2Sessions = (int) ($packs['pack_2']['sessions'] ?? 2);
        $offers[] = [
            '@type' => 'Offer',
            'name' => "Pack {$pack2Sessions} séances",
            'description' => "Forfait de {$pack2Sessions} séances Snoezelen de {$regularDuration} minutes pour un suivi régulier.",
            'price' => (string) $pack2Price,
            'priceCurrency' => 'EUR',
            'url' => $canonicalBase . '/tarifs',
            'availability' => 'https://schema.org/InStock',
        ];
    }

    if (!empty($packs['pack_4'])) {
        $pack4Price = (int) ($packs['pack_4']['price'] ?? 200);
        $pack4Sessions = (int) ($packs['pack_4']['sessions'] ?? 4);
        $offers[] = [
            '@type' => 'Offer',
            'name' => "Pack {$pack4Sessions} séances",
            'description' => "Forfait de {$pack4Sessions} séances Snoezelen de {$regularDuration} minutes pour un accompagnement complet.",
            'price' => (string) $pack4Price,
            'priceCurrency' => 'EUR',
            'url' => $canonicalBase . '/tarifs',
            'availability' => 'https://schema.org/InStock',
        ];
    }

    $provider = [
        '@type' => 'LocalBusiness',
        'name' => $seo['seo_title'] ?? 'sensaë',
        'url' => $canonicalBase,
    ];

    if (!empty($contact['contact_phone'] ?? '')) {
        $provider['telephone'] = $contact['contact_phone'];
    }
    if (!empty($contact['contact_address'] ?? '')) {
        $provider['address'] = [
            '@type' => 'PostalAddress',
            'streetAddress' => $contact['contact_address'],
            'addressCountry' => 'FR',
        ];
    }

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Service',
        'name' => 'Séances Snoezelen',
        'description' => 'Séances de stimulation sensorielle Snoezelen personnalisées dans un espace dédié. Accompagnement adapté pour enfants, adultes, personnes âgées et situations de handicap.',
        'url' => $canonicalBase . '/tarifs',
        'provider' => $provider,
        'serviceType' => 'Thérapie sensorielle Snoezelen',
        'areaServed' => [
            '@type' => 'Country',
            'name' => 'France',
        ],
        'hasOfferCatalog' => [
            '@type' => 'OfferCatalog',
            'name' => 'Tarifs séances Snoezelen',
            'itemListElement' => $offers,
        ],
    ];
@endphp

@push('structured_data')
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
@endpush
