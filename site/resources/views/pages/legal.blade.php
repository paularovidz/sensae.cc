@extends('layouts.app')

@section('title', $page->meta_title ?? $page->title . ' - sensëa')

@section('content')
    <section class="py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            <h1 class="text-3xl font-bold text-text-light font-secondary">{{ $page->title }}</h1>

            <div class="mt-8 prose prose-invert max-w-none text-text-default text-sm">
                {!! $page->content !!}
            </div>
        </div>
    </section>
@endsection
