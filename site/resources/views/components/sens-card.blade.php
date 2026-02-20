@props(['sens'])

<a href="{{ route('sens.show', $sens->slug) }}" class="group block border border-border rounded-2xl overflow-hidden hover:border-primary/50 transition">
    @if($sens->image)
        <div class="aspect-video overflow-hidden">
            <img src="{{ asset('storage/' . $sens->image) }}" alt="{{ $sens->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
        </div>
    @endif

    <div class="p-5">
        @if($sens->category)
            <span class="text-xs font-medium text-primary bg-primary/10 px-2 py-1 rounded-full">{{ $sens->category }}</span>
        @endif
        <h3 class="mt-2 text-lg font-bold text-text-light group-hover:text-primary transition font-secondary">{{ $sens->title }}</h3>
        @if($sens->excerpt)
            <p class="mt-2 text-sm text-text-default line-clamp-2">{{ $sens->excerpt }}</p>
        @endif
    </div>
</a>
