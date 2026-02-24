<header data-header class="header group/header mt-5 transform sticky-header">
    <div class="relative container">
        <nav class="hs-dropdown relative flex items-center justify-between px-2 py-2.5 [--auto-close:inside] [--gpu-acceleration:false] [--strategy:absolute] lg:[--strategy:static]">
            {{-- Logo / Texte --}}
            <a href="{{ route('home') }}" class="logo flex items-center gap-2 shrink-0">
                @if(!empty($menu['logo_slug']))
                    <x-image :slug="$menu['logo_slug']" class="h-8 w-auto" loading="eager" />
                @endif
                @if(!empty($menu['text']))
                    <span class="text-2xl font-bold text-text-light font-secondary">{{ $menu['text'] }}</span>
                @endif
            </a>

            {{-- Entrées desktop --}}
            <div class="navbar hs-dropdown-menu hs-dropdown-open:flex hs-dropdown-open:!mt-2 has-customized-scrollbar max-lg:absolute max-lg:!transform-none lg:!transform-none">
                @foreach(($menu['entries'] ?? []) as $entry)
                    @if(($entry['type'] ?? 'link') === 'link')
                        <a href="{{ $entry['url'] ?? '#' }}" class="nav-link text-white transition rounded-full hover:bg-white/5">
                            {{ $entry['label'] }}
                        </a>
                    @else
                        <div class="relative group/dropdown">
                            <button class="nav-link text-white transition rounded-full hover:bg-white/5 flex items-center gap-1">
                                {{ $entry['label'] }}
                                <svg class="w-4 h-4 transition-transform group-hover/dropdown:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>

                            <div class="absolute top-full left-0 mt-1 bg-accent border border-border rounded-2xl shadow-2xl p-4 min-w-[280px] opacity-0 invisible group-hover/dropdown:opacity-100 group-hover/dropdown:visible transition-all duration-150 -translate-y-1 group-hover/dropdown:translate-y-0 {{ count($entry['submenus'] ?? []) > 1 ? 'flex gap-6' : '' }}">
                                @foreach(($entry['submenus'] ?? []) as $submenu)
                                    <div class="min-w-[200px]">
                                        @if(!empty($submenu['title']))
                                            <div class="text-xs font-semibold text-text-default/60 uppercase tracking-wider mb-2 px-2">{{ $submenu['title'] }}</div>
                                        @endif

                                        @if(!empty($submenu['image_slug']))
                                            <div class="mb-3 rounded-2xl overflow-hidden">
                                                <x-image :slug="$submenu['image_slug']" class="w-full h-28 object-cover" />
                                            </div>
                                        @endif

                                        <ul class="space-y-0.5">
                                            @foreach(($submenu['links'] ?? []) as $link)
                                                <li>
                                                    <a href="{{ $link['url'] ?? '#' }}" class="block px-2 py-1.5 text-sm text-text-default hover:text-primary hover:bg-white/5 rounded-md transition">
                                                        {{ $link['label'] }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            <div class="flex items-center gap-3">
                {{-- CTA --}}
                @if(!empty($menu['cta_text']))
                    @if(!empty($menu['cta_action']))
                        <button onclick="{{ $menu['cta_action'] }}" class="hidden sm:inline-flex items-center px-5 py-3 bg-primary text-text-light text-sm rounded-full hover:bg-primary/90 transition">
                            {{ $menu['cta_text'] }}
                        </button>
                    @elseif(!empty($menu['cta_url']))
                        <a href="{{ $menu['cta_url'] }}" class="hidden sm:inline-flex items-center px-5 py-3 bg-primary text-text-light text-sm rounded-full hover:bg-primary/90 transition">
                            {{ $menu['cta_text'] }}
                        </a>
                    @endif
                @endif

                {{-- Burger mobile --}}
                <button data-mobile-toggle class="lg:hidden p-2 text-text-default hover:text-primary transition">
                    <svg data-icon-open class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg data-icon-close class="w-6 h-6 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </nav>
    </div>

    {{-- Menu mobile --}}
    <div data-mobile-menu class="mobile-menu lg:hidden">
        <div>
            <div class="border-t border-border bg-accent px-4 py-4 space-y-1">
                @foreach(($menu['entries'] ?? []) as $menuIndex => $entry)
                    @if(($entry['type'] ?? 'link') === 'link')
                        <a href="{{ $entry['url'] ?? '#' }}" class="block px-3 py-2 text-text-default hover:text-primary transition rounded-full">
                            {{ $entry['label'] }}
                        </a>
                    @else
                        <details class="group/sub">
                            <summary class="w-full flex items-center justify-between px-3 py-2 text-text-default hover:text-primary transition rounded-full cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                                {{ $entry['label'] }}
                                <svg class="w-4 h-4 transition-transform group-open/sub:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </summary>
                            <div class="pl-4 mt-1 space-y-2 border-l border-border ml-3">
                                @foreach(($entry['submenus'] ?? []) as $submenu)
                                    @if(!empty($submenu['title']))
                                        <div class="text-xs font-semibold text-text-default/60 uppercase tracking-wider px-3 pt-2">{{ $submenu['title'] }}</div>
                                    @endif
                                    @foreach(($submenu['links'] ?? []) as $link)
                                        <a href="{{ $link['url'] ?? '#' }}" class="block px-3 py-1.5 text-sm text-text-default hover:text-primary transition rounded-full">
                                            {{ $link['label'] }}
                                        </a>
                                    @endforeach
                                @endforeach
                            </div>
                        </details>
                    @endif
                @endforeach

                @if(!empty($menu['cta_text']))
                    <div class="pt-2">
                        @if(!empty($menu['cta_action']))
                            <button onclick="{{ $menu['cta_action'] }}" class="w-full px-4 py-2 bg-primary text-text-light text-sm font-semibold rounded-full hover:bg-primary/90 transition text-center">
                                {{ $menu['cta_text'] }}
                            </button>
                        @elseif(!empty($menu['cta_url']))
                            <a href="{{ $menu['cta_url'] }}" class="block w-full px-4 py-2 bg-primary text-text-light text-sm font-semibold rounded-full hover:bg-primary/90 transition text-center">
                                {{ $menu['cta_text'] }}
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</header>
