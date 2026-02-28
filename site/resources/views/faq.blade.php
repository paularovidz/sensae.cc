@extends('layouts.app')

@section('title', 'FAQ - sensaë')

@section('breadcrumb')
    <li><a href="/" class="hover:text-primary transition">Accueil</a></li>
    <li class="before:content-['/'] before:mx-1.5">FAQ</li>
@endsection

@section('content')
<x-breadcrumb :items="[
    ['name' => 'Accueil', 'url' => url('/')],
    ['name' => 'FAQ'],
]" />
    <section class="py-16">
        <div class="container mx-auto px-4 sm:px-6">
            <div class="text-center mb-10">
                <x-pill text="FAQ" />
                <h1 data-animate="fade-up" class="text-3xl md:text-4xl font-bold text-text-light font-secondary">Foire aux questions</h1>
                <p data-animate="fade-up" data-animate-delay="0.1" class="mt-4 text-text-default">Retrouvez les réponses aux questions les plus fréquentes sur le Snoezelen.</p>
            </div>

            @if($faqsByCategory->count())
                <div
                    data-faq-tabs
                    data-animate="fade-up" data-animate-delay="0.2"
                    class="bg-primary/5 rounded-lg border border-border p-6 lg:rounded-3xl lg:p-12 max-lg:space-y-6 lg:grid lg:grid-cols-8 lg:gap-16"
                >
                    {{-- Navigation catégories --}}
                    <nav class="lg:col-span-3 xl:col-span-2 max-lg:flex max-lg:flex-wrap max-lg:gap-2">
                        @foreach($faqsByCategory->keys() as $i => $category)
                            <button
                                type="button"
                                data-faq-tab="{{ $i }}"
                                class="cursor-pointer faq-tab px-6 py-2.5 text-start text-lg max-lg:min-w-full rounded-lg transition hover:text-white {{ $i === 0 ? 'text-white max-lg:bg-white/5' : 'text-text-default' }}"
                            >
                                {{ $category }}
                            </button>
                        @endforeach
                    </nav>

                    {{-- Contenu FAQ par catégorie --}}
                    @foreach($faqsByCategory->values() as $i => $faqs)
                        <div
                            data-faq-panel="{{ $i }}"
                            class="lg:col-span-5 xl:col-span-6 space-y-5 {{ $i !== 0 ? 'hidden' : '' }}"
                        >
                            @foreach($faqs as $index => $faq)
                                <x-faq-item :faq="$faq" :open="$index === 0" />
                            @endforeach
                        </div>
                    @endforeach
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        const container = document.querySelector('[data-faq-tabs]');
                        if (!container) return;

                        const tabs = container.querySelectorAll('[data-faq-tab]');
                        const panels = container.querySelectorAll('[data-faq-panel]');

                        tabs.forEach(tab => {
                            tab.addEventListener('click', () => {
                                const index = tab.dataset.faqTab;

                                tabs.forEach(t => {
                                    t.classList.remove('text-white', 'max-lg:bg-white/5');
                                    t.classList.add('text-text-default');
                                });
                                tab.classList.remove('text-text-default');
                                tab.classList.add('text-white', 'max-lg:bg-white/5');

                                panels.forEach(p => p.classList.add('hidden'));
                                const target = container.querySelector(`[data-faq-panel="${index}"]`);
                                if (target) target.classList.remove('hidden');
                            });
                        });
                    });
                </script>
            @else
                <p class="text-text-default text-center py-12">Aucune FAQ pour le moment.</p>
            @endif
        </div>
    </section>
@endsection
