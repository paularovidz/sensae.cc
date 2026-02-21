<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        {{-- Bottom spacer so content isn't hidden behind sticky bar --}}
        <div style="height:60px;"></div>

        {{-- Sticky save bar --}}
        <div style="position:sticky;bottom:0;z-index:20;display:flex;justify-content:flex-end;padding:12px 0;background:linear-gradient(to bottom, transparent, var(--fi-body-bg, rgb(17 24 39)) 30%);">
            <x-filament::button type="submit">
                Sauvegarder
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
