<footer data-footer class="to-primary/20 lg:to-primary/5 bg-gradient-to-b from-transparent">
    <div class="lg:bg-[radial-gradient(55%_55%_at_50%_130%,theme(colors.primary)_40%,transparent_120%)] xl:bg-[radial-gradient(55%_55%_at_50%_100%,theme(colors.primary)_0%,transparent_100%)]">
        <div class="container space-y-16 lg:space-y-28">
            <div class="grid pt-16 lg:grid-cols-2 xl:grid-cols-3">

                {{-- Logo, Description and Social --}}
                <div class="flex flex-col gap-y-5 lg:pe-10">
                    <a href="/" class="logo-wrapper">
                        <span class="logo-text font-tertiary">{{ $menu['text'] ?? 'sensaë' }}</span>
                    </a>

                    @if(!empty($footer['description']))
                        <p class="opacity-90">{{ $footer['description'] }}</p>
                    @endif

                    @isset($social)
                        @if(!empty($social['social_facebook']) || !empty($social['social_instagram']))
                            <ul class="flex flex-wrap items-center gap-4 mt-5">
                                @if(!empty($social['social_facebook']))
                                    <li>
                                        <a aria-label="facebook" href="{{ $social['social_facebook'] }}" target="_blank" rel="noopener noreferrer nofollow" class="group relative flex items-center justify-center transition duration-300 bg-primary/15 hover:bg-primary rounded-full h-12 w-12">
                                            <span class="sr-only">facebook</span>
                                            <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <g clip-path="url(#clip0_fb)">
                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M0.5 12.567C0.5 18.533 4.833 23.494 10.5 24.5V15.833H7.5V12.5H10.5V9.833C10.5 6.833 12.433 5.167 15.167 5.167C16.033 5.167 16.967 5.3 17.833 5.433V8.5H16.3C14.833 8.5 14.5 9.233 14.5 10.167V12.5H17.7L17.167 15.833H14.5V24.5C20.167 23.494 24.5 18.534 24.5 12.567C24.5 5.93 19.1 0.5 12.5 0.5C5.9 0.5 0.5 5.93 0.5 12.567Z" fill="#F9FBFF"/>
                                                </g>
                                                <defs>
                                                    <clipPath id="clip0_fb">
                                                        <rect width="25" height="25" fill="white"/>
                                                    </clipPath>
                                                </defs>
                                            </svg>
                                        </a>
                                    </li>
                                @endif
                                @if(!empty($social['social_instagram']))
                                    <li>
                                        <a aria-label="instagram" href="{{ $social['social_instagram'] }}" target="_blank" rel="noopener noreferrer nofollow" class="group relative flex items-center justify-center transition duration-300 bg-primary/15 hover:bg-primary rounded-full h-12 w-12">
                                            <span class="sr-only">instagram</span>
                                            <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M13.5682 2.08008C14.74 2.0832 15.3348 2.08945 15.8484 2.10404L16.0505 2.11133C16.2838 2.11966 16.514 2.13008 16.7921 2.14258C17.9005 2.19466 18.6567 2.36966 19.3203 2.62695C20.0078 2.89154 20.5869 3.24987 21.1661 3.82799C21.696 4.34855 22.1059 4.97849 22.3671 5.67383C22.6244 6.33737 22.7994 7.09362 22.8515 8.20299C22.864 8.48008 22.8744 8.71029 22.8828 8.94466L22.889 9.14674C22.9046 9.65924 22.9109 10.254 22.913 11.4259L22.914 12.203V13.5676C22.9165 14.3274 22.9086 15.0872 22.89 15.8467L22.8838 16.0488C22.8755 16.2832 22.865 16.5134 22.8525 16.7905C22.8005 17.8999 22.6234 18.6551 22.3671 19.3197C22.1059 20.015 21.696 20.6449 21.1661 21.1655C20.6455 21.6954 20.0156 22.1053 19.3203 22.3665C18.6567 22.6238 17.9005 22.7988 16.7921 22.8509L16.0505 22.8822L15.8484 22.8884C15.3348 22.903 14.74 22.9103 13.5682 22.9124L12.7911 22.9134H11.4275C10.6674 22.9161 9.90726 22.9081 9.14734 22.8895L8.94525 22.8832C8.69797 22.8738 8.45075 22.8631 8.20359 22.8509C7.09525 22.7988 6.339 22.6238 5.67442 22.3665C4.97945 22.1051 4.34988 21.6953 3.82963 21.1655C3.29936 20.645 2.88912 20.0151 2.62755 19.3197C2.37025 18.6561 2.19525 17.8999 2.14317 16.7905L2.11192 16.0488L2.10671 15.8467C2.08751 15.0872 2.07883 14.3274 2.08067 13.5676V11.4259C2.07779 10.6661 2.08543 9.90632 2.10359 9.14674L2.11088 8.94466C2.11921 8.71029 2.12963 8.48008 2.14213 8.20299C2.19421 7.09362 2.36921 6.33841 2.6265 5.67383C2.88869 4.9782 3.29965 4.34824 3.83067 3.82799C4.35062 3.29836 4.97982 2.88849 5.67442 2.62695C6.339 2.36966 7.09421 2.19466 8.20359 2.14258C8.48067 2.13008 8.71192 2.11966 8.94525 2.11133L9.14734 2.10508C9.90691 2.08657 10.6667 2.07858 11.4265 2.08112L13.5682 2.08008ZM12.4973 7.28841C11.116 7.28841 9.79124 7.83714 8.81449 8.8139C7.83774 9.79065 7.289 11.1154 7.289 12.4967C7.289 13.8781 7.83774 15.2028 8.81449 16.1796C9.79124 17.1563 11.116 17.7051 12.4973 17.7051C13.8787 17.7051 15.2034 17.1563 16.1802 16.1796C17.1569 15.2028 17.7057 13.8781 17.7057 12.4967C17.7057 11.1154 17.1569 9.79065 16.1802 8.8139C15.2034 7.83714 13.8787 7.28841 12.4973 7.28841ZM12.4973 9.37174C12.9077 9.37168 13.3141 9.45244 13.6933 9.60942C14.0724 9.7664 14.417 9.99653 14.7072 10.2867C14.9974 10.5768 15.2277 10.9213 15.3848 11.3004C15.5419 11.6795 15.6228 12.0858 15.6229 12.4962C15.6229 12.9066 15.5422 13.313 15.3852 13.6921C15.2282 14.0713 14.9981 14.4159 14.7079 14.7061C14.4178 14.9963 14.0733 15.2266 13.6942 15.3837C13.3151 15.5408 12.9088 15.6217 12.4984 15.6217C11.6696 15.6217 10.8747 15.2925 10.2887 14.7065C9.70262 14.1204 9.37338 13.3255 9.37338 12.4967C9.37338 11.6679 9.70262 10.8731 10.2887 10.287C10.8747 9.70098 11.6696 9.37174 12.4984 9.37174M17.9671 5.72591C17.6218 5.72591 17.2906 5.86309 17.0464 6.10728C16.8022 6.35147 16.665 6.68266 16.665 7.02799C16.665 7.37333 16.8022 7.70452 17.0464 7.94871C17.2906 8.19289 17.6218 8.33008 17.9671 8.33008C18.3125 8.33008 18.6437 8.19289 18.8878 7.94871C19.132 7.70452 19.2692 7.37333 19.2692 7.02799C19.2692 6.68266 19.132 6.35147 18.8878 6.10728C18.6437 5.86309 18.3125 5.72591 17.9671 5.72591Z" fill="#fff"/>
                                            </svg>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        @endif
                    @endisset
                </div>

                {{-- Columns --}}
                @if(!empty($footer['columns']))
                    <div class="col-span-2 mt-10 grid gap-y-10 md:grid-cols-{{ count($footer['columns']) }} xl:mt-0 xl:ml-5">
                        @foreach($footer['columns'] as $i => $column)
                            <div class="{{ $i > 0 ? 'md:border-l md:px-8 lg:px-8 xl:px-14' : 'md:border-l md:pl-8 lg:pl-8 xl:pl-14' }}">
                                @if(!empty($column['title']))
                                    <h3 class="text-h5 text-light mb-6">{{ $column['title'] }}</h3>
                                @endif
                                @if(!empty($column['links']))
                                    <ul class="flex flex-col gap-y-1.5">
                                        @foreach($column['links'] as $link)
                                            <li class="inline-block">
                                                <a class="inline-block py-px text-inherit opacity-90 duration-300 hover:text-white" href="{{ $link['url'] }}">
                                                    {{ $link['label'] }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

            </div>

            {{-- Bottom bar --}}
            <div class="py-7 text-sm md:text-base">
                <div class="flex flex-wrap items-center justify-between gap-x-8 gap-y-6">
                    <ul class="flex flex-wrap items-center gap-x-4 gap-y-2 md:flex-nowrap">
                        @if(!empty($footer['bottom_links']))
                            @foreach($footer['bottom_links'] as $link)
                                <li class="inline-block">
                                    <a class="inline-block py-px text-inherit opacity-90 duration-300 hover:text-white" href="{{ $link['url'] }}">{{ $link['label'] }}</a>
                                </li>
                            @endforeach
                        @endif
                        @if(!empty($footer['credit_name']))
                            <li>Réalisé par
                                @if(!empty($footer['credit_url']))
                                    <button data-lkb="{{ base64_encode($footer['credit_url']) }}" class="text-inherit opacity-90 duration-300 hover:text-white">{{ $footer['credit_name'] }}</button>
                                @else
                                    {{ $footer['credit_name'] }}
                                @endif
                            </li>
                        @endif
                    </ul>
                    <ul class="flex flex-wrap gap-y-4 text-white">
                        <li class="inline-block opacity-90">
                            <span>Copyright {{ date('Y') }}</span>
                        </li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</footer>
