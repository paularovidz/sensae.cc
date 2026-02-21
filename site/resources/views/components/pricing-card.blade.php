@props(['type' => 'regular', 'price' => 0, 'duration' => 0, 'label' => '', 'sublabel' => null, 'perSession' => null, 'highlight' => false, 'badge' => null])

<div class="border border-border rounded-2xl p-6 flex flex-col {{ $highlight ? 'ring-2 ring-primary' : '' }}">
    @if($badge)
        <span class="text-xs font-medium text-primary bg-primary/10 px-3 py-1 rounded-full self-start">{{ $badge }}</span>
    @endif

    <h3 class="text-xl font-bold text-text-light mt-3 font-secondary">{{ $label }}</h3>

    @if($sublabel)
        <p class="mt-1 text-sm text-text-default/70">{{ $sublabel }}</p>
    @endif

    <div class="mt-4">
        <span class="text-4xl font-bold text-text-light">{{ $price }}€</span>
        @if($perSession)
            <span class="text-text-default text-sm ml-1">soit {{ $perSession }}€ / séance</span>
        @endif
    </div>

    <p class="mt-2 text-sm text-text-default">{{ $duration }} min par séance</p>

    {{ $slot }}

    <a href="https://sensae.cc/booking" target="_blank"       class="mt-auto pt-6 block text-center px-6 py-3 rounded-full {{ $highlight ? 'bg-primary text-text-light' : 'border border-border text-text-light hover:bg-accent' }} transition font-medium">
        Réserver
    </a>
</div>
