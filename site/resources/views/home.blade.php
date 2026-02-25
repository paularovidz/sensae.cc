@extends('layouts.app')

@section('title', $seo['seo_title'] ?? 'sensaë - Salle Snoezelen')

@section('content')
    {{-- Hero --}}
    <section data-hero class="hero relative banner-gradient">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 text-center">
            <h1 data-hero-h1 class="text-xs uppercase mb-2">sensaë salle snoezelen à Audruicq</h1>
            <p data-hero-title class="text-3xl md:text-6xl font-bold text-text-light leading-tight">Écouter les besoins,
                répondre aux sens, <span class="font-secondary">prendre soin autrement</span></p>
            <p data-hero-subtitle class="mt-6 text-lg text-text-default max-w-2xl mx-auto">Une expérience multisensorielle
                sur-mesure pour gérer les émotions, réduire l’anxiété et retrouver un équilibre intérieur.</p>
            <div data-hero-cta class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
                <x-cta-contact label="Ouverture en août" :star="true" />
                <a href="#piliers"
                    class="px-8 py-3 border border-border text-text-light rounded-full font-medium hover:bg-accent transition">En
                    savoir plus</a>
            </div>
            <x-hero-image slug="snoezelen-sensae" alt="Salle Snoezelen sensaë" />
        </div>
    </section>

    <x-pillars />

    <x-pourquoi-nous />

    {{-- Roue des Sens --}}
    @if ($sens->count())
        <section id="comprendre" data-wheel data-animate-border class="relative py-16">
            <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <h2 data-animate="fade-up" class="text-3xl font-bold text-text-light font-secondary mb-4 text-center">
                    Comprendre les sens</h2>
                <p data-animate="fade-up" data-animate-delay="0.1"
                    class="text-text-default text-center max-w-2xl mx-auto mb-12">
                    Six dimensions sensorielles au cœur de l'approche Snoezelen.
                </p>
            </div>

            <x-sens-wheel :sens="$sens" :links="true" />
        </section>
    @endif

    <x-pricing :discovery-only="true" />

    {{-- Reviews --}}
    @if ($reviews->count())
        <section data-animate-border class="py-16 border-t border-border">
            <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <h2 data-animate="fade-up" class="text-3xl font-bold text-text-light font-secondary mb-8 text-center">
                    Témoignages</h2>
                <div data-animate-grid class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($reviews as $review)
                        <x-review-card :review="$review" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- FAQ --}}
    <section class="section-gradient relative">
        <div class="container space-y-12 md:space-y-16">
            <div class="mx-auto max-w-2xl text-center" data-animate="fade-up">
                <x-pill text="FAQ" />
                <h2>Foire <strong>Aux Questions</strong></h2>
            </div>
            <x-faq :faqs="$faqs" />
        </div>
    </section>

    <x-cta-section />
@endsection
