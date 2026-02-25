@extends('layouts.app')

@section('title', $sens->title . ' - sensaë')
@section('meta_description', $sens->excerpt)

@section('content')
    <article class="py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            @if($sens->category)
                <span data-animate="fade-in" class="text-sm font-medium text-primary bg-primary/10 px-3 py-1 rounded-full">{{ $sens->category }}</span>
            @endif

            <h1 data-animate="fade-up" class="mt-4 text-3xl md:text-4xl font-bold text-text-light font-secondary">{{ $sens->title }}</h1>

            @if($sens->image)
                <div data-animate="fade-up" data-animate-delay="0.1" class="mt-8 rounded-2xl overflow-hidden">
                    <x-image :slug="$sens->image" :alt="$sens->title" class="w-full" />
                </div>
            @endif

            <div data-animate="fade-up" data-animate-delay="0.2" class="mt-8 prose prose-invert max-w-none text-text-default">
                {!! $sens->content !!}
            </div>
        </div>
    </article>

    {{-- Other sens --}}
    @if($allSens->count() > 1)
        <section data-animate-border class="py-16 border-t border-border">
            <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <h2 data-animate="fade-up" class="text-2xl font-bold text-text-light font-secondary mb-6">Les autres sens</h2>
                <div data-animate-grid class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($allSens->where('id', '!=', $sens->id)->take(3) as $item)
                        <x-sens-card :sens="$item" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
