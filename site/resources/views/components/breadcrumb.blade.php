@props(['items' => []])

@if (count($items) > 1)
    @push('structured_data')
        @php
            $schema = [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => collect($items)->map(fn ($item, $index) => [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $item['name'],
                    ...(!empty($item['url']) ? ['item' => $item['url']] : []),
                ])->all(),
            ];
        @endphp
        <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
    @endpush
@endif
