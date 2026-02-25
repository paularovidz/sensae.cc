@props(['slug', 'alt' => ''])

<div data-hero-image class="relative mx-4 mt-12 -mb-4 max-w-screen-xl md:m-0 md:mx-auto md:mt-20">
    <div class="relative">
        <div class="overflow-hidden rounded-t-2xl bg-black border-2 border-b-0 border-[#0e0c0c] md:rounded-t-3xl">
            <x-image :slug="$slug" :alt="$alt" class="w-full h-full object-cover" loading="eager" fetchpriority="high" />
        </div>
        <div data-hero-image-glow class="hero-image-glow"></div>
    </div>
</div>
