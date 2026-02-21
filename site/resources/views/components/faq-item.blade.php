@props(['faq'])

<details data-faq-item class="group border border-border rounded-xl">
    <summary class="flex items-center justify-between p-4 cursor-pointer list-none">
        <span class="font-medium text-text-light">{{ $faq->question }}</span>
        <svg data-faq-chevron class="w-5 h-5 text-text-default" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </summary>
    <div data-faq-content class="px-4 pb-4 text-sm text-text-default leading-relaxed">
        {!! $faq->answer !!}
    </div>
</details>
