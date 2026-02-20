@if($obfuscated)
    <span {{ $attributes->merge(['class' => 'cursor-pointer']) }} @if($blank) data-lkb="{{ base64_encode($href) }}" @else data-lk="{{ base64_encode($href) }}" @endif>{{ $slot }}</span>
@else
    <a href="{{ $href }}" @if($blank) target="_blank" rel="noopener noreferrer" @endif {{ $attributes }}>{{ $slot }}</a>
@endif
