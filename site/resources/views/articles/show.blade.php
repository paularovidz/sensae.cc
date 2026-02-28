@extends('layouts.app')

@section('title', $article->title . ' - sensaë')
@section('meta_description', $article->excerpt)

@section('breadcrumb')
    <li><a href="/" class="hover:text-primary transition">Accueil</a></li>
    <li class="before:content-['/'] before:mx-1.5"><a href="{{ route('articles.index') }}" class="hover:text-primary transition">Conseils</a></li>
    <li class="before:content-['/'] before:mx-1.5">{{ $article->title }}</li>
@endsection

@section('content')
<x-breadcrumb :items="[
    ['name' => 'Accueil', 'url' => url('/')],
    ['name' => 'Conseils', 'url' => url('/conseils')],
    ['name' => $article->title],
]" />
    <article class="py-16">
        {{-- Header centré --}}
        <div class="article relative mx-auto w-full space-y-8 lg:max-w-4xl px-4 sm:px-6">
            <div class="space-y-8 text-center" data-animate="fade-up">
                <div>
                    @if($article->categories)
                        <ul class="flex justify-center gap-2 mb-4 list-unstyled">
                            @foreach($article->categories as $cat)
                                <li class="text-secondary inline-block text-sm font-medium tracking-wider">{{ $cat }}</li>
                            @endforeach
                        </ul>
                    @endif
                    <h1 class="h2">{{ $article->title }}</h1>
                </div>

                @if($article->excerpt)
                    <p class="text-text-default">{!! $article->excerpt !!}</p>
                @endif
            </div>

            {{-- Featured image --}}
            @if($article->image)
                <div data-animate="fade-up" data-animate-delay="0.1">
                    <x-image :slug="$article->image" :alt="$article->title" class="h-96 w-full rounded-3xl object-cover" />
                </div>
            @endif
        </div>

        {{-- Layout 2 colonnes --}}
        <div class="max-w-7xl mx-auto px-4 sm:px-6 mt-12 lg:flex lg:gap-12">

            {{-- Sidebar (desktop only) --}}
            <aside class="hidden lg:block lg:w-64 shrink-0">
                <div class="sticky top-[100px] space-y-8">
                    {{-- TOC --}}
                    @if(count($toc) > 1)
                        <nav data-animate="fade-up">
                            <p class="text-xs font-medium tracking-widest uppercase text-text-default mb-3">Sommaire</p>
                            <ol class="space-y-2 list-decimal list-inside">
                                @foreach($toc as $item)
                                    <li><a href="#{{ $item['id'] }}" class="text-text-default hover:text-primary transition text-sm">{{ $item['text'] }}</a></li>
                                @endforeach
                            </ol>
                        </nav>
                    @endif

                    {{-- Auteur --}}
                    @if($article->author)
                        <div class="flex items-center gap-3" data-animate="fade-up" data-animate-delay="0.2">
                            <x-image slug="celine-delcloy" alt="{{ $article->author }}" class="h-12 w-12 rounded-full border-2 border-border object-cover" />
                            <div>
                                <p class="font-medium text-text-light text-sm">{{ $article->author }}</p>
                                <p class="text-xs text-text-default">Éducatrice spécialisée, formée au Snoezelen</p>
                            </div>
                        </div>
                    @endif
                </div>
            </aside>

            {{-- Contenu principal --}}
            <div class="flex-1 max-w-3xl space-y-8">

                {{-- Mobile : auteur --}}
                @if($article->author)
                    <div class="flex flex-wrap justify-center gap-4 lg:hidden" data-animate="fade-up">
                        <x-image slug="celine-delcloy" alt="{{ $article->author }}" class="h-16 w-16 rounded-full border-4 border-border object-cover" />
                        <div class="text-start">
                            <p class="font-medium text-text-light">{{ $article->author }}</p>
                            <p class="text-sm text-text-default">Éducatrice spécialisée, formée au Snoezelen</p>
                        </div>
                    </div>
                @endif

                {{-- Mobile : TOC accordion --}}
                @if(count($toc) > 1)
                    <div x-data="{ open: true }" class="lg:hidden" data-animate="fade-up" data-animate-delay="0.2">
                        <button @click="open = !open" class="mb-2 flex w-full items-center justify-between border-b border-border pb-2 text-text-light">
                            <span class="text-sm font-normal tracking-widest uppercase">Table des matières</span>
                            <span class="flex h-9 w-9 items-center justify-center rounded-full transition duration-300" :class="open ? 'rotate-180' : ''">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 512 512"><path d="M256 294.1L383 167c9.4-9.4 24.6-9.4 33.9 0s9.3 24.6 0 34L273 345c-9.1 9.1-23.7 9.3-33.1.7L95 201.1c-4.7-4.7-7-10.9-7-17s2.3-12.3 7-17c9.4-9.4 24.6-9.4 33.9 0l127.1 127z"></path></svg>
                            </span>
                        </button>
                        <nav x-show="open" x-collapse class="prose-styles">
                            <ol class="max-h-60 overflow-auto">
                                @foreach($toc as $item)
                                    <li class="py-1"><a href="#{{ $item['id'] }}" class="text-text-default hover:text-primary transition text-sm">{{ $item['text'] }}</a></li>
                                @endforeach
                            </ol>
                        </nav>
                    </div>
                @endif

                {{-- Article --}}
                <div class="prose-styles" data-animate="fade-up" data-animate-delay="0.2">
                    {!! $content !!}
                </div>
            </div>
        </div>
    </article>

    <x-cta-section />
@endsection
