@extends('layouts.app')

@section('title', $page->meta_title ?? $page->title . ' - sensaë')

@section('content')
    {{-- Hero banner --}}
    <section class="hero relative">
        <div class="container">
            <div class="relative z-10 mx-auto max-w-4xl space-y-10">
                <h1 data-animate="fade-up" class="has-italic-text font-secondary mb-6 text-center text-5xl text-white md:text-6xl/tight">
                    {!! $page->title !!}
                </h1>
            </div>
        </div>
    </section>

    {{-- Content --}}
    <section>
        <div class="container">
            <div data-animate="fade-up" class="mx-auto max-w-4xl prose-styles">
                {!! $page->content !!}
            </div>
        </div>
    </section>
@endsection
