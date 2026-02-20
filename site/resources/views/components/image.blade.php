@props(['slug', 'alt' => null])

@if($media)
    <img
        src="{{ $media->getPublicUrl() }}"
        alt="{{ $alt ?? $media->alt ?? '' }}"
        @if($media->width) width="{{ $media->width }}" @endif
        @if($media->height) height="{{ $media->height }}" @endif
        {{ $attributes->merge(['loading' => 'lazy']) }}
    >
@endif
