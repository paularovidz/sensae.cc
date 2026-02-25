@extends('layouts.app')

@section('title', 'Conseils Snoezelen - sensaë')

@section('content')
    <section class="py-16">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <h1 data-animate="fade-up" class="text-3xl md:text-4xl font-bold text-text-light font-secondary">Conseils & Articles</h1>
            <p data-animate="fade-up" data-animate-delay="0.1" class="mt-4 text-text-default">Découvrez nos articles sur la stimulation multisensorielle et le Snoezelen.</p>

            <div data-animate-grid class="mt-10 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($articles as $article)
                    <a href="{{ route('articles.show', $article->slug) }}" data-tilt class="group block border border-border rounded-2xl overflow-hidden hover:border-primary/50 transition">
                        @if($article->image)
                            <div class="aspect-video overflow-hidden">
                                <x-image :slug="$article->image" :alt="$article->title" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" />
                            </div>
                        @endif
                        <div class="p-5">
                            @if($article->categories)
                                <div class="flex flex-wrap gap-2 mb-2">
                                    @foreach($article->categories as $cat)
                                        <span class="text-xs font-medium text-primary bg-primary/10 px-2 py-1 rounded-full">{{ $cat }}</span>
                                    @endforeach
                                </div>
                            @endif
                            <h2 class="text-lg font-bold text-text-light group-hover:text-primary transition font-secondary">{{ $article->title }}</h2>
                            @if($article->excerpt)
                                <p class="mt-2 text-sm text-text-default line-clamp-2">{{ $article->excerpt }}</p>
                            @endif
                            @if($article->published_at)
                                <p class="mt-3 text-xs text-text-default">{{ $article->published_at->translatedFormat('d F Y') }}</p>
                            @endif
                        </div>
                    </a>
                @empty
                    <p class="text-text-default col-span-full text-center py-12">Aucun article pour le moment.</p>
                @endforelse
            </div>

            <div class="mt-10">
                {{ $articles->links() }}
            </div>
        </div>
    </section>
@endsection
