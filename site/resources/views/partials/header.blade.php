<header class="border-b border-border relative z-50" x-data="{ mobileOpen: false }">
    <nav class="max-w-6xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between">
        {{-- Logo / Texte --}}
        <a href="{{ route('home') }}" class="flex items-center gap-2 shrink-0">
            @if(!empty($menu['logo_slug']))
                <x-image :slug="$menu['logo_slug']" class="h-8 w-auto" loading="eager" />
            @endif
            @if(!empty($menu['text']))
                <span class="text-2xl font-bold text-text-light font-secondary">{{ $menu['text'] }}</span>
            @endif
        </a>

        {{-- Entrées desktop --}}
        <div class="hidden lg:flex items-center gap-1">
            @foreach(($menu['entries'] ?? []) as $entry)
                @if(($entry['type'] ?? 'link') === 'link')
                    <a href="{{ $entry['url'] ?? '#' }}" class="px-3 py-2 text-text-default hover:text-primary transition rounded-lg hover:bg-white/5">
                        {{ $entry['label'] }}
                    </a>
                @else
                    <div x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" class="relative">
                        <button @click="open = !open" class="px-3 py-2 text-text-default hover:text-primary transition rounded-lg hover:bg-white/5 flex items-center gap-1">
                            {{ $entry['label'] }}
                            <svg class="w-4 h-4 transition-transform" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        <div x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" x-cloak
                            class="absolute top-full left-0 mt-1 bg-accent border border-border rounded-xl shadow-2xl p-4 min-w-[280px]"
                            :class="{ 'flex gap-6': {{ count($entry['submenus'] ?? []) }} > 1 }">

                            @foreach(($entry['submenus'] ?? []) as $submenu)
                                <div class="min-w-[200px]">
                                    @if(!empty($submenu['title']))
                                        <div class="text-xs font-semibold text-text-default/60 uppercase tracking-wider mb-2 px-2">{{ $submenu['title'] }}</div>
                                    @endif

                                    @if(!empty($submenu['image_slug']))
                                        <div class="mb-3 rounded-lg overflow-hidden">
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
                    <button onclick="{{ $menu['cta_action'] }}" class="hidden sm:inline-flex items-center px-4 py-2 bg-primary text-text-light text-sm font-semibold rounded-lg hover:bg-primary/90 transition">
                        {{ $menu['cta_text'] }}
                    </button>
                @elseif(!empty($menu['cta_url']))
                    <a href="{{ $menu['cta_url'] }}" class="hidden sm:inline-flex items-center px-4 py-2 bg-primary text-text-light text-sm font-semibold rounded-lg hover:bg-primary/90 transition">
                        {{ $menu['cta_text'] }}
                    </a>
                @endif
            @endif

            {{-- Burger mobile --}}
            <button @click="mobileOpen = !mobileOpen" class="lg:hidden p-2 text-text-default hover:text-primary transition">
                <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <svg x-show="mobileOpen" x-cloak class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </nav>

    {{-- Menu mobile --}}
    <div x-show="mobileOpen" x-transition x-cloak class="lg:hidden border-t border-border bg-accent">
        <div class="max-w-6xl mx-auto px-4 py-4 space-y-1">
            @foreach(($menu['entries'] ?? []) as $entry)
                @if(($entry['type'] ?? 'link') === 'link')
                    <a href="{{ $entry['url'] ?? '#' }}" class="block px-3 py-2 text-text-default hover:text-primary transition rounded-lg">
                        {{ $entry['label'] }}
                    </a>
                @else
                    <div x-data="{ sub: false }">
                        <button @click="sub = !sub" class="w-full flex items-center justify-between px-3 py-2 text-text-default hover:text-primary transition rounded-lg">
                            {{ $entry['label'] }}
                            <svg class="w-4 h-4 transition-transform" :class="sub && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="sub" x-transition x-cloak class="pl-4 mt-1 space-y-2 border-l border-border ml-3">
                            @foreach(($entry['submenus'] ?? []) as $submenu)
                                @if(!empty($submenu['title']))
                                    <div class="text-xs font-semibold text-text-default/60 uppercase tracking-wider px-3 pt-2">{{ $submenu['title'] }}</div>
                                @endif
                                @foreach(($submenu['links'] ?? []) as $link)
                                    <a href="{{ $link['url'] ?? '#' }}" class="block px-3 py-1.5 text-sm text-text-default hover:text-primary transition rounded-lg">
                                        {{ $link['label'] }}
                                    </a>
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach

            @if(!empty($menu['cta_text']))
                <div class="pt-2">
                    @if(!empty($menu['cta_action']))
                        <button onclick="{{ $menu['cta_action'] }}" class="w-full px-4 py-2 bg-primary text-text-light text-sm font-semibold rounded-lg hover:bg-primary/90 transition text-center">
                            {{ $menu['cta_text'] }}
                        </button>
                    @elseif(!empty($menu['cta_url']))
                        <a href="{{ $menu['cta_url'] }}" class="block w-full px-4 py-2 bg-primary text-text-light text-sm font-semibold rounded-lg hover:bg-primary/90 transition text-center">
                            {{ $menu['cta_text'] }}
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</header>
