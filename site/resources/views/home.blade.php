@extends('layouts.app')

@section('title', $seo['seo_title'] ?? 'sensaë - Salle Snoezelen')

@section('content')
    {{-- Hero --}}
    <section data-hero class="hero relative banner-gradient">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 text-center">
            <h1 data-hero-h1 class="text-xs uppercase mb-2">sensaë salle snoezelen à Audruicq</h1>
            <p data-hero-title class="text-4xl md:text-6xl font-bold text-text-light leading-tight">Écouter les besoins, répondre aux sens, <span class="font-secondary">prendre soin autrement</span></p>
            <p data-hero-subtitle class="mt-6 text-lg text-text-default max-w-2xl mx-auto">Une expérience multisensorielle sur-mesure pour gérer les émotions, réduire l’anxiété et retrouver un équilibre intérieur.</p>
            <div data-hero-cta class="mt-8 flex items-center justify-center gap-4">
                <x-cta-contact label="Ouverture en août" :star="true" />
                <a href="#comprendre" class="px-8 py-3 border border-border text-text-light rounded-full font-medium hover:bg-accent transition">En savoir plus</a>
            </div>
            <div data-hero-image class="relative mx-4 mt-12 -mb-4 max-w-screen-xl md:m-0 md:mx-auto md:mt-20">
                <div class="relative">
                    <div class="overflow-hidden rounded-t-2xl bg-black border-2 border-b-0 border-[#0e0c0c] md:rounded-t-3xl">
                        <x-image slug="snoezelen-sensae" alt="Salle Snoezelen sensaë" class="w-full h-full object-cover" loading="eager" fetchpriority="high" />
                    </div>
                    <div data-hero-image-glow class="hero-image-glow"></div>
                </div>
            </div>
        </div>
    </section>

    <x-pillars />

    <x-pourquoi-nous />

    {{-- Roue des Sens --}}
    @if($sens->count())
        <section id="comprendre" data-wheel data-animate-border class="relative py-16">
            <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <h2 data-animate="fade-up" class="text-3xl font-bold text-text-light font-secondary mb-4 text-center">Comprendre les sens</h2>
                <p data-animate="fade-up" data-animate-delay="0.1" class="text-text-default text-center max-w-2xl mx-auto mb-12">
                    Six dimensions sensorielles au cœur de l'approche Snoezelen.
                </p>
            </div>

            <x-sens-wheel :sens="$sens" :links="true" />
        </section>
    @endif

    <x-pricing />

    {{-- Reviews --}}
    @if($reviews->count())
        <section data-animate-border class="py-16 border-t border-border">
            <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <h2 data-animate="fade-up" class="text-3xl font-bold text-text-light font-secondary mb-8 text-center">Témoignages</h2>
                <div data-animate-grid class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($reviews as $review)
                        <x-review-card :review="$review" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- FAQ --}}
    @if($faqs->count())
        <section class="section-gradient relative">
            <div class="container space-y-12 md:space-y-16">
                <div class="mx-auto max-w-2xl text-center" data-animate="fade-up">
                    <x-pill text="FAQ" />
                    <h2>Foire <strong>Aux Questions</strong></h2>
                </div>

                <div data-animate="fade-up" data-animate-delay="0.2" class="mx-auto max-w-4xl">
                    <div class="bg-primary/5 rounded-lg border border-border p-6 lg:rounded-3xl lg:p-12">
                        <div class="space-y-5">
                            @foreach($faqs as $index => $faq)
                                <x-faq-item :faq="$faq" :open="$index === 0" />
                            @endforeach
                        </div>
                    </div>
                    <div class="text-center mt-8">
                        <a href="{{ route('faq') }}" class="text-primary hover:underline">Voir toutes les questions</a>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <x-cta-section />
@endsection
