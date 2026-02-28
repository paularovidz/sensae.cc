@extends('layouts.app')

@section('title', 'Apprendre les sens - sensaë')
@section('meta_description', 'Découvrez les sens stimulés en Snoezelen : goût, odorat, ouïe, toucher, vestibulaire et vue. Comprenez comment l\'approche multi-sensorielle agit.')

@section('content')
<main class="section-spacing-lg fit-to-screen">

    {{-- Hero: roue des sens + marquee --}}
    <section data-wheel>
        <div class="overflow-hidden md:space-y-16">
            <div class="container">
                <div class="mx-auto max-w-2xl text-center" data-animate="fade-up">
                    <x-pill text="Sens" />
                    <h2 class="has-italic-text">Un lâcher prise pour <strong>tous les sens</strong></h2>
                </div>
            </div>

            <div data-animate="fade-up" data-animate-delay="0.2">
                <x-sens-wheel :sens="$allSens" :links="false" />
            </div>
        </div>
    </section>

    {{-- Sens cards --}}
    <section id="sens">
        <div class="container space-y-8">
            <div data-animate="fade-up">
                <h2 class="h4">Comprendre mes sens</h2>
                <p class="opacity-80 xl:max-w-3xl">L'approche snoezelen est <strong>multi-sensorielle</strong>. Il faut comprendre comment les sens fonctionnent, et à quel point ils sont la racine de nos actes et pensées.</p>
            </div>

            <div data-animate="fade-up" data-animate-delay="0.2" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach($allSens as $s)
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

    <x-cta-section />
</main>
@endsection
