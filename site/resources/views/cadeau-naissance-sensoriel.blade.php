@extends('layouts.immersive')

@push('structured_data')
    @php
        $breadcrumbSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Accueil', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Cadeau naissance'],
            ],
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
@endpush

@section('meta')
    <title>Cadeau naissance sensoriel : offrir une séance Snoezelen — sensaë</title>
    <meta name="description" content="Offrez un cadeau de naissance unique : une séance Snoezelen pour éveiller les sens de bébé en douceur. Un moment de connexion parent-enfant inoubliable.">
    <meta property="og:title" content="Cadeau naissance sensoriel — sensaë">
    <meta property="og:description" content="Un cadeau qui ne s'achète pas en magasin. Un moment de douceur sensorielle pour bébé et ses parents.">
    <meta property="og:type" content="website">
@endsection

@section('sections')
    {{-- Section 0 — Intro --}}
    <section class="ps-section" data-ps="0" style="background:#050208;">
        <div class="ps-section-inner">
            <h1 class="ps-title">Offrir un éveil sensoriel</h1>
            <p class="ps-subtitle">Un cadeau de naissance qui ne se déballe pas.<br>Il se vit.</p>
        </div>
        <button class="ps-start" type="button">
            <span class="ps-start-btn">Commencer</span>
            <div class="ps-start-chevron"></div>
        </button>
        <div class="ps-orb" style="width:300px;height:300px;background:radial-gradient(circle,rgba(210,153,255,0.2),transparent 70%);top:60%;left:50%;transform:translateX(-50%);"></div>
    </section>

    {{-- Section 1 — Pourquoi --}}
    <section class="ps-section" data-ps="1" style="background:#0d0520;">
        <div class="ps-num" aria-hidden="true">01</div>
        <div class="ps-orb" style="width:400px;height:400px;background:radial-gradient(circle,rgba(210,153,255,0.15),transparent 70%);top:30%;right:-100px;"></div>
        <div class="ps-section-inner">
            <h2 class="ps-title">Un cadeau qui a du sens</h2>
            <p class="ps-line">Pas un objet de plus. <strong>Une expérience sensorielle</strong> pour les premiers jours</p>
            <p class="ps-line">Un moment de <strong>connexion</strong> entre le parent et son bébé</p>
            <p class="ps-line">Un espace hors du temps, loin du rythme effréné des <strong>premiers mois</strong></p>
            <p class="ps-small">Accessible dès les premières semaines de vie</p>
        </div>
    </section>

    {{-- Section 2 — L'espace --}}
    <section class="ps-section" data-ps="2" style="background:#1a0a3e;">
        <div class="ps-num" aria-hidden="true">02</div>
        <div class="ps-orb" style="width:350px;height:350px;background:radial-gradient(circle,rgba(210,153,255,0.12),transparent 70%);top:20%;left:-50px;"></div>
        <div class="ps-orb" style="width:250px;height:250px;background:radial-gradient(circle,rgba(114,26,214,0.15),transparent 70%);bottom:15%;right:10%;"></div>
        <div class="ps-section-inner">
            <h2 class="ps-title">Un cocon sensoriel adapté</h2>
            <p class="ps-line">L'espace est pensé pour accueillir les tout-petits en toute sécurité</p>
            <div style="display:flex;flex-wrap:wrap;gap:12px;justify-content:center;margin:1.5rem 0;">
                <span class="ps-line" style="margin:0;padding:8px 18px;border:1px solid rgba(210,153,255,0.25);border-radius:999px;font-size:0.8rem;" data-ps-item>Lumière très douce</span>
                <span class="ps-line" style="margin:0;padding:8px 18px;border:1px solid rgba(210,153,255,0.2);border-radius:999px;font-size:0.8rem;" data-ps-item>Sons apaisants</span>
                <span class="ps-line" style="margin:0;padding:8px 18px;border:1px solid rgba(210,153,255,0.25);border-radius:999px;font-size:0.8rem;" data-ps-item>Textures douces</span>
                <span class="ps-line" style="margin:0;padding:8px 18px;border:1px solid rgba(210,153,255,0.2);border-radius:999px;font-size:0.8rem;" data-ps-item>Température idéale</span>
                <span class="ps-line" style="margin:0;padding:8px 18px;border:1px solid rgba(210,153,255,0.25);border-radius:999px;font-size:0.8rem;" data-ps-item>Couleurs pastel</span>
                <span class="ps-line" style="margin:0;padding:8px 18px;border:1px solid rgba(210,153,255,0.2);border-radius:999px;font-size:0.8rem;" data-ps-item>Odeurs légères</span>
            </div>
            <p class="ps-line" style="margin-top:1rem;">Chaque stimulation est <strong>infime et progressive</strong>.<br>Le bébé guide le rythme.</p>
        </div>
    </section>

    {{-- Section 3 — La séance --}}
    <section class="ps-section" data-ps="3" style="background:#0a1a2e;">
        <div class="ps-num" aria-hidden="true">03</div>
        <div class="ps-orb" style="width:300px;height:300px;background:radial-gradient(circle,rgba(26,107,122,0.2),transparent 70%);top:40%;left:10%;"></div>
        <div class="ps-section-inner">
            <h2 class="ps-title">Parent et bébé, ensemble</h2>
            <div class="ps-duo">
                <div class="ps-duo-card" style="border:1px solid rgba(255,250,245,0.1);background:rgba(255,250,245,0.03);">
                    <h3>Le parent</h3>
                    <p>Accompagne, observe et partage ce moment de découverte</p>
                </div>
                <div class="ps-duo-card" style="border:1px solid rgba(210,153,255,0.35);background:rgba(210,153,255,0.08);">
                    <h3>La praticienne</h3>
                    <p>Guide en douceur, adapte chaque stimulation au bébé</p>
                </div>
            </div>
            <p class="ps-subtitle" style="margin-top:1.5rem;">Le bébé reste dans les bras ou à côté de son parent.<br>Rien n'est imposé.</p>
        </div>
    </section>

    {{-- Section 4 — Bienfaits --}}
    <section class="ps-section" data-ps="4" style="background:#080810;">
        <div class="ps-num" aria-hidden="true">04</div>
        <div class="ps-pulse"></div>
        <div class="ps-pulse-outer"></div>
        <div class="ps-section-inner" style="position:relative;z-index:10;">
            <h2 class="ps-title">Un éveil en douceur</h2>
            <p class="ps-line">Les sens s'éveillent <strong>naturellement</strong>, sans sur-stimulation</p>
            <p class="ps-line">Le lien parent-enfant se renforce dans un cadre <strong>sécurisant</strong></p>
            <p class="ps-line">Un moment de <strong>calme partagé</strong>, précieux dans les premières semaines</p>
        </div>
    </section>

    {{-- Section 5 — Le souvenir --}}
    <section class="ps-section" data-ps="5" style="background:#0f0a08;">
        <div class="ps-num" aria-hidden="true">05</div>
        <div class="ps-orb" style="width:400px;height:400px;background:radial-gradient(circle,rgba(255,214,153,0.08),transparent 70%);bottom:10%;left:50%;transform:translateX(-50%);"></div>
        <div class="ps-section-inner">
            <h2 class="ps-title">Ce qu'on offre vraiment</h2>
            <p class="ps-line">Bien plus qu'un cadeau : un <strong>souvenir sensoriel</strong> pour toute la famille</p>
            <p class="ps-line" style="margin-top:0.5rem;margin-bottom:0.5rem;">Une parenthèse de douceur qui marque les <strong>premiers instants</strong></p>
            <div class="ps-pills">
                <div class="ps-pill"><span class="ps-pill-dot"></span>Connexion</div>
                <div class="ps-pill"><span class="ps-pill-dot"></span>Douceur</div>
                <div class="ps-pill"><span class="ps-pill-dot"></span>Éveil</div>
                <div class="ps-pill"><span class="ps-pill-dot"></span>Sérénité</div>
            </div>
        </div>
    </section>

    {{-- Section 6 — Closing --}}
    <section class="ps-section" data-ps="6" style="background:#0a0810;">
        <div class="ps-section-inner">
            <h2 class="ps-title" style="font-size:clamp(2rem,6vw,3.8rem);">Le plus beau cadeau<br>ne se met pas dans une boîte</h2>
            <p class="ps-subtitle">Il se vit, se ressent, et se partage.</p>
            <div class="ps-cta ps-cta-duo">
                <x-cta-booking :star="true" />
                <button data-tf-popup="{{ $contact['typeform_gift_id'] ?? $contact['typeform_id'] ?? 'EjSgPPjE' }}" data-tf-iframe-props="title=Offrir une séance sensaë" data-tf-medium="snippet" data-tf-size="90" class="ps-cta-btn ps-cta-btn--outline">
                    Offrir une séance
                    <span class="ps-cta-star">
                        <svg width="16" height="16" viewBox="0 0 20 19" fill="none"><path d="M10 0.5C9.554 8.769 9.188 9.099 0 9.5C9.188 9.901 9.554 10.231 10 18.5C10.446 10.231 10.813 9.901 20 9.5C10.813 9.099 10.446 8.769 10 0.5Z" fill="currentColor"/></svg>
                    </span>
                </button>
            </div>
        </div>
        <div class="ps-orb" style="width:500px;height:500px;background:radial-gradient(circle,rgba(210,153,255,0.1),transparent 70%);top:50%;left:50%;transform:translate(-50%,-50%);"></div>
    </section>
@endsection
