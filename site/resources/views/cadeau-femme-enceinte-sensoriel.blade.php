@extends('layouts.immersive')

@section('meta')
    <title>Cadeau femme enceinte sensoriel : une séance Snoezelen — sensaë</title>
    <meta name="description" content="Offrez une expérience sensorielle unique à une future maman. Séance Snoezelen pour femme enceinte : détente profonde, connexion avec bébé, bien-être prénatal.">
    <meta property="og:title" content="Cadeau femme enceinte sensoriel — sensaë">
    <meta property="og:description" content="Un cocon sensoriel pour future maman. Du bien-être à deux, avant la rencontre.">
    <meta property="og:type" content="website">
@endsection

@section('sections')
    {{-- Section 0 — Intro --}}
    <section class="ps-section" data-ps="0" style="background:#050208;">
        <div class="ps-section-inner">
            <h1 class="ps-title">Un cocon sensoriel<br>pour future maman</h1>
            <p class="ps-subtitle">Du bien-être à deux,<br>avant la rencontre.</p>
        </div>
        <button class="ps-start" type="button">
            <span class="ps-start-btn">Commencer</span>
            <div class="ps-start-chevron"></div>
        </button>
        <div class="ps-orb" style="width:300px;height:300px;background:radial-gradient(circle,rgba(210,153,255,0.2),transparent 70%);top:60%;left:50%;transform:translateX(-50%);"></div>
    </section>

    {{-- Section 1 — L'idée --}}
    <section class="ps-section" data-ps="1" style="background:#0d0520;">
        <div class="ps-num">01</div>
        <div class="ps-orb" style="width:400px;height:400px;background:radial-gradient(circle,rgba(210,153,255,0.15),transparent 70%);top:30%;right:-100px;"></div>
        <div class="ps-section-inner">
            <h2 class="ps-title">Un cadeau pas comme les autres</h2>
            <p class="ps-line">La grossesse est un <strong>voyage intérieur</strong>. Ce cadeau l'accompagne</p>
            <p class="ps-line">Un moment de <strong>pause profonde</strong>, loin des préparatifs et du quotidien</p>
            <p class="ps-line">Une expérience sensorielle qui rapproche <strong>maman et bébé</strong></p>
            <p class="ps-small">Recommandé à partir du deuxième trimestre</p>
        </div>
    </section>

    {{-- Section 2 — L'environnement --}}
    <section class="ps-section" data-ps="2" style="background:#1a0a3e;">
        <div class="ps-num">02</div>
        <div class="ps-orb" style="width:350px;height:350px;background:radial-gradient(circle,rgba(210,153,255,0.12),transparent 70%);top:20%;left:-50px;"></div>
        <div class="ps-orb" style="width:250px;height:250px;background:radial-gradient(circle,rgba(114,26,214,0.15),transparent 70%);bottom:15%;right:10%;"></div>
        <div class="ps-section-inner">
            <h2 class="ps-title">Un environnement enveloppant</h2>
            <p class="ps-line">L'espace s'adapte au confort de la future maman</p>
            <div style="display:flex;flex-wrap:wrap;gap:12px;justify-content:center;margin:1.5rem 0;">
                <span class="ps-line" style="margin:0;padding:8px 18px;border:1px solid rgba(210,153,255,0.25);border-radius:999px;font-size:0.8rem;" data-ps-item>Positions confortables</span>
                <span class="ps-line" style="margin:0;padding:8px 18px;border:1px solid rgba(210,153,255,0.2);border-radius:999px;font-size:0.8rem;" data-ps-item>Lumière tamisée</span>
                <span class="ps-line" style="margin:0;padding:8px 18px;border:1px solid rgba(210,153,255,0.25);border-radius:999px;font-size:0.8rem;" data-ps-item>Musique douce</span>
                <span class="ps-line" style="margin:0;padding:8px 18px;border:1px solid rgba(210,153,255,0.2);border-radius:999px;font-size:0.8rem;" data-ps-item>Chaleur enveloppante</span>
                <span class="ps-line" style="margin:0;padding:8px 18px;border:1px solid rgba(210,153,255,0.25);border-radius:999px;font-size:0.8rem;" data-ps-item>Textures réconfortantes</span>
                <span class="ps-line" style="margin:0;padding:8px 18px;border:1px solid rgba(210,153,255,0.2);border-radius:999px;font-size:0.8rem;" data-ps-item>Arômes adaptés</span>
            </div>
            <p class="ps-line" style="margin-top:1rem;">Chaque élément est choisi pour le <strong>bien-être maternel</strong>.<br>Rien qui puisse gêner ou incommoder.</p>
        </div>
    </section>

    {{-- Section 3 — L'expérience --}}
    <section class="ps-section" data-ps="3" style="background:#0a1a2e;">
        <div class="ps-num">03</div>
        <div class="ps-orb" style="width:300px;height:300px;background:radial-gradient(circle,rgba(26,107,122,0.2),transparent 70%);top:40%;left:10%;"></div>
        <div class="ps-section-inner">
            <h2 class="ps-title">Détente et connexion</h2>
            <div class="ps-duo">
                <div class="ps-duo-card" style="border:1px solid rgba(255,250,245,0.1);background:rgba(255,250,245,0.03);">
                    <h3>Le corps</h3>
                    <p>Relâchement des tensions, soulagement du dos et des jambes</p>
                </div>
                <div class="ps-duo-card" style="border:1px solid rgba(210,153,255,0.35);background:rgba(210,153,255,0.08);">
                    <h3>Le lien</h3>
                    <p>Un moment de présence avec bébé, à travers les sensations</p>
                </div>
            </div>
            <p class="ps-subtitle" style="margin-top:1.5rem;">Bébé perçoit la détente de maman.<br>Un moment partagé, déjà.</p>
        </div>
    </section>

    {{-- Section 4 — Moment suspendu --}}
    <section class="ps-section" data-ps="4" style="background:#080810;">
        <div class="ps-num">04</div>
        <div class="ps-pulse"></div>
        <div class="ps-pulse-outer"></div>
        <div class="ps-section-inner" style="position:relative;z-index:10;">
            <h2 class="ps-title">Un moment suspendu</h2>
            <p class="ps-line">La respiration <strong>s'apaise</strong>, le mental se relâche</p>
            <p class="ps-line">Les sensations douces accompagnent la <strong>détente profonde</strong></p>
            <p class="ps-line">Le temps s'arrête. Il n'y a <strong>plus que vous deux</strong></p>
        </div>
    </section>

    {{-- Section 5 — Bienfaits --}}
    <section class="ps-section" data-ps="5" style="background:#0f0a08;">
        <div class="ps-num">05</div>
        <div class="ps-orb" style="width:400px;height:400px;background:radial-gradient(circle,rgba(255,214,153,0.08),transparent 70%);bottom:10%;left:50%;transform:translateX(-50%);"></div>
        <div class="ps-section-inner">
            <h2 class="ps-title">Les bienfaits ressentis</h2>
            <p class="ps-line">Les futures mamans rapportent un <strong>apaisement profond</strong> après la séance</p>
            <p class="ps-line" style="margin-top:0.5rem;margin-bottom:0.5rem;">Un cadeau dont les effets <strong>perdurent</strong> bien au-delà de la séance</p>
            <div class="ps-pills">
                <div class="ps-pill"><span class="ps-pill-dot"></span>Détente musculaire</div>
                <div class="ps-pill"><span class="ps-pill-dot"></span>Meilleur sommeil</div>
                <div class="ps-pill"><span class="ps-pill-dot"></span>Réduction du stress</div>
                <div class="ps-pill"><span class="ps-pill-dot"></span>Lien prénatal</div>
            </div>
        </div>
    </section>

    {{-- Section 6 — Closing --}}
    <section class="ps-section" data-ps="6" style="background:#0a0810;">
        <div class="ps-section-inner">
            <h2 class="ps-title" style="font-size:clamp(2rem,6vw,3.8rem);">Offrir du temps,<br>du calme, de la douceur</h2>
            <p class="ps-subtitle">Le plus beau cadeau pour une future maman,<br>c'est un moment rien que pour elle.</p>
            <div class="ps-cta">
                <a href="https://sensae.cc/booking" target="_blank">
                    Offrir cette expérience
                    <span class="ps-cta-star">
                        <svg width="16" height="16" viewBox="0 0 20 19" fill="none"><path d="M10 0.5C9.554 8.769 9.188 9.099 0 9.5C9.188 9.901 9.554 10.231 10 18.5C10.446 10.231 10.813 9.901 20 9.5C10.813 9.099 10.446 8.769 10 0.5Z" fill="currentColor"/></svg>
                    </span>
                </a>
            </div>
        </div>
        <div class="ps-orb" style="width:500px;height:500px;background:radial-gradient(circle,rgba(210,153,255,0.1),transparent 70%);top:50%;left:50%;transform:translate(-50%,-50%);"></div>
    </section>
@endsection
