@extends('layouts.app')

@section('title', $sens->title . ' - sensaë')
@section('meta_description', $sens->excerpt)

@section('breadcrumb')
    <li><a href="/" class="hover:text-primary transition">Accueil</a></li>
    <li class="before:content-['/'] before:mx-1.5"><a href="{{ route('sens.index') }}" class="hover:text-primary transition">Comprendre les sens</a></li>
    <li class="before:content-['/'] before:mx-1.5">{{ $sens->title }}</li>
@endsection

@section('content')
<x-breadcrumb :items="[
    ['name' => 'Accueil', 'url' => url('/')],
    ['name' => 'Comprendre les sens', 'url' => url('/comprendre-sens')],
    ['name' => $sens->title],
]" />

    <article class="py-16">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            {{-- Carte conteneur --}}
            <div data-animate="fade-up" class="rounded-2xl border border-primary/15 bg-primary/10 p-4 md:p-10">
                <div class="grid grid-cols-1 gap-y-12 lg:grid-cols-7 lg:items-start lg:gap-x-10">

                    {{-- Sidebar --}}
                    <aside class="lg:col-span-2 lg:sticky lg:top-32 lg:max-w-52">
                        {{-- Lien retour --}}
                        <a href="{{ route('sens.index') }}" class="group mb-6 inline-flex items-center gap-1.5 text-sm text-text-default transition hover:text-primary">
                            <svg class="h-4 w-4 transition group-hover:-translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                            </svg>
                            Comprendre les sens
                        </a>

                        {{-- Image --}}
                        @if($sens->image)
                            <div class="mb-6 flex justify-center rounded-xl bg-accent p-4">
                                <x-image :slug="$sens->image" :alt="$sens->title" class="w-full rounded-lg" />
                            </div>
                        @endif

                        {{-- CTA --}}
                        <div class="mb-6">
                            <x-cta-contact class="text-sm" />
                        </div>

                        {{-- Metadata --}}
                        @if($sens->category)
                            <div class="divide-y divide-primary/25">
                                <div class="py-3">
                                    <p class="mb-1 text-xs uppercase tracking-wider text-text-default">Catégorie</p>
                                    <span class="text-sm font-medium text-primary">{{ $sens->category }}</span>
                                </div>
                            </div>
                        @endif
                    </aside>

                    {{-- Contenu principal --}}
                    <div class="lg:col-span-5">
                        <h1 class="text-3xl font-bold text-text-light font-secondary md:text-4xl">{{ $sens->title }}</h1>

                        @if($sens->excerpt)
                            <p class="mt-4 text-text-default">{{ $sens->excerpt }}</p>
                        @endif

                        <div class="prose-styles mt-8">
                            {!! $sens->content !!}
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </article>

    {{-- Les autres sens --}}
    @if($allSens->count() > 1)
        <section data-animate-border class="border-t border-border py-16">
            <div class="mx-auto max-w-6xl px-4 sm:px-6">
                <h2 data-animate="fade-up" class="mb-6 text-2xl font-bold text-text-light font-secondary">Les autres sens</h2>
                <div data-animate="fade-up" data-animate-delay="0.2" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($allSens->where('id', '!=', $sens->id)->take(3) as $s)
                        <div class="border-primary/30 hover:bg-accent relative space-y-8 rounded-lg border p-5 transition-colors duration-300">
                            <div class="flex items-center gap-4">
                                @if($s->image)
                                    <x-image :slug="$s->image" :alt="$s->title" class="bg-primary/10 h-12 w-12 rounded-sm" width="48" height="48" />
                                @endif
                                <h3 class="h5">{{ $s->title }}</h3>
                                <a href="{{ route('sens.show', $s->slug) }}" class="absolute inset-0 size-full">
                                    <span class="sr-only">En savoir plus sur {{ strtolower($s->title) }}</span>
                                </a>
                            </div>
                            @if($s->excerpt)
                                <p>{{ $s->excerpt }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
