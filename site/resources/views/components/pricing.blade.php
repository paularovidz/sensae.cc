{{-- Pricing --}}
@php
    $pricing = app(\App\Services\SensaeApiService::class)->getPricing();
    $sessions = $pricing['sessions'] ?? [];
    $packs = $pricing['packs'] ?? [];

    $discoveryPrice = (int) ($sessions['discovery']['price'] ?? 55);
    $discoveryDuration = (int) ($sessions['discovery']['duration'] ?? 75);
    $regularPrice = (int) ($sessions['regular']['price'] ?? 45);
    $regularDuration = (int) ($sessions['regular']['duration'] ?? 45);
    $pack2Price = (int) ($packs['pack_2']['price'] ?? 110);
    $pack2Sessions = (int) ($packs['pack_2']['sessions'] ?? 2);
    $pack4Price = (int) ($packs['pack_4']['price'] ?? 200);
    $pack4Sessions = (int) ($packs['pack_4']['sessions'] ?? 4);
    $pack2PerSession = $pack2Sessions > 0 ? round($pack2Price / $pack2Sessions) : 0;
    $pack4PerSession = $pack4Sessions > 0 ? round($pack4Price / $pack4Sessions) : 0;
@endphp
<section data-animate-border class="py-16">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
        <h2 data-animate="fade-up" class="text-3xl font-bold text-text-light font-secondary mb-4 text-center">Nos tarifs
        </h2>
        <p data-animate="fade-up" data-animate-delay="0.1" class="text-text-default text-center max-w-2xl mx-auto mb-12">
            Choisissez la formule adaptée à vos besoins.</p>

        {{-- Séance découverte — bandeau pleine largeur --}}
        <div data-animate="fade-up"
            class="pricing-card flex flex-col md:flex-row md:items-center md:justify-between gap-6 rounded-3xl border border-primary/30 bg-primary/10 px-8 py-10 mb-5 max-w-6xl mx-auto">
            <div class="space-y-3">
                <span class="inline-block text-xs font-medium text-primary bg-primary/10 px-3 py-1 rounded-full">1ère
                    séance</span>
                <h3 class="text-xl md:text-2xl font-medium text-text-light font-secondary">Séance découverte</h3>
                <p class="text-text-default text-sm leading-relaxed max-w-lg">Première séance plus longue pour vous
                    accueillir, comprendre vos attentes et adapter l'environnement sensoriel.</p>
                <p class="text-xs text-text-default/50">{{ $discoveryDuration }} min</p>
            </div>
            <div class="flex flex-col items-start md:items-end gap-4 shrink-0">
                <div class="flex flex-wrap items-end gap-1.5">
                    <span
                        class="text-4xl md:text-5xl font-medium text-text-light font-secondary leading-none">{{ $discoveryPrice }}<span
                            class="text-3xl md:text-4xl">€</span></span>
                </div>
                <a href="https://sensae.cc/booking" target="_blank"
                    class="group inline-flex items-center justify-between gap-4 px-6 py-3.5 rounded-full font-medium transition bg-primary text-text-light hover:opacity-90">
                    <span>Réserver</span>
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-white/15">
                        <img src="{{ asset('storage/star.svg') }}" alt=""
                            class="h-4 w-4 transition-all duration-300 group-hover:rotate-180" loading="lazy"
                            decoding="async">
                    </span>
                </a>
            </div>
        </div>

        {{-- Séances régulières + packs --}}
        <div data-animate-grid class="grid grid-cols-1 md:grid-cols-3 gap-5 max-w-6xl mx-auto">
            <x-pricing-card label="1 séance" :price="$regularPrice" :duration="$regularDuration"
                description="Pour une envie de se reconnecter à soi-même ou un moment de décompression sensorielle."
                unit="/ séance" />

            <x-pricing-card :label="'Pack ' . $pack2Sessions . ' séances'" :price="$pack2PerSession" :duration="$regularDuration"
                description="Un suivi régulier pour approfondir les bienfaits sensoriels et installer une routine de bien-être."
                :total-price="$pack2Price" :total-sessions="$pack2Sessions" :highlight="true" badge="Populaire" unit="/ séance" />

            <x-pricing-card :label="'Pack ' . $pack4Sessions . ' séances'" :price="$pack4PerSession" :duration="$regularDuration"
                description="L'engagement idéal pour un accompagnement complet et des résultats durables."
                :total-price="$pack4Price" :total-sessions="$pack4Sessions" badge="Meilleur prix" unit="/ séance" />
        </div>
    </div>
</section>
