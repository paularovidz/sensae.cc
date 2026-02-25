@props(['faqs'])

@if ($faqs->count())
    <div data-animate="fade-up" data-animate-delay="0.2" class="mx-auto max-w-4xl">
        <div class="bg-primary/5 rounded-lg border border-border p-6 lg:rounded-3xl lg:p-12">
            <div class="space-y-5">
                @foreach ($faqs as $index => $faq)
                    <x-faq-item :faq="$faq" :open="$index === 0" />
                @endforeach
            </div>
        </div>
    </div>
@endif
