@props(['sens', 'links' => true, 'repeat' => 6])

@php
    $wheelItems = collect();
    for ($i = 0; $i < $repeat; $i++) {
        $wheelItems = $wheelItems->concat($sens);
    }
@endphp

<div data-wheel-container data-wheel-links="{{ $links ? '1' : '0' }}" data-wheel-max-tablet="28" data-wheel-max-mobile="21" class="relative mx-auto wheel-container overflow-hidden">
    {{-- Glow ring (thin purple border, slightly larger than center) --}}
    <div data-wheel-glow class="absolute left-1/2 wheel-glow"></div>

    {{-- Central half-ellipse with thick border as visual gap --}}
    <div data-wheel-center class="absolute left-1/2 wheel-center"></div>

    {{-- Orbit --}}
    <div data-wheel-orbit class="absolute left-1/2 wheel-orbit">
        @foreach($wheelItems as $index => $item)
            @if($links)
                <a href="{{ route('sens.show', $item->slug) }}"
                   data-wheel-bubble
                   class="absolute wheel-bubble group">
                    <div data-wheel-bubble-inner class="wheel-bubble-inner">
                        <div class="wheel-bubble-img rounded-full overflow-hidden group-hover:border-primary transition-colors duration-300">
                            <x-image :slug="$item->image" :alt="$item->title" class="w-full h-full object-cover" />
                        </div>
                        <span class="wheel-bubble-label text-text-light text-sm font-medium font-secondary text-center mt-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            {{ $item->title }}
                        </span>
                    </div>
                </a>
            @else
                <div data-wheel-bubble
                     class="absolute wheel-bubble">
                    <div data-wheel-bubble-inner class="wheel-bubble-inner">
                        <div class="wheel-bubble-img rounded-full overflow-hidden">
                            <x-image :slug="$item->image" :alt="$item->title" class="w-full h-full object-cover" />
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
</div>

{{-- Fallback statique (reduced-motion) --}}
<div data-wheel-fallback class="hidden max-w-6xl mx-auto px-4 sm:px-6">
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-6">
        @foreach($sens as $item)
            @if($links)
                <a href="{{ route('sens.show', $item->slug) }}" class="group text-center">
                    <div class="w-20 h-20 mx-auto rounded-full overflow-hidden border-2 border-primary/30 group-hover:border-primary transition-colors">
                        <x-image :slug="$item->image" :alt="$item->title" class="w-full h-full object-cover" />
                    </div>
                    <span class="block mt-2 text-sm text-text-light font-secondary group-hover:text-primary transition-colors">{{ $item->title }}</span>
                </a>
            @else
                <div class="text-center">
                    <div class="w-20 h-20 mx-auto rounded-full overflow-hidden border-2 border-primary/30">
                        <x-image :slug="$item->image" :alt="$item->title" class="w-full h-full object-cover" />
                    </div>
                </div>
            @endif
        @endforeach
    </div>
</div>
