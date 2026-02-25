@props(['label' => 'Ouverture en août', 'star' => false])

<button
    data-tf-popup="EjSgPPjE"
    data-tf-iframe-props="title=Contact sensaë"
    data-tf-medium="snippet"
    data-tf-size="90"
    {{ $attributes->merge(['class' => 'cta-button group inline-flex items-center gap-3 px-8 py-3 bg-primary text-text-light rounded-full font-medium hover:opacity-90 transition cursor-pointer']) }}
>
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
</button>
