@extends('layouts.app')

@section('title', $seo['seo_title'] ?? 'sensëa - Salle Snoezelen')

@section('content')
    {{-- Hero --}}
    <section class="py-20 md:py-28">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 text-center">
            <h1 class="text-4xl md:text-6xl font-bold text-text-light font-secondary leading-tight">
                Votre espace <span class="text-primary">Snoezelen</span>
            </h1>
            <p class="mt-6 text-lg text-text-default max-w-2xl mx-auto">
                Découvrez la stimulation multisensorielle dans un environnement adapté et bienveillant.
            </p>
            <div class="mt-8 flex items-center justify-center gap-4">
                <a href="https://sensae.cc/booking" target="_blank" class="px-8 py-3 bg-primary text-text-light rounded-xl font-medium hover:opacity-90 transition">
                    Réserver une séance
                </a>
                <a href="#comprendre" class="px-8 py-3 border border-border text-text-light rounded-xl font-medium hover:bg-accent transition">
                    En savoir plus
                </a>
            </div>
        </div>
    </section>

    {{-- Comprendre les sens --}}
    @if($sens->count())
        <section id="comprendre" class="py-16 border-t border-border">
            <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <h2 class="text-3xl font-bold text-text-light font-secondary mb-8">Comprendre les sens</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($sens as $item)
                        <x-sens-card :sens="$item" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Pricing --}}
    @php
        $discoveryPrice = $unitPricing['discovery_price'];
        $discoveryDuration = $unitPricing['discovery_duration'];
        $regularPrice = $unitPricing['regular_price'];
        $regularDuration = $unitPricing['regular_duration'];
        $pack2Price = $packPricing['pack_2_price'];
        $pack2Sessions = $packPricing['pack_2_sessions'];
        $pack4Price = $packPricing['pack_4_price'];
        $pack4Sessions = $packPricing['pack_4_sessions'];
        $pack2PerSession = $pack2Sessions > 0 ? round($pack2Price / $pack2Sessions) : 0;
        $pack4PerSession = $pack4Sessions > 0 ? round($pack4Price / $pack4Sessions) : 0;
    @endphp
    <section class="py-16 border-t border-border">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <h2 class="text-3xl font-bold text-text-light font-secondary mb-4 text-center">Nos tarifs</h2>
            <p class="text-text-default text-center max-w-2xl mx-auto mb-12">Choisissez la formule adaptée à vos besoins.</p>

            {{-- Première séance --}}
            <div class="max-w-3xl mx-auto mb-10">
                <div class="border border-primary/30 bg-primary/5 rounded-2xl p-6 md:p-8">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <span class="text-xs font-medium text-primary bg-primary/10 px-3 py-1 rounded-full">1ère séance</span>
                            <h3 class="text-xl font-bold text-text-light mt-3 font-secondary">Séance découverte</h3>
                            <p class="mt-2 text-sm text-text-default leading-relaxed max-w-lg">
                                La première séance est plus longue afin de prendre le temps de vous accueillir, de comprendre vos attentes et d'adapter l'environnement sensoriel à vos besoins.
                            </p>
                            <p class="mt-2 text-sm text-text-default">Durée : {{ $discoveryDuration }} minutes</p>
                        </div>
                        <div class="text-center md:text-right shrink-0">
                            <span class="text-4xl font-bold text-text-light">{{ $discoveryPrice }}€</span>
                            <div class="mt-3">
                                <a href="https://sensae.cc/booking" target="_blank" class="inline-block px-6 py-3 bg-primary text-text-light rounded-xl font-medium hover:bg-primary/90 transition">
                                    Réserver
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Grille : 1 séance, Pack 2, Pack 4 --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-4xl mx-auto">
                <x-pricing-card
                    label="1 séance"
                    :price="$regularPrice"
                    :duration="$regularDuration"
                />

                <x-pricing-card
                    :label="'Pack ' . $pack2Sessions . ' séances'"
                    :price="$pack2Price"
                    :duration="$regularDuration"
                    :per-session="$pack2PerSession"
                    :highlight="true"
                    badge="Populaire"
                />

                <x-pricing-card
                    :label="'Pack ' . $pack4Sessions . ' séances'"
                    :price="$pack4Price"
                    :duration="$regularDuration"
                    :per-session="$pack4PerSession"
                    badge="Meilleur prix"
                />
            </div>
        </div>
    </section>

    {{-- Reviews --}}
    @if($reviews->count())
        <section class="py-16 border-t border-border">
            <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <h2 class="text-3xl font-bold text-text-light font-secondary mb-8 text-center">Témoignages</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($reviews as $review)
                        <x-review-card :review="$review" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- FAQ --}}
    @if($faqs->count())
        <section class="py-16 border-t border-border">
            <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <h2 class="text-3xl font-bold text-text-light font-secondary mb-8 text-center">Questions fréquentes</h2>
                <div class="max-w-3xl mx-auto space-y-3">
                    @foreach($faqs as $faq)
                        <x-faq-item :faq="$faq" />
                    @endforeach
                </div>
                <div class="text-center mt-8">
                    <a href="{{ route('faq') }}" class="text-primary hover:underline">Voir toutes les questions</a>
                </div>
            </div>
        </section>
    @endif
@endsection
