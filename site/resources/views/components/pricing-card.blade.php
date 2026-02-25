@props([
    'price' => 0,
    'duration' => 0,
    'label' => '',
    'description' => '',
    'unit' => '/ séance',
    'totalPrice' => null,
    'totalSessions' => null,
    'highlight' => false,
    'badge' => null,
])

<div class="pricing-card group flex min-h-full flex-col justify-between gap-8 rounded-3xl border border-border px-8 py-10 transition duration-500 {{ $highlight ? 'bg-primary/10 border-primary/30 pricing-card--highlight' : 'bg-primary/[2.5%]' }}">
    <div class="space-y-5">
        @if($badge)
            <span class="inline-block text-xs font-medium text-primary bg-primary/10 px-3 py-1 rounded-full">{{ $badge }}</span>
        @else
            <span class="inline-block text-xs py-1">&nbsp;</span>
        @endif

        <h3 class="text-xl md:text-2xl font-medium text-text-light font-secondary">{{ $label }}</h3>

        <div class="space-y-5">
            @if($description)
                <p class="text-text-default text-sm leading-relaxed">{{ $description }}</p>
            @endif

            <div>
                <div class="flex flex-wrap items-end gap-1.5">
                    <span class="text-4xl md:text-5xl font-medium text-text-light font-secondary leading-none">{{ $price }}<span class="text-3xl md:text-4xl">€</span></span>
                    <span class="pb-1.5 text-sm leading-none uppercase text-text-default/70">{{ $unit }}</span>
                </div>

                @if($totalPrice && $totalSessions)
                    <p class="mt-2 text-sm text-text-default/60">soit {{ $totalPrice }}€ les {{ $totalSessions }} séances</p>
                @else
                    <p class="mt-2 text-sm text-text-default/60">&nbsp;</p>
                @endif
            </div>

            <p class="text-xs text-text-default/50">{{ $duration }} min par séance</p>
        </div>
    </div>

    <a href="https://sensae.cc/booking" target="_blank"
       class="group/btn inline-flex items-center justify-between gap-4 w-full px-6 py-3.5 rounded-full font-medium transition {{ $highlight ? 'bg-primary text-text-light hover:opacity-90' : 'border border-border text-text-light hover:border-primary/50 hover:bg-primary/5' }}">
        <span>Réserver</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-white/15">
            <img
                src="{{ asset('storage/star.svg') }}"
                alt=""
                class="h-4 w-4 transition-all duration-300 group-hover/btn:rotate-180"
                loading="lazy"
                decoding="async"
            >
        </span>
    </a>
</div>
