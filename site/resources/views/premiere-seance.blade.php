@extends('layouts.immersive')

@push('structured_data')
    @php
        $breadcrumbSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Accueil', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Première séance'],
            ],
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
@endpush

@section('meta')
    <title>Comment se passe une première séance Snoezelen ? — sensaë</title>
    <meta name="description" content="Découvrez le déroulement d'une première séance Snoezelen : accueil, découverte sensorielle, exploration, apaisement et retour. On ne peut pas rater une séance.">
    <meta property="og:title" content="Votre première séance Snoezelen — sensaë">
    <meta property="og:description" content="Pas de protocole. Pas de performance. Juste un moment pour vous.">
    <meta property="og:type" content="website">
@endsection

@section('sections')
    {{-- Section 0 — Intro --}}
    <section class="ps-section" data-ps="0" style="background:#050208;">
        <div class="ps-section-inner">
            <h1 class="ps-title">Votre première séance</h1>
            <p class="ps-subtitle">Pas de protocole. Pas de performance.<br>Juste vous.</p>
        </div>
        <button class="ps-start" type="button">
            <span class="ps-start-btn">Commencer</span>
            <div class="ps-start-chevron"></div>
        </button>
        <div class="ps-orb" style="width:300px;height:300px;background:radial-gradient(circle,rgba(114,26,214,0.2),transparent 70%);top:60%;left:50%;transform:translateX(-50%);"></div>
    </section>

    {{-- Section 1 — Accueil --}}
    <section class="ps-section" data-ps="1" style="background:#0d0520;">
        <div class="ps-num" aria-hidden="true">01</div>
        <div class="ps-orb" style="width:400px;height:400px;background:radial-gradient(circle,rgba(114,26,214,0.18),transparent 70%);top:30%;right:-100px;"></div>
        <div class="ps-section-inner">
            <h2 class="ps-title">Accueil et prise de contact</h2>
            <p class="ps-line">Le cadre est posé : <strong>durée, liberté, zéro obligation</strong></p>
            <p class="ps-line">Vos <strong>sensibilités</strong> sont recueillies avec attention</p>
            <p class="ps-line">Votre état émotionnel et corporel est <strong>observé</strong></p>
            <p class="ps-small">La première séance dure en moyenne 45 à 60 minutes</p>
        </div>
    </section>

    {{-- Section 2 — Découverte --}}
    <section class="ps-section" data-ps="2" style="background:#1a0a3e;">
        <div class="ps-num" aria-hidden="true">02</div>
        <div class="ps-orb" style="width:350px;height:350px;background:radial-gradient(circle,rgba(210,153,255,0.12),transparent 70%);top:20%;left:-50px;"></div>
        <div class="ps-orb" style="width:250px;height:250px;background:radial-gradient(circle,rgba(114,26,214,0.15),transparent 70%);bottom:15%;right:10%;"></div>
        <div class="ps-section-inner">
            <h2 class="ps-title">Découverte de l'espace</h2>
            <p class="ps-line">Vous entrez dans un espace conçu pour éveiller vos sens en douceur</p>
            <div style="display:flex;flex-wrap:wrap;gap:12px;justify-content:center;margin:1.5rem 0;">
                <span class="ps-line" style="margin:0;padding:8px 18px;border:1px solid rgba(114,26,214,0.25);border-radius:999px;font-size:0.8rem;" data-ps-item>Lumière tamisée</span>
                <span class="ps-line" style="margin:0;padding:8px 18px;border:1px solid rgba(210,153,255,0.2);border-radius:999px;font-size:0.8rem;" data-ps-item>Colonnes à bulles</span>
                <span class="ps-line" style="margin:0;padding:8px 18px;border:1px solid rgba(114,26,214,0.25);border-radius:999px;font-size:0.8rem;" data-ps-item>Fibres optiques</span>
                <span class="ps-line" style="margin:0;padding:8px 18px;border:1px solid rgba(210,153,255,0.2);border-radius:999px;font-size:0.8rem;" data-ps-item>Sons naturels</span>
                <span class="ps-line" style="margin:0;padding:8px 18px;border:1px solid rgba(114,26,214,0.25);border-radius:999px;font-size:0.8rem;" data-ps-item>Odeurs légères</span>
                <span class="ps-line" style="margin:0;padding:8px 18px;border:1px solid rgba(210,153,255,0.2);border-radius:999px;font-size:0.8rem;" data-ps-item>Fauteuils enveloppants</span>
            </div>
            <p class="ps-line" style="margin-top:1rem;">Je ne vous impose <strong>rien</strong>.<br>Une stimulation à la fois, si nécessaire.</p>
        </div>
    </section>

    {{-- Section 3 — Exploration --}}
    <section class="ps-section" data-ps="3" style="background:#0a1a2e;">
        <div class="ps-num" aria-hidden="true">03</div>
        <div class="ps-orb" style="width:300px;height:300px;background:radial-gradient(circle,rgba(26,107,122,0.2),transparent 70%);top:40%;left:10%;"></div>
        <div class="ps-section-inner">
            <h2 class="ps-title">Exploration sensorielle</h2>
            <div class="ps-duo">
                <div class="ps-duo-card" style="border:1px solid rgba(255,250,245,0.1);background:rgba(255,250,245,0.03);">
                    <h3>Libre</h3>
                    <p>Vous choisissez spontanément ce qui vous attire</p>
                </div>
                <div class="ps-duo-card" style="border:1px solid rgba(114,26,214,0.35);background:rgba(114,26,214,0.08);">
                    <h3>Guidée</h3>
                    <p>La praticienne propose, ajuste et sécurise</p>
                </div>
            </div>
            <p class="ps-subtitle" style="margin-top:1.5rem;">Aucun exercice structuré.<br>Aucune attente de réussite.</p>
        </div>
    </section>

    {{-- Section 4 — Apaisement --}}
    <section class="ps-section" data-ps="4" style="background:#080810;">
        <div class="ps-num" aria-hidden="true">04</div>
        <div class="ps-pulse"></div>
        <div class="ps-pulse-outer"></div>
        <div class="ps-section-inner" style="position:relative;z-index:10;">
            <h2 class="ps-title">Phase d'apaisement</h2>
            <p class="ps-line">La lumière s'adoucit encore</p>
            <p class="ps-line">Les stimulations se réduisent</p>
            <p class="ps-line">Le corps et l'esprit reviennent <strong>doucement</strong></p>
        </div>
    </section>

    {{-- Section 5 — Retour --}}
    <section class="ps-section" data-ps="5" style="background:#0f0a08;">
        <div class="ps-num" aria-hidden="true">05</div>
        <div class="ps-orb" style="width:400px;height:400px;background:radial-gradient(circle,rgba(255,214,153,0.08),transparent 70%);bottom:10%;left:50%;transform:translateX(-50%);"></div>
        <div class="ps-section-inner">
            <h2 class="ps-title">Retour et verbalisation</h2>
            <p class="ps-line">Un échange sur les ressentis, les stimulations appréciées ou inconfortables</p>
            <p class="ps-line" style="margin-top:0.5rem;margin-bottom:0.5rem;">Avec les enfants ou les personnes en situation de handicap, l'évaluation repose sur <strong>l'observation</strong></p>
            <div class="ps-pills">
                <div class="ps-pill"><span class="ps-pill-dot"></span>Le regard</div>
                <div class="ps-pill"><span class="ps-pill-dot"></span>La posture</div>
                <div class="ps-pill"><span class="ps-pill-dot"></span>Les vocalisations</div>
                <div class="ps-pill"><span class="ps-pill-dot"></span>Le relâchement</div>
            </div>
        </div>
    </section>

    {{-- Section 6 — Closing --}}
    <section class="ps-section" data-ps="6" style="background:#0a0810;">
        <div class="ps-section-inner">
            <h2 class="ps-title" style="font-size:clamp(2rem,6vw,3.8rem);">On ne peut pas<br>« rater » une séance</h2>
            <p class="ps-subtitle">Il n'y a rien à réussir, rien à accomplir.<br>Juste ressentir, à son rythme.</p>
            <div class="ps-cta">
                <x-cta-booking :star="true" />
            </div>
        </div>
        <div class="ps-orb" style="width:500px;height:500px;background:radial-gradient(circle,rgba(114,26,214,0.1),transparent 70%);top:50%;left:50%;transform:translate(-50%,-50%);"></div>
    </section>
@endsection
