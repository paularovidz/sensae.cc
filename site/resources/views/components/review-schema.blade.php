@props(['reviews'])

@if ($reviews->count())
    @push('structured_data')
        @php
            $avgRating = round($reviews->avg('rating'), 1);
            $schema = [
                '@context' => 'https://schema.org',
                '@type' => 'LocalBusiness',
                'name' => $seo['seo_title'] ?? 'sensaë',
                'aggregateRating' => [
                    '@type' => 'AggregateRating',
                    'ratingValue' => $avgRating,
                    'bestRating' => 5,
                    'worstRating' => 1,
                    'ratingCount' => $reviews->count(),
                ],
                'review' => $reviews->map(fn ($review) => [
                    '@type' => 'Review',
                    'author' => [
                        '@type' => 'Person',
                        'name' => $review->author_name,
                    ],
                    'reviewRating' => [
                        '@type' => 'Rating',
                        'ratingValue' => $review->rating,
                        'bestRating' => 5,
                        'worstRating' => 1,
                    ],
                    'reviewBody' => $review->content,
                ])->values()->all(),
            ];
        @endphp
        <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
    @endpush
@endif
