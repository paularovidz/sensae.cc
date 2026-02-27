@props([
    'title' => 'Reconnectez-vous à <strong>vos sens</strong>',
    'pills' => ['Réduction du stress physiologique', 'Amélioration du sommeil', 'Réduction des troubles du comportement','Réduction des stéréotypies'],
])

<section class="mt-auto">
    <div class="bg-accent py-14 md:py-28">
        <div class="container grid grid-cols-1 place-content-between gap-y-12 lg:grid-cols-2 lg:gap-x-10">
            {{-- Left: title + pills --}}
            <div data-animate="fade-up" class="space-y-7 lg:max-w-2xl">
                <h2>{!! $title !!}</h2>

                <div class="flex flex-wrap gap-3">
                    @foreach($pills as $pill)
                        <div class="from-primary/20 to-primary/5 inline-flex items-center gap-3 rounded-full bg-gradient-to-r to-90% py-1.5 pe-5 pl-2.5 text-sm text-white">
                            <svg class="text-primary shrink-0" width="20" height="20" viewBox="0 0 21 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M10.5 20C16.023 20 20.5 15.523 20.5 10C20.5 4.477 16.023 0 10.5 0C4.977 0 0.5 4.477 0.5 10C0.5 15.523 4.977 20 10.5 20ZM10.242 14.044L15.798 7.378L14.091 5.956L9.313 11.688L6.841 9.214L5.27 10.786L8.603 14.119L9.463 14.979L10.242 14.044Z" fill="currentColor"/>
                            </svg>
                            {{ $pill }}
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Right: CTA + rating --}}
            @php
                $ratingScore = $rating['rating_score'] ?? '';
                $ratingLabel = $rating['rating_label'] ?? '';
                $hasRating = !empty($ratingScore) && !empty($ratingLabel);
            @endphp
            <div class="flex flex-wrap-reverse items-center gap-x-16 gap-y-6 md:flex-nowrap lg:justify-end">
                {{-- Button --}}
                <div data-animate="fade-up" data-animate-delay="0.1" class="relative {{ $hasRating ? 'after:hidden after:sm:block after:absolute after:top-0 after:left-[120%] after:h-full after:w-px after:rotate-[18deg] after:bg-primary/35' : '' }}">
                    <x-cta-booking :star="true" />
                </div>

                {{-- Rating (only if configured in BO) --}}
                @if($hasRating)
                    <div data-animate="fade-up" data-animate-delay="0.2" class="flex items-center gap-4 text-sm leading-relaxed">
                        <svg class="text-primary shrink-0" width="32" height="31" viewBox="0 0 35 34" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M7.368 33.625L10.144 21.331L0.833 13.063L13.133 11.969L17.917 0.375L22.7 11.969L35 13.063L25.69 21.331L28.466 33.625L17.917 27.106L7.368 33.625Z" fill="url(#cta-star-grad)"/>
                            <defs>
                                <linearGradient id="cta-star-grad" x1="4.19" y1="-4.53" x2="14.79" y2="41.96" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="currentColor" stop-opacity="0"/>
                                    <stop offset="1" stop-color="currentColor"/>
                                </linearGradient>
                            </defs>
                        </svg>
                        <div class="text-text-default">
                            <span class="text-text-light font-medium">{{ $ratingScore }}</span><br>
                            {{ $ratingLabel }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
