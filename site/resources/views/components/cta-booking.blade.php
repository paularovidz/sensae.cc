{{--
    CTA Booking — composant unique pour tous les boutons de réservation.

    Pour ouvrir les réservations : passer $bookingOpen à true.
--}}
@props(['label' => null, 'star' => false])

@php
    $bookingOpen = false;
    $defaultLabel = $bookingOpen ? 'Réserver une séance' : 'Ouverture en août';
    $label = $label ?? $defaultLabel;
@endphp

@if($bookingOpen)
    <a
        href="https://suivi.sensae.cc/booking"
        target="_blank"
        {{ $attributes->merge(['class' => 'ps-cta-btn cta-button group inline-flex items-center gap-3 px-8 py-3 bg-primary text-text-light rounded-full font-medium hover:opacity-90 transition']) }}
    >
@else
    <button
        data-tf-popup="{{ $contact['typeform_id'] ?? 'EjSgPPjE' }}"
        data-tf-iframe-props="title=Contact sensaë"
        data-tf-medium="snippet"
        data-tf-size="90"
        {{ $attributes->merge(['class' => 'ps-cta-btn cta-button group inline-flex items-center gap-3 px-8 py-3 bg-primary text-text-light rounded-full font-medium hover:opacity-90 transition cursor-pointer']) }}
    >
@endif

    <span class="cta-button-text" data-cta-text>{{ $label }}</span>

    @if($star)
        <span class="ps-cta-star flex h-7 w-7 items-center justify-center rounded-full bg-white/15">
            <svg width="16" height="16" viewBox="0 0 20 19" fill="none" class="h-4 w-4 transition-all duration-300 group-hover:rotate-180"><path d="M10 0.5C9.554 8.769 9.188 9.099 0 9.5C9.188 9.901 9.554 10.231 10 18.5C10.446 10.231 10.813 9.901 20 9.5C10.813 9.099 10.446 8.769 10 0.5Z" fill="currentColor"/></svg>
        </span>
    @endif

@if($bookingOpen)
    </a>
@else
    </button>
@endif
