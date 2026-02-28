@php echo '<?xml version="1.0" encoding="UTF-8"?>'; @endphp
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
{{-- Static routes --}}
@foreach($staticUrls as $url)
    <url>
        <loc>{{ url($url['loc']) }}</loc>
        <changefreq>{{ $url['changefreq'] }}</changefreq>
        <priority>{{ $url['priority'] }}</priority>
    </url>
@endforeach

{{-- Sens --}}
@foreach($sens as $item)
    <url>
        <loc>{{ url('/comprendre-sens/' . $item->slug) }}</loc>
        <lastmod>{{ $item->updated_at->toW3cString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
@endforeach

{{-- Articles --}}
@foreach($articles as $item)
    <url>
        <loc>{{ url('/conseils/' . $item->slug) }}</loc>
        <lastmod>{{ $item->updated_at->toW3cString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.6</priority>
    </url>
@endforeach

{{-- Pages --}}
@foreach($pages as $item)
    <url>
        @if($item->template === 'location')
        <loc>{{ url('/salle-snoezelen-' . $item->slug) }}</loc>
        <priority>0.6</priority>
        @else
        <loc>{{ url('/' . $item->slug) }}</loc>
        <priority>0.4</priority>
        @endif
        <lastmod>{{ $item->updated_at->toW3cString() }}</lastmod>
        <changefreq>monthly</changefreq>
    </url>
@endforeach
</urlset>
