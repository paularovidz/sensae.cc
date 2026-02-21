@extends('layouts.app')

@section('title', $page->meta_title ?? $page->title . ' - sensëa')
@section('meta_description', $page->meta_description ?? '')

@section('content')
    <section class="py-20">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            @if($page->big_title)
                <p data-animate="fade-in" class="text-primary font-medium mb-2">{{ $page->big_title }}</p>
            @endif

            <h1 data-animate="fade-up" class="text-3xl md:text-5xl font-bold text-text-light font-secondary leading-tight">
                {{ $page->h1 ?? $page->title }}
            </h1>

            @if($page->image)
                <div data-animate="fade-up" data-animate-delay="0.1" class="mt-10 rounded-2xl overflow-hidden">
                    <x-image :slug="$page->image" :alt="$page->title" class="w-full" />
                </div>
            @endif

            <div data-animate="fade-up" data-animate-delay="0.2" class="mt-10 prose prose-invert max-w-none text-text-default">
                {!! $page->content !!}
            </div>

            <div data-animate="fade-up" data-animate-delay="0.3" class="mt-12 text-center">
                <a href="https://sensae.cc/booking" target="_blank" data-magnetic="0.3" class="inline-block px-8 py-3 bg-primary text-text-light rounded-xl font-medium hover:opacity-90 transition">
                    Réserver une séance
                </a>
            </div>
        </div>
    </section>
@endsection
