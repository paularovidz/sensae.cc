@extends('layouts.immersive')

@section('meta')
    <title>Séance Snoezelen pour enfant autiste — sensaë</title>
    <meta name="description" content="Comment se déroule une séance Snoezelen pour un enfant autiste ? Un espace adapté, sans contrainte, pour explorer ses sens à son propre rythme.">
    <meta property="og:title" content="Snoezelen et autisme — sensaë">
    <meta property="og:description" content="Un espace adapté, sans attente ni contrainte. L'enfant explore à son rythme.">
    <meta property="og:type" content="website">
@endsection

@section('sections')
    {{-- Section 0 — Intro --}}
    <section class="ps-section" data-ps="0" style="background:#050208;">
        <div class="ps-section-inner">
            <h1 class="ps-title">Snoezelen et autisme</h1>
            <p class="ps-subtitle">Un espace adapté, sans attente ni contrainte.<br>L'enfant explore à son rythme.</p>
        </div>
        <button class="ps-start" type="button">
            <span class="ps-start-btn">Commencer</span>
            <div class="ps-start-chevron"></div>
        </button>
        <div class="ps-orb" style="width:300px;height:300px;background:radial-gradient(circle,rgba(26,107,122,0.25),transparent 70%);top:60%;left:50%;transform:translateX(-50%);"></div>
    </section>

    {{-- Section 1 — Comprendre --}}
    <section class="ps-section" data-ps="1" style="background:#0a1520;">
        <div class="ps-num" aria-hidden="true">01</div>
        <div class="ps-orb" style="width:400px;height:400px;background:radial-gradient(circle,rgba(26,107,122,0.18),transparent 70%);top:30%;right:-100px;"></div>
        <div class="ps-section-inner">
            <h2 class="ps-title">Comprendre l'approche</h2>
            <p class="ps-line">Le Snoezelen n'est pas une thérapie. C'est un <strong>espace d'exploration libre</strong></p>
            <p class="ps-line">L'enfant autiste y retrouve un environnement <strong>prévisible et contrôlé</strong></p>
            <p class="ps-line">Chaque stimulation est <strong>choisie, dosée et adaptable</strong> à tout moment</p>
            <p class="ps-small">Aucune compétence n'est attendue. Aucun objectif à atteindre.</p>
        </div>
    </section>

    {{-- Section 2 — L'environnement --}}
    <section class="ps-section" data-ps="2" style="background:#0f1a30;">
        <div class="ps-num" aria-hidden="true">02</div>
        <div class="ps-orb" style="width:350px;height:350px;background:radial-gradient(circle,rgba(26,107,122,0.15),transparent 70%);top:20%;left:-50px;"></div>
        <div class="ps-orb" style="width:250px;height:250px;background:radial-gradient(circle,rgba(114,26,214,0.12),transparent 70%);bottom:15%;right:10%;"></div>
        <div class="ps-section-inner">
            <h2 class="ps-title">Un environnement adapté</h2>
            <p class="ps-line">L'espace est structuré pour réduire les sources de stress sensoriel</p>
            <div style="display:flex;flex-wrap:wrap;gap:12px;justify-content:center;margin:1.5rem 0;">
                <span class="ps-line" style="margin:0;padding:8px 18px;border:1px solid rgba(26,107,122,0.3);border-radius:999px;font-size:0.8rem;" data-ps-item>Lumière modulable</span>
                <span class="ps-line" style="margin:0;padding:8px 18px;border:1px solid rgba(26,107,122,0.25);border-radius:999px;font-size:0.8rem;" data-ps-item>Pas de bruit soudain</span>
                <span class="ps-line" style="margin:0;padding:8px 18px;border:1px solid rgba(26,107,122,0.3);border-radius:999px;font-size:0.8rem;" data-ps-item>Textures variées</span>
                <span class="ps-line" style="margin:0;padding:8px 18px;border:1px solid rgba(26,107,122,0.25);border-radius:999px;font-size:0.8rem;" data-ps-item>Repères stables</span>
                <span class="ps-line" style="margin:0;padding:8px 18px;border:1px solid rgba(26,107,122,0.3);border-radius:999px;font-size:0.8rem;" data-ps-item>Espace sécurisé</span>
                <span class="ps-line" style="margin:0;padding:8px 18px;border:1px solid rgba(26,107,122,0.25);border-radius:999px;font-size:0.8rem;" data-ps-item>Rythme libre</span>
            </div>
            <p class="ps-line" style="margin-top:1rem;">Chaque élément peut être <strong>ajouté ou retiré</strong>.<br>L'enfant n'est jamais submergé.</p>
        </div>
    </section>

    {{-- Section 3 — L'exploration --}}
    <section class="ps-section" data-ps="3" style="background:#0a1a2e;">
        <div class="ps-num" aria-hidden="true">03</div>
        <div class="ps-orb" style="width:300px;height:300px;background:radial-gradient(circle,rgba(26,107,122,0.2),transparent 70%);top:40%;left:10%;"></div>
        <div class="ps-section-inner">
            <h2 class="ps-title">L'exploration sensorielle</h2>
            <div class="ps-duo">
                <div class="ps-duo-card" style="border:1px solid rgba(26,107,122,0.25);background:rgba(26,107,122,0.06);">
                    <h3>Auto-régulation</h3>
                    <p>L'enfant choisit ce qu'il touche, regarde ou écoute</p>
                </div>
                <div class="ps-duo-card" style="border:1px solid rgba(114,26,214,0.3);background:rgba(114,26,214,0.06);">
                    <h3>Accompagnement</h3>
                    <p>J'observe, sécurise et ajuste l'environnement</p>
                </div>
            </div>
            <p class="ps-subtitle" style="margin-top:1.5rem;">Pas de consigne. Pas de « bien » ou « mal » faire.<br>Juste explorer.</p>
        </div>
    </section>

    {{-- Section 4 — Un espace sécurisant --}}
    <section class="ps-section" data-ps="4" style="background:#080810;">
        <div class="ps-num" aria-hidden="true">04</div>
        <div class="ps-pulse"></div>
        <div class="ps-pulse-outer"></div>
        <div class="ps-section-inner" style="position:relative;z-index:10;">
            <h2 class="ps-title">Un espace sécurisant</h2>
            <p class="ps-line">L'environnement Snoezelen peut aider à <strong>réduire l'anxiété</strong></p>
            <p class="ps-line">Les stimulations répétitives et prévisibles <strong>rassurent</strong></p>
            <p class="ps-line">L'enfant développe ses propres stratégies d'<strong>auto-apaisement</strong></p>
        </div>
    </section>

    {{-- Section 5 — Observations --}}
    <section class="ps-section" data-ps="5" style="background:#0f0a08;">
        <div class="ps-num" aria-hidden="true">05</div>
        <div class="ps-orb" style="width:400px;height:400px;background:radial-gradient(circle,rgba(255,214,153,0.08),transparent 70%);bottom:10%;left:50%;transform:translateX(-50%);"></div>
        <div class="ps-section-inner">
            <h2 class="ps-title">Ce que l'on observe</h2>
            <p class="ps-line">Chaque séance est unique. La praticienne note les <strong>signaux non-verbaux</strong></p>
            <p class="ps-line" style="margin-top:0.5rem;margin-bottom:0.5rem;">Au fil des séances, des <strong>évolutions</strong> apparaissent souvent</p>
            <div class="ps-pills">
                <div class="ps-pill"><span class="ps-pill-dot"></span>Plus de calme</div>
                <div class="ps-pill"><span class="ps-pill-dot"></span>Curiosité sensorielle</div>
                <div class="ps-pill"><span class="ps-pill-dot"></span>Meilleure tolérance</div>
                <div class="ps-pill"><span class="ps-pill-dot"></span>Interactions spontanées</div>
            </div>
        </div>
    </section>

    {{-- Section 6 — Closing --}}
    <section class="ps-section" data-ps="6" style="background:#0a0810;">
        <div class="ps-section-inner">
            <h2 class="ps-title" style="font-size:clamp(2rem,6vw,3.8rem);">Chaque enfant<br>a son propre chemin</h2>
            <p class="ps-subtitle">Le Snoezelen ne demande rien.<br>Il offre un espace où être soi suffit.</p>
            <div class="ps-cta">
                <x-cta-booking :star="true" />
            </div>
        </div>
        <div class="ps-orb" style="width:500px;height:500px;background:radial-gradient(circle,rgba(26,107,122,0.12),transparent 70%);top:50%;left:50%;transform:translate(-50%,-50%);"></div>
    </section>
@endsection
