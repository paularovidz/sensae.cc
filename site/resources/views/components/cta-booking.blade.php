{{--
    CTA Booking — composant unique pour tous les boutons de réservation.

    Pour ouvrir les réservations : passer $bookingOpen à true
    et décommenter le lien <a> ci-dessous (+ commenter le <button>).
--}}
@props(['label' => null, 'star' => false])

@php
    $bookingOpen = false;
    $defaultLabel = $bookingOpen ? 'Réserver une séance' : 'Ouverture en août 2026';
    $label = $label ?? $defaultLabel;
@endphp

@if($bookingOpen)
    <a
        href="https://suivi.sensae.cc/booking"
        target="_blank"
        {{ $attributes->merge(['class' => 'cta-button group inline-flex items-center gap-3 px-8 py-3 bg-primary text-text-light rounded-full font-medium hover:opacity-90 transition']) }}
    >
@else
    <button
        data-tf-popup="{{ $contact['typeform_id'] ?? 'EjSgPPjE' }}"
        data-tf-iframe-props="title=Contact sensaë"
        data-tf-medium="snippet"
        data-tf-size="90"
        {{ $attributes->merge(['class' => 'cta-button group inline-flex items-center gap-3 px-8 py-3 bg-primary text-text-light rounded-full font-medium hover:opacity-90 transition cursor-pointer']) }}
    >
@endif

    <span class="cta-button-text" data-cta-text>{{ $label }}</span>

    @if($star)
        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-white/15">
            <img
                src="{{ asset('storage/star.svg') }}"
                alt=""
                class="h-4 w-4 transition-all duration-300 group-hover:rotate-180"
                loading="lazy"
                fetchpriority="low"
                decoding="async"
            >
        </span>
    @endif

@if($bookingOpen)
    </a>
@else
    </button>
@endif
