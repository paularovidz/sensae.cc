@extends('layouts.app')

@section('title', $page->meta_title ?? $page->title . ' - sensaë')
@section('meta_description', $page->meta_description ?? '')

@section('breadcrumb')
    <li><a href="/" class="hover:text-primary transition">Accueil</a></li>
    <li class="before:content-['/'] before:mx-1.5">{{ $page->title }}</li>
@endsection

@section('content')
<x-breadcrumb :items="[
    ['name' => 'Accueil', 'url' => url('/')],
    ['name' => $page->title],
]" />

    {{-- Hero banner --}}
    <section class="hero relative banner-gradient">
        <div class="container">
            <div class="relative z-10 mx-auto max-w-4xl space-y-10">
                @if($page->h1)
                    <h1 data-animate="fade-in" class="text-xs text-center uppercase">
                        {{ $page->title }}
                    </h1>
                @endif
                
                <p data-animate="fade-up" class="has-italic-text font-secondary mb-6 text-center text-5xl text-white md:text-6xl/tight">
                    {!! $page->title !!}
                </p>

                <div data-animate="fade-up" data-animate-delay="0.1" class="flex flex-wrap items-center justify-center gap-5">
                    <x-cta-contact label="Ouverture en août" :star="true" />
                </div>
            </div>
        </div>

        @if($page->image)
            <x-hero-image :slug="$page->image" :alt="$page->title" />
        @endif
    </section>

    {{-- Content --}}
    <section>
        <div class="container">
            <div data-animate="fade-up" class="mx-auto max-w-4xl prose-styles">
                {!! $page->content !!}
            </div>
        </div>
    </section>

    {{-- Carte --}}
    @php
        $baseName = $mapSettings['map_base_name'] ?? 'Audruicq';
        $baseLat = (float) ($mapSettings['map_base_latitude'] ?? 50.8792100);
        $baseLng = (float) ($mapSettings['map_base_longitude'] ?? 2.0746580);
        $hasItineraire = $page->map_city && $page->map_latitude && $page->map_longitude;
    @endphp
    <section>
        <div class="container">
            <div class="mx-auto max-w-5xl">
                @if($hasItineraire)
                    <div class="mb-8 text-center" data-animate="fade-up">
                        <p class="text-secondary mb-2 text-sm font-medium uppercase tracking-wider">Itinéraire</p>
                        <h2 class="font-secondary text-3xl text-white md:text-4xl">
                            Votre espace multi-sensoriel à <span class="text-primary">{{ $page->map_travel_time }} minutes</span> de {{ $page->map_city }}
                        </h2>
                    </div>
                @else
                    <div class="mb-8 text-center" data-animate="fade-up">
                        <p class="text-secondary mb-2 text-sm font-medium uppercase tracking-wider">Nous trouver</p>
                        <h2 class="font-secondary text-3xl text-white md:text-4xl">
                            Nous trouver à {{ $baseName }}
                        </h2>
                    </div>
                @endif

                <div data-animate="fade-up" data-animate-delay="0.1" class="map-itineraire-wrapper overflow-hidden rounded-2xl border border-border/30">
                    <div id="map-itineraire" class="h-[300px] w-full md:h-[350px]"></div>
                </div>

                @if($hasItineraire)
                    <p data-animate="fade-up" data-animate-delay="0.2" class="mt-6 text-center text-lg text-white/70">
                        Notre salle sensaë à {{ $baseName }} est facilement accessible depuis {{ $page->map_city }} en voiture.
                    </p>
                @else
                    <p data-animate="fade-up" data-animate-delay="0.2" class="mt-6 text-center text-lg text-white/70">
                        L'adresse définitive sera communiquée à l'ouverture de la salle Snoezelen.<br>
                        À proximité de Calais, Saint-Omer, Dunkerque et Boulogne-sur-Mer.
                    </p>
                @endif
            </div>
        </div>
    </section>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        .map-itineraire-wrapper { position: relative; }
        .map-itineraire-wrapper::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(114,26,214,0.05) 0%, rgba(114,26,214,0.08) 100%);
            pointer-events: none;
            z-index: 10;
        }
        .map-itineraire-wrapper > div { filter: brightness(1.4) contrast(1.2); }
        .marker-itineraire { background: transparent !important; border: none !important; }
        .marker-itineraire svg { filter: drop-shadow(0 4px 12px rgba(114,26,214,0.6)); }
        .leaflet-popup-content-wrapper { background: rgba(20,15,30,0.95) !important; border: 1px solid rgba(114,26,214,0.3) !important; border-radius: 12px !important; box-shadow: 0 8px 32px rgba(114,26,214,0.3) !important; }
        .leaflet-popup-tip { background: rgba(20,15,30,0.95) !important; }
        .leaflet-popup-content { margin: 12px 16px !important; color: #fff !important; }
        .leaflet-control-attribution { background: rgba(20,15,30,0.8) !important; color: rgba(255,255,255,0.5) !important; font-size: 10px !important; }
        .leaflet-control-attribution a { color: rgba(210,153,255,0.7) !important; }
        @keyframes pulse-itineraire {
            0% { transform: translate(-50%,-50%) scale(1); opacity: 0.6; }
            50% { transform: translate(-50%,-50%) scale(1.5); opacity: 0; }
            100% { transform: translate(-50%,-50%) scale(1); opacity: 0; }
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof L === 'undefined') return;

            var baseLat = {{ $baseLat }};
            var baseLng = {{ $baseLng }};
            var baseName = @json($baseName);
            var hasItineraire = {{ $hasItineraire ? 'true' : 'false' }};

            var map = L.map('map-itineraire', {
                scrollWheelZoom: false,
                zoomControl: false,
            });

            L.control.zoom({ position: 'bottomright' }).addTo(map);

            L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; OSM &copy; CARTO',
                subdomains: 'abcd',
                maxZoom: 19,
            }).addTo(map);

            // Marqueur base (principal, avec pulse)
            var baseIcon = L.divIcon({
                className: 'marker-itineraire',
                html: '<div style="position:relative;"><div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:50px;height:50px;background:rgba(114,26,214,0.3);border-radius:50%;animation:pulse-itineraire 2s infinite;"></div><svg width="40" height="50" viewBox="0 0 48 60" fill="none" style="position:relative;z-index:10;"><path d="M24 0C10.745 0 0 10.745 0 24c0 16.8 24 36 24 36s24-19.2 24-36C48 10.745 37.255 0 24 0z" fill="#721ad6"/><circle cx="24" cy="22" r="10" fill="white"/><circle cx="24" cy="22" r="5" fill="#721ad6"/></svg></div>',
                iconSize: [40, 50],
                iconAnchor: [20, 50],
                popupAnchor: [0, -50],
            });
            L.marker([baseLat, baseLng], { icon: baseIcon }).addTo(map);

            if (hasItineraire) {
                var villeLat = {{ $page->map_latitude ?? 0 }};
                var villeLng = {{ $page->map_longitude ?? 0 }};
                var villeName = @json($page->map_city);

                // Route pré-calculée
                var routeCoords = @json($page->map_route_geometry);
                if (routeCoords) {
                    var latLngs = routeCoords.map(function (c) { return [c[1], c[0]]; });
                    L.polyline(latLngs, { color: '#721ad6', weight: 4, opacity: 0.9 }).addTo(map);
                } else {
                    L.polyline([[villeLat, villeLng], [baseLat, baseLng]], {
                        color: '#721ad6', weight: 3, opacity: 0.8, dashArray: '10, 10',
                    }).addTo(map);
                }

                // Marqueur ville (secondaire)
                var villeIcon = L.divIcon({
                    className: 'marker-itineraire',
                    html: '<svg width="32" height="40" viewBox="0 0 32 40" fill="none"><path d="M16 0C7.163 0 0 7.163 0 16c0 11.2 16 24 16 24s16-12.8 16-24C32 7.163 24.837 0 16 0z" fill="#c67dff"/><circle cx="16" cy="14" r="6" fill="#D299FF"/></svg>',
                    iconSize: [32, 40],
                    iconAnchor: [16, 40],
                    popupAnchor: [0, -40],
                });
                L.marker([villeLat, villeLng], { icon: villeIcon }).addTo(map);

                map.fitBounds([[villeLat, villeLng], [baseLat, baseLng]], { padding: [50, 50], maxZoom: 12 });
            } else {
                map.setView([baseLat, baseLng], 9);
            }
        });
    </script>

    <x-pricing :discovery-only="true" />

    <x-cta-section />
@endsection
