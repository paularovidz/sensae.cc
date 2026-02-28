@extends('layouts.immersive')

@push('structured_data')
    @php
        $breadcrumbSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Accueil', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Snoezelen & Alzheimer'],
            ],
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
@endpush

@section('meta')
    <title>Séance Snoezelen et Alzheimer — sensaë</title>
    <meta name="description" content="Comment le Snoezelen accompagne les personnes atteintes d'Alzheimer. Quand les mots s'effacent, les sens restent. Une approche douce et respectueuse.">
    <meta property="og:title" content="Snoezelen et Alzheimer — sensaë">
    <meta property="og:description" content="Quand les mots s'effacent, les sens restent. Une approche sensorielle douce pour les personnes atteintes d'Alzheimer.">
    <meta property="og:type" content="website">
@endsection

@section('sections')
    {{-- Section 0 — Intro --}}
    <section class="ps-section" data-ps="0" style="background:#050208;">
        <div class="ps-section-inner">
            <h1 class="ps-title">Quand les mots s'effacent,<br>les sens restent</h1>
            <p class="ps-subtitle">Le Snoezelen et la maladie d'Alzheimer.<br>Une autre façon de communiquer.</p>
        </div>
        <button class="ps-start" type="button">
            <span class="ps-start-btn">Commencer</span>
            <div class="ps-start-chevron"></div>
        </button>
        <div class="ps-orb" style="width:300px;height:300px;background:radial-gradient(circle,rgba(255,214,153,0.15),transparent 70%);top:60%;left:50%;transform:translateX(-50%);"></div>
    </section>

    {{-- Section 1 — Pourquoi --}}
    <section class="ps-section" data-ps="1" style="background:#100a08;">
        <div class="ps-num" aria-hidden="true">01</div>
        <div class="ps-orb" style="width:400px;height:400px;background:radial-gradient(circle,rgba(255,214,153,0.12),transparent 70%);top:30%;right:-100px;"></div>
        <div class="ps-section-inner">
            <h2 class="ps-title">Pourquoi le Snoezelen</h2>
            <p class="ps-line">La mémoire cognitive s'estompe, mais la <strong>mémoire sensorielle persiste</strong></p>
            <p class="ps-line">Une odeur, une mélodie, une texture peuvent <strong>raviver des émotions</strong></p>
            <p class="ps-line">Le Snoezelen offre un canal de <strong>communication non-verbal</strong></p>
            <p class="ps-small">Approche utilisée dans de nombreux établissements spécialisés</p>
        </div>
    </section>

    {{-- Section 2 — L'environnement --}}
    <section class="ps-section" data-ps="2" style="background:#150d1e;">
        <div class="ps-num" aria-hidden="true">02</div>
        <div class="ps-orb" style="width:350px;height:350px;background:radial-gradient(circle,rgba(255,214,153,0.1),transparent 70%);top:20%;left:-50px;"></div>
        <div class="ps-orb" style="width:250px;height:250px;background:radial-gradient(circle,rgba(114,26,214,0.12),transparent 70%);bottom:15%;right:10%;"></div>
        <div class="ps-section-inner">
            <h2 class="ps-title">Un environnement familier</h2>
            <p class="ps-line">L'espace est conçu pour rassurer, jamais pour désorienter</p>
            <div style="display:flex;flex-wrap:wrap;gap:12px;justify-content:center;margin:1.5rem 0;">
                <span class="ps-line" style="margin:0;padding:8px 18px;border:1px solid rgba(255,214,153,0.25);border-radius:999px;font-size:0.8rem;" data-ps-item>Lumière chaleureuse</span>
                <span class="ps-line" style="margin:0;padding:8px 18px;border:1px solid rgba(255,214,153,0.2);border-radius:999px;font-size:0.8rem;" data-ps-item>Mélodies connues</span>
                <span class="ps-line" style="margin:0;padding:8px 18px;border:1px solid rgba(255,214,153,0.25);border-radius:999px;font-size:0.8rem;" data-ps-item>Odeurs d'enfance</span>
                <span class="ps-line" style="margin:0;padding:8px 18px;border:1px solid rgba(255,214,153,0.2);border-radius:999px;font-size:0.8rem;" data-ps-item>Textures douces</span>
                <span class="ps-line" style="margin:0;padding:8px 18px;border:1px solid rgba(255,214,153,0.25);border-radius:999px;font-size:0.8rem;" data-ps-item>Rythme lent</span>
                <span class="ps-line" style="margin:0;padding:8px 18px;border:1px solid rgba(255,214,153,0.2);border-radius:999px;font-size:0.8rem;" data-ps-item>Présence rassurante</span>
            </div>
            <p class="ps-line" style="margin-top:1rem;">Les stimulations sont <strong>simples et rassurantes</strong>.<br>Elles font écho à des souvenirs <strong>profondément ancrés</strong>.</p>
        </div>
    </section>

    {{-- Section 3 — L'accompagnement --}}
    <section class="ps-section" data-ps="3" style="background:#0a1a2e;">
        <div class="ps-num" aria-hidden="true">03</div>
        <div class="ps-orb" style="width:300px;height:300px;background:radial-gradient(circle,rgba(26,107,122,0.2),transparent 70%);top:40%;left:10%;"></div>
        <div class="ps-section-inner">
            <h2 class="ps-title">L'accompagnement</h2>
            <div class="ps-duo">
                <div class="ps-duo-card" style="border:1px solid rgba(255,214,153,0.2);background:rgba(255,214,153,0.04);">
                    <h3>Avec un proche</h3>
                    <p>Un moment de connexion partagé, au-delà de la maladie</p>
                </div>
                <div class="ps-duo-card" style="border:1px solid rgba(114,26,214,0.3);background:rgba(114,26,214,0.06);">
                    <h3>Avec la praticienne</h3>
                    <p>Un regard professionnel, attentif aux moindres réactions</p>
                </div>
            </div>
            <p class="ps-subtitle" style="margin-top:1.5rem;">La personne n'a rien à comprendre ni à retenir.<br>Juste à ressentir, dans l'instant.</p>
        </div>
    </section>

    {{-- Section 4 — Apaisement --}}
    <section class="ps-section" data-ps="4" style="background:#080810;">
        <div class="ps-num" aria-hidden="true">04</div>
        <div class="ps-pulse"></div>
        <div class="ps-pulse-outer"></div>
        <div class="ps-section-inner" style="position:relative;z-index:10;">
            <h2 class="ps-title">Un apaisement profond</h2>
            <p class="ps-line">L'agitation et l'anxiété <strong>diminuent</strong> dans cet environnement</p>
            <p class="ps-line">Les gestes deviennent plus <strong>calmes</strong>, le visage plus <strong>détendu</strong></p>
            <p class="ps-line">Des moments de <strong>présence et de lucidité</strong> peuvent émerger</p>
        </div>
    </section>

    {{-- Section 5 — Effets --}}
    <section class="ps-section" data-ps="5" style="background:#0f0a08;">
        <div class="ps-num" aria-hidden="true">05</div>
        <div class="ps-orb" style="width:400px;height:400px;background:radial-gradient(circle,rgba(255,214,153,0.08),transparent 70%);bottom:10%;left:50%;transform:translateX(-50%);"></div>
        <div class="ps-section-inner">
            <h2 class="ps-title">Ce que l'on observe</h2>
            <p class="ps-line">Chaque personne réagit différemment. Mais certains <strong>effets reviennent</strong></p>
            <p class="ps-line" style="margin-top:0.5rem;margin-bottom:0.5rem;">Des moments de <strong>connexion</strong> que l'on croyait perdus</p>
            <div class="ps-pills">
                <div class="ps-pill"><span class="ps-pill-dot"></span>Apaisement</div>
                <div class="ps-pill"><span class="ps-pill-dot"></span>Sourires</div>
                <div class="ps-pill"><span class="ps-pill-dot"></span>Regards prolongés</div>
                <div class="ps-pill"><span class="ps-pill-dot"></span>Gestes tendres</div>
            </div>
        </div>
    </section>

    {{-- Section 6 — Closing --}}
    <section class="ps-section" data-ps="6" style="background:#0a0810;">
        <div class="ps-section-inner">
            <h2 class="ps-title" style="font-size:clamp(2rem,6vw,3.8rem);">On ne perd jamais<br>la capacité de ressentir</h2>
            <p class="ps-subtitle">Le Snoezelen ne guérit pas.<br>Il offre des instants de présence, de douceur et de dignité.</p>
            <div class="ps-cta">
                <x-cta-booking :star="true" />
            </div>
        </div>
        <div class="ps-orb" style="width:500px;height:500px;background:radial-gradient(circle,rgba(255,214,153,0.1),transparent 70%);top:50%;left:50%;transform:translate(-50%,-50%);"></div>
    </section>
@endsection
