@extends('layouts.app')

@section('title', $article->title . ' - sensëa')
@section('meta_description', $article->excerpt)

@section('content')
    <article class="py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            @if($article->categories)
                <div data-animate="fade-in" class="flex flex-wrap gap-2 mb-4">
                    @foreach($article->categories as $cat)
                        <span class="text-sm font-medium text-primary bg-primary/10 px-3 py-1 rounded-full">{{ $cat }}</span>
                    @endforeach
                </div>
            @endif

            <h1 data-animate="fade-up" class="text-3xl md:text-4xl font-bold text-text-light font-secondary">{{ $article->title }}</h1>

            <div data-animate="fade-up" data-animate-delay="0.1" class="mt-4 flex items-center gap-4 text-sm text-text-default">
                @if($article->author)
                    <span>Par {{ $article->author }}</span>
                @endif
                @if($article->published_at)
                    <span>{{ $article->published_at->translatedFormat('d F Y') }}</span>
                @endif
            </div>

            @if($article->image)
                <div data-animate="fade-up" data-animate-delay="0.2" class="mt-8 rounded-2xl overflow-hidden">
                    <x-image :slug="$article->image" :alt="$article->title" class="w-full" />
                </div>
            @endif

            <div data-animate="fade-up" data-animate-delay="0.3" class="mt-8 prose prose-invert max-w-none text-text-default">
                {!! $article->content !!}
            </div>
        </div>
    </article>
@endsection
