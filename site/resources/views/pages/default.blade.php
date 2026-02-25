@extends('layouts.app')

@section('title', $page->meta_title ?? $page->title . ' - sensaë')
@section('meta_description', $page->meta_description ?? '')

@section('content')
    <section class="py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            <h1 data-animate="fade-up" class="text-3xl md:text-4xl font-bold text-text-light font-secondary">{{ $page->h1 ?? $page->title }}</h1>

            <div data-animate="fade-up" data-animate-delay="0.1" class="mt-8 prose prose-invert max-w-none text-text-default">
                {!! $page->content !!}
            </div>
        </div>
    </section>
@endsection
