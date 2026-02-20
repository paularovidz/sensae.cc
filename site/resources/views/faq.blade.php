@extends('layouts.app')

@section('title', 'FAQ - sensëa')

@section('content')
    <section class="py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            <h1 class="text-3xl md:text-4xl font-bold text-text-light font-secondary">Foire aux questions</h1>
            <p class="mt-4 text-text-default">Retrouvez les réponses aux questions les plus fréquentes sur le Snoezelen.</p>

            <div class="mt-10 space-y-8">
                @forelse($faqsByCategory as $category => $faqs)
                    <div>
                        @if($category)
                            <h2 class="text-xl font-bold text-text-light font-secondary mb-4">{{ $category }}</h2>
                        @endif
                        <div class="space-y-3">
                            @foreach($faqs as $faq)
                                <x-faq-item :faq="$faq" />
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p class="text-text-default text-center py-12">Aucune FAQ pour le moment.</p>
                @endforelse
            </div>
        </div>
    </section>
@endsection
