<div x-data="availabilityBadge" class="inline-flex">
    <template x-if="loading">
        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-accent/50 text-sm">
            <span class="w-2 h-2 rounded-full bg-text-default animate-pulse"></span>
            <span class="text-text-default">Chargement...</span>
        </span>
    </template>

    <template x-if="!loading && available">
        <a href="https://sensae.cc/booking" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary text-text-light text-sm font-medium hover:opacity-90 transition">
            <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
            <span x-text="label"></span>
        </a>
    </template>
</div>
