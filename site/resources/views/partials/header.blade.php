@if (!request()->routeIs('home') && View::yieldContent('hideGlow') !== 'true')
<div class="absolute top-0 left-1/2 -z-30 h-1/2 w-full -translate-x-1/2 overflow-hidden" aria-hidden="true">
    <div
        class="bg-[radial-gradient(53.29%_50%_at_50%_50%,theme(colors.primary)_0%,rgba(39,_27,_69,_0)_100%)] absolute top-[-550px] left-[-10%] h-[800px] w-[800px]">
    </div>
    <div
        class="bg-[radial-gradient(53.29%_50%_at_50%_50%,theme(colors.primary)_0%,rgba(39,_27,_69,_0)_100%)] absolute top-[-550px] right-[-10%] h-[800px] w-[800px]">
    </div>
</div>
@endif
<header data-header class="header group/header mt-5 transform sticky-header">
    <div class="relative container">
        <nav
            class="hs-dropdown relative flex items-center justify-between px-2 py-2.5 [--auto-close:inside] [--gpu-acceleration:false] [--strategy:absolute] lg:[--strategy:static]">
            {{-- Logo / Texte --}}
            <a href="{{ route('home') }}" class="logo flex items-center gap-2 shrink-0">
                @if (!empty($menu['logo_slug']))
                    <x-image :slug="$menu['logo_slug']" class="h-8 w-auto" loading="eager" />
                @endif
                @if (!empty($menu['text']))
                    <span class="text-2xl font-bold text-text-light font-secondary">{{ $menu['text'] }}</span>
                @endif
            </a>

            {{-- Entrées desktop --}}
            <div
                class="navbar hidden lg:flex lg:items-center">
                @foreach ($menu['entries'] ?? [] as $entry)
                    @if (($entry['type'] ?? 'link') === 'link')
                        <a href="{{ $entry['url'] ?? '#' }}"
                            class="nav-link text-white transition rounded-full hover:bg-white/5">
                            {{ $entry['label'] }}
                        </a>
                    @else
                        <div data-dropdown class="relative flex items-center">
                            @if (!empty($entry['url']))
                                <a href="{{ $entry['url'] }}"
                                    class="nav-link text-white transition rounded-full hover:bg-white/5 flex items-center gap-1">
                                    {{ $entry['label'] }}
                                    <svg data-dropdown-chevron class="w-4 h-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </a>
                            @else
                                <button
                                    class="nav-link text-white transition rounded-full hover:bg-white/5 flex items-center gap-1">
                                    {{ $entry['label'] }}
                                    <svg data-dropdown-chevron class="w-4 h-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            @endif

                            <div data-dropdown-panel
                                class="absolute top-full left-1/2 -translate-x-1/2 mt-1 bg-body/90 backdrop-blur-3xl border border-white/10 rounded-2xl shadow-2xl p-4 min-w-[280px] opacity-0 invisible {{ count($entry['submenus'] ?? []) > 1 ? 'flex gap-6' : '' }}"
                                style="transform-origin: top center;">
                                @foreach ($entry['submenus'] ?? [] as $submenu)
                                    <div class="min-w-[200px]">
                                        @if (!empty($submenu['title']))
                                            <div
                                                class="text-xs font-semibold text-white uppercase tracking-wider mb-2 px-2">
                                                {{ $submenu['title'] }}</div>
                                        @endif

                                        @if (!empty($submenu['image_slug']))
                                            <div class="mb-3 rounded-2xl overflow-hidden">
                                                <x-image :slug="$submenu['image_slug']" class="w-full h-28 object-cover" />
                                            </div>
                                        @endif

                                        <ul class="list-unstyled space-y-0.5">
                                            @foreach ($submenu['links'] ?? [] as $link)
                                                <li>
                                                    <a href="{{ $link['url'] ?? '#' }}"
                                                        class="block px-2 py-1.5 text-sm text-text-default hover:text-primary hover:bg-white/5 rounded-md transition">
                                                        {{ $link['label'] }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>

                                        @if (!empty($submenu['cta_text']) && !empty($submenu['cta_url']))
                                            <a href="{{ $submenu['cta_url'] }}"
                                                class="mt-3 block text-center px-4 py-2 text-sm font-medium text-white bg-primary/20 border border-primary/30 rounded-full hover:bg-primary/30 transition">
                                                {{ $submenu['cta_text'] }}
                                            </a>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            <div class="flex items-center gap-3">
                {{-- CTA --}}
                @if (!empty($menu['cta_text']))
                    @if (!empty($menu['cta_action']))
                        <button onclick="{{ $menu['cta_action'] }}"
                            class="hidden sm:inline-flex items-center px-5 py-3 bg-primary text-text-light text-sm rounded-full hover:bg-primary/90 transition">
                            {{ $menu['cta_text'] }}
                        </button>
                    @elseif(!empty($menu['cta_url']))
                        <a href="{{ $menu['cta_url'] }}"
                            class="hidden sm:inline-flex items-center px-5 py-3 bg-primary text-text-light text-sm rounded-full hover:bg-primary/90 transition">
                            {{ $menu['cta_text'] }}
                        </a>
                    @endif
                @endif

                {{-- Burger mobile --}}
                <button data-mobile-toggle class="lg:hidden p-2 text-text-default hover:text-primary transition">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </nav>
    </div>

    </div>
</header>

{{-- Menu mobile fullscreen --}}
<div data-mobile-menu class="fixed inset-0 z-[60] hidden lg:!hidden bg-body/95 backdrop-blur-xl overflow-y-auto">
    {{-- Close button --}}
    <button data-mobile-close class="absolute top-7 right-6 p-2 text-text-default hover:text-primary transition z-10">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>

    <div class="flex flex-col justify-center min-h-full px-8 py-20 space-y-2">
        @foreach ($menu['entries'] ?? [] as $menuIndex => $entry)
            @if (($entry['type'] ?? 'link') === 'link')
                <a href="{{ $entry['url'] ?? '#' }}" data-mobile-item
                    class="block px-4 py-3 text-2xl font-medium text-text-light hover:text-primary transition">
                    {{ $entry['label'] }}
                </a>
            @else
                <details class="group/sub" data-mobile-item>
                    <summary
                        class="w-full flex items-center justify-between px-4 py-3 text-2xl font-medium text-text-light hover:text-primary transition cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                        {{ $entry['label'] }}
                        <svg class="w-5 h-5 transition-transform group-open/sub:rotate-180" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <div class="pl-6 mt-1 space-y-1 border-l border-white/10 ml-4">
                        @foreach ($entry['submenus'] ?? [] as $submenu)
                            @if (!empty($submenu['title']))
                                <div class="text-xs font-semibold text-white/50 uppercase tracking-wider px-4 pt-3 pb-1">
                                    {{ $submenu['title'] }}</div>
                            @endif
                            @foreach ($submenu['links'] ?? [] as $link)
                                <a href="{{ $link['url'] ?? '#' }}"
                                    class="block px-4 py-2 text-base text-text-default hover:text-primary transition">
                                    {{ $link['label'] }}
                                </a>
                            @endforeach
                            @if (!empty($submenu['cta_text']) && !empty($submenu['cta_url']))
                                <a href="{{ $submenu['cta_url'] }}"
                                    class="block px-4 py-2 mt-1 text-sm font-medium text-primary hover:text-primary/80 transition">
                                    {{ $submenu['cta_text'] }} &rarr;
                                </a>
                            @endif
                        @endforeach
                    </div>
                </details>
            @endif
        @endforeach

        @if (!empty($menu['cta_text']))
            <div class="pt-4 px-4" data-mobile-item>
                @if (!empty($menu['cta_action']))
                    <button onclick="{{ $menu['cta_action'] }}"
                        class="w-full px-6 py-3 bg-primary text-text-light text-base font-semibold rounded-full hover:bg-primary/90 transition text-center">
                        {{ $menu['cta_text'] }}
                    </button>
                @elseif(!empty($menu['cta_url']))
                    <a href="{{ $menu['cta_url'] }}"
                        class="block w-full px-6 py-3 bg-primary text-text-light text-base font-semibold rounded-full hover:bg-primary/90 transition text-center">
                        {{ $menu['cta_text'] }}
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>
