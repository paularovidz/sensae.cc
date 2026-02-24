@extends('layouts.experience')

@section('content')
    {{-- Custom cursor --}}
    <div class="xp-cursor" data-xp-cursor></div>

    {{-- Skip button --}}
    <button class="xp-skip" data-xp-skip>Passer</button>

    {{-- Act 1 — Darkness --}}
    <section class="xp-canvas is-active" data-xp-act="1">
        <div class="xp-halo" data-xp-halo></div>
        <div data-xp-particles></div>
        <p class="xp-text" data-xp-text="1">Touchez l'obscurit&eacute;.</p>
        <p class="xp-sound-hint" data-xp-sound-hint>Activez le son pour une meilleure exp&eacute;rience</p>
        <button class="xp-start" data-xp-start>Cliquez pour d&eacute;marrer l'exp&eacute;rience</button>
    </section>

    {{-- Act 2 — Fluid Orbs --}}
    <section class="xp-canvas" data-xp-act="2">
        <div data-xp-fluid-orbs></div>
        <p class="xp-text" data-xp-text="2">Laissez-vous traverser.</p>
        <p class="xp-text xp-text--small xp-text--bottom xp-hint" data-xp-hint="2">Bougez &middot; Scrollez &middot; Cliquez</p>
    </section>

    {{-- Act 3 — Sound --}}
    <section class="xp-canvas" data-xp-act="3">
        <div data-xp-circles></div>
        <div class="xp-pads" data-xp-pads>
            <button class="xp-pad" data-note="0">Do</button>
            <button class="xp-pad" data-note="1">R&eacute;</button>
            <button class="xp-pad" data-note="2">Mi</button>
            <button class="xp-pad" data-note="3">Sol</button>
            <button class="xp-pad" data-note="4">La</button>
            <button class="xp-pad" data-note="5">Do</button>
            <button class="xp-pad" data-note="6">R&eacute;</button>
            <button class="xp-pad" data-note="7">Mi</button>
        </div>
        <p class="xp-text" data-xp-text="3">Composez votre silence.</p>
        <p class="xp-text xp-text--small xp-text--bottom xp-hint" data-xp-hint="3">Jouez avec votre clavier</p>
    </section>

    {{-- Act 4 — Light --}}
    <section class="xp-canvas" data-xp-act="4">
        <div data-xp-orbs></div>
        <div data-xp-bubbles></div>
        <p class="xp-text" data-xp-text="4">Rien &agrave; faire. Juste ressentir.</p>
    </section>

    {{-- Act 5 — Breathing --}}
    <section class="xp-canvas" data-xp-act="5">
        <div class="xp-breath-container">
            <div class="xp-breath-outer" data-xp-breath-outer></div>
            <div class="xp-breath-inner" data-xp-breath-inner></div>
            <span class="xp-breath-timer" data-xp-breath-timer></span>
        </div>
        <p class="xp-breath-label" data-xp-breath-label></p>
        <p class="xp-text" data-xp-text="5" style="top: 30%;">Respirez.</p>
    </section>

    {{-- Act 6 — Ripples + CTA --}}
    <section class="xp-canvas" data-xp-act="6">
        <div data-xp-ripples></div>
        <p class="xp-text" data-xp-text="6">Chaque s&eacute;ance est une invitation &agrave; ressentir.</p>
        <div class="xp-cta" data-xp-cta>
            <a href="https://sensae.cc/booking" target="_blank" rel="noopener">R&eacute;server une s&eacute;ance <span class="xp-cta-star-wrap"><img src="/storage/star.svg" alt="" class="xp-cta-star" width="16" height="16"></span></a>
        </div>
    </section>
@endsection
