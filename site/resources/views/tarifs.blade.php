@extends('layouts.app')

@section('title', 'Tarifs - sensaë')
@section('hideGlow', 'true')

@section('breadcrumb')
    <li><a href="/" class="hover:text-primary transition">Accueil</a></li>
    <li class="before:content-['/'] before:mx-1.5">Tarifs</li>
@endsection

@section('content')
<x-breadcrumb :items="[
    ['name' => 'Accueil', 'url' => url('/')],
    ['name' => 'Tarifs'],
]" />
    {{-- Hero / Intro --}}
    <section class="hero relative banner-gradient">
        <div class="container space-y-12 md:space-y-16">
            <div class="mx-auto max-w-2xl text-center" data-animate="fade-up">
                <x-pill text="Tarifs" />
                <h2 class="text-white">Nos <span class="font-secondary italic">tarifs</span></h2>
                <p class="text-text-default mt-4">Des formules adaptées à chaque besoin. Chaque séance est personnalisée et se déroule dans un cadre sensoriel unique.</p>
            </div>

            <x-hero-image slug="snoezelen-sensae" alt="Salle Snoezelen sensaë" />
        </div>
    </section>

    {{-- Ce qui vous attend --}}
    <section class="section-gradient relative">
        <div class="container space-y-12 md:space-y-16">
            <div class="mx-auto max-w-2xl text-center" data-animate="fade-up">
                <x-pill text="L'espace" />
                <h2>Ce qui vous <strong>attend</strong></h2>
                <p class="text-text-default mt-4">Une salle entièrement pensée pour stimuler vos sens en douceur. Chaque élément est choisi pour créer une bulle de bien-être.</p>
            </div>

            <div class="grid gap-x-12 gap-y-10 md:grid-cols-2 lg:grid-cols-3" data-animate-grid>
                <div class="border-primary/40 before:bg-primary relative space-y-5 before:absolute before:-left-px before:content-[''] last:border-b-0 last:pb-0 max-sm:border-b max-sm:pb-10 before:max-sm:bottom-0 before:max-sm:h-px before:max-sm:w-1/4 last:before:max-sm:hidden sm:border-l sm:pl-10 before:sm:top-1/2 before:sm:h-1/4 before:sm:w-px before:sm:-translate-y-1/2">
                    <h3 class="h4">Colonne à bulles</h3>
                    <p>Tubes lumineux aux couleurs changeantes, captivants et apaisants. Les bulles montent lentement et invitent à la contemplation.</p>
                </div>
                <div class="border-primary/40 before:bg-primary relative space-y-5 before:absolute before:-left-px before:content-[''] last:border-b-0 last:pb-0 max-sm:border-b max-sm:pb-10 before:max-sm:bottom-0 before:max-sm:h-px before:max-sm:w-1/4 last:before:max-sm:hidden sm:border-l sm:pl-10 before:sm:top-1/2 before:sm:h-1/4 before:sm:w-px before:sm:-translate-y-1/2">
                    <h3 class="h4">Fibres optiques</h3>
                    <p>Des centaines de filaments lumineux doux au toucher. Parfaits pour stimuler la vue et le toucher simultanément.</p>
                </div>
                <div class="border-primary/40 before:bg-primary relative space-y-5 before:absolute before:-left-px before:content-[''] last:border-b-0 last:pb-0 max-sm:border-b max-sm:pb-10 before:max-sm:bottom-0 before:max-sm:h-px before:max-sm:w-1/4 last:before:max-sm:hidden sm:border-l sm:pl-10 before:sm:top-1/2 before:sm:h-1/4 before:sm:w-px before:sm:-translate-y-1/2">
                    <h3 class="h4">Projecteur sensoriel</h3>
                    <p>Projections de formes et de couleurs sur les murs et le plafond. Un univers visuel immersif qui évolue au fil de la séance.</p>
                </div>
                <div class="border-primary/40 before:bg-primary relative space-y-5 before:absolute before:-left-px before:content-[''] last:border-b-0 last:pb-0 max-sm:border-b max-sm:pb-10 before:max-sm:bottom-0 before:max-sm:h-px before:max-sm:w-1/4 last:before:max-sm:hidden sm:border-l sm:pl-10 before:sm:top-1/2 before:sm:h-1/4 before:sm:w-px before:sm:-translate-y-1/2">
                    <h3 class="h4">Matelas & coussins</h3>
                    <p>Un espace douillet avec matelas épais, coussins moelleux et couvertures aux textures variées pour un confort total.</p>
                </div>
                <div class="border-primary/40 before:bg-primary relative space-y-5 before:absolute before:-left-px before:content-[''] last:border-b-0 last:pb-0 max-sm:border-b max-sm:pb-10 before:max-sm:bottom-0 before:max-sm:h-px before:max-sm:w-1/4 last:before:max-sm:hidden sm:border-l sm:pl-10 before:sm:top-1/2 before:sm:h-1/4 before:sm:w-px before:sm:-translate-y-1/2">
                    <h3 class="h4">Diffuseur d'arômes</h3>
                    <p>Des senteurs douces et naturelles diffusées dans l'espace. L'olfaction est un sens puissant pour éveiller les émotions et la mémoire.</p>
                </div>
                <div class="border-primary/40 before:bg-primary relative space-y-5 before:absolute before:-left-px before:content-[''] last:border-b-0 last:pb-0 max-sm:border-b max-sm:pb-10 before:max-sm:bottom-0 before:max-sm:h-px before:max-sm:w-1/4 last:before:max-sm:hidden sm:border-l sm:pl-10 before:sm:top-1/2 before:sm:h-1/4 before:sm:w-px before:sm:-translate-y-1/2">
                    <h3 class="h4">Musique & sons</h3>
                    <p>Ambiances sonores adaptées : musique relaxante, sons de la nature, vibrations. Le son enveloppe l'espace et accompagne la détente.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Pricing --}}
    <x-pricing />

    {{-- Offrir une séance --}}
    <section class="relative">
        <div class="container space-y-12 md:space-y-16">
            <div class="mx-auto max-w-2xl text-center" data-animate="fade-up">
                <x-pill text="Idée cadeau" />
                <h2>Offrir une <strong>séance</strong></h2>
                <p class="text-text-default mt-4">Un cadeau qui ne s'emballe pas, mais qui se vit. Offrez une expérience sensorielle unique à un proche.</p>
            </div>

            <div class="grid gap-14 lg:grid-cols-2" data-animate-grid>
                <a href="{{ route('cadeau-naissance') }}" data-tilt class="group grid overflow-hidden rounded-3xl bg-[linear-gradient(-20deg,theme(colors.primary/35%),theme(colors.body))] transition">
                    <div class="px-8 py-10 lg:px-10 lg:py-10 xl:px-16 xl:py-16 space-y-6">
                        <span class="inline-block text-xs font-medium text-primary bg-primary/10 px-3 py-1 rounded-full">Naissance</span>
                        <h3 class="prose-strong:font-medium prose-strong:text-secondary">Cadeau naissance <strong>sensoriel</strong></h3>
                        <div class="prose-styles">
                            <p>Un éveil sensoriel en douceur pour bébé et ses parents. Un moment de connexion inoubliable dès les premières semaines de vie.</p>
                        </div>
                        <span class="inline-flex items-center gap-2 text-sm text-primary group-hover:gap-3 transition-all">
                            Découvrir
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </span>
                    </div>
                </a>

                <a href="{{ route('cadeau-femme-enceinte') }}" data-tilt class="group grid overflow-hidden rounded-3xl bg-[linear-gradient(30deg,_theme(colors.primary/2%)_20%,_theme(colors.primary/80%)_100%)] transition">
                    <div class="px-8 py-10 lg:px-10 lg:py-10 xl:px-16 xl:py-16 space-y-6">
                        <span class="inline-block text-xs font-medium text-primary bg-primary/10 px-3 py-1 rounded-full">Future maman</span>
                        <h3 class="prose-strong:font-medium prose-strong:text-secondary">Cadeau femme <strong>enceinte</strong></h3>
                        <div class="prose-styles">
                            <p>Un cocon sensoriel pour future maman. Détente profonde, connexion avec bébé et bien-être prénatal dans un espace dédié.</p>
                        </div>
                        <span class="inline-flex items-center gap-2 text-sm text-primary group-hover:gap-3 transition-all">
                            Découvrir
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </span>
                    </div>
                </a>
            </div>
        </div>
    </section>

    {{-- Séances spécialisées --}}
    <section class="section-gradient relative">
        <div class="container space-y-12 md:space-y-16">
            <div class="mx-auto max-w-2xl text-center" data-animate="fade-up">
                <x-pill text="Accompagnement" />
                <h2>Séances <strong>spécialisées</strong></h2>
                <p class="text-text-default mt-4">L'approche Snoezelen s'adapte à chaque personne. Je propose des séances conçues pour des besoins spécifiques.</p>
            </div>

            <div class="grid gap-14 lg:grid-cols-2" data-animate-grid>
                <a href="{{ route('snoezelen-autisme') }}" data-tilt class="group grid overflow-hidden rounded-3xl bg-[linear-gradient(82.48deg,_theme(colors.primary/0%)_-1.1%,_theme(colors.primary/36%)_96.45%)] transition">
                    <div class="px-8 py-10 lg:px-10 lg:py-10 xl:px-16 xl:py-16 space-y-6">
                        <span class="inline-block text-xs font-medium text-primary bg-primary/10 px-3 py-1 rounded-full">TSA</span>
                        <h3 class="prose-strong:font-medium prose-strong:text-secondary">Snoezelen & <strong>Autisme</strong></h3>
                        <div class="prose-styles">
                            <p>Un espace adapté, sans contrainte ni attente. L'enfant explore ses sens à son propre rythme, dans un environnement sécurisant et prévisible.</p>
                        </div>
                        <span class="inline-flex items-center gap-2 text-sm text-primary group-hover:gap-3 transition-all">
                            En savoir plus
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </span>
                    </div>
                </a>

                <a href="{{ route('snoezelen-alzheimer') }}" data-tilt class="group grid overflow-hidden rounded-3xl bg-[linear-gradient(-20deg,theme(colors.primary/35%),theme(colors.body))] transition">
                    <div class="px-8 py-10 lg:px-10 lg:py-10 xl:px-16 xl:py-16 space-y-6">
                        <span class="inline-block text-xs font-medium text-primary bg-primary/10 px-3 py-1 rounded-full">Alzheimer</span>
                        <h3 class="prose-strong:font-medium prose-strong:text-secondary">Snoezelen & <strong>Alzheimer</strong></h3>
                        <div class="prose-styles">
                            <p>Stimuler la mémoire sensorielle, apaiser l'anxiété et maintenir le lien. Une approche douce pour les personnes atteintes de la maladie d'Alzheimer.</p>
                        </div>
                        <span class="inline-flex items-center gap-2 text-sm text-primary group-hover:gap-3 transition-all">
                            En savoir plus
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </span>
                    </div>
                </a>
            </div>
        </div>
    </section>

    {{-- FAQ Tarifs --}}
    @if($faqs->count())
        <section class="section-gradient relative">
            <div class="container space-y-12 md:space-y-16">
                <div class="mx-auto max-w-2xl text-center" data-animate="fade-up">
                    <x-pill text="FAQ" />
                    <h2>Questions sur les <strong>tarifs</strong></h2>
                </div>
                <x-faq :faqs="$faqs" />
            </div>
        </section>
    @endif

    {{-- CTA --}}
    <x-cta-section />
@endsection
