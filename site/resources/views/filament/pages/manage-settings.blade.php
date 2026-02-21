<x-filament-panels::page>
    <form wire:submit="save">
        <div class="space-y-6">
            {{-- Colors --}}
            <x-filament::section heading="Couleurs">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach(['color_primary' => 'Primaire', 'color_secondary' => 'Secondaire', 'color_accent' => 'Accent', 'color_body' => 'Fond', 'color_border' => 'Bordures', 'color_text_default' => 'Texte', 'color_text_light' => 'Texte clair'] as $key => $label)
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</label>
                            <div class="flex items-center gap-2 mt-1">
                                <input type="color" wire:model.live="data.{{ $key }}" class="h-10 w-14 rounded cursor-pointer">
                                <input type="text" wire:model.live="data.{{ $key }}" class="fi-input block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-filament::section>

            {{-- Contact --}}
            <x-filament::section heading="Contact">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                        <input type="email" wire:model="data.contact_email" class="fi-input mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Téléphone</label>
                        <input type="text" wire:model="data.contact_phone" class="fi-input mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Adresse</label>
                        <input type="text" wire:model="data.contact_address" class="fi-input mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                    </div>
                </div>
            </x-filament::section>

            {{-- Social --}}
            <x-filament::section heading="Réseaux sociaux">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach(['social_facebook' => 'Facebook', 'social_instagram' => 'Instagram', 'social_linkedin' => 'LinkedIn'] as $key => $label)
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</label>
                            <input type="url" wire:model="data.{{ $key }}" class="fi-input mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm" placeholder="https://">
                        </div>
                    @endforeach
                </div>
            </x-filament::section>

            {{-- SEO --}}
            <x-filament::section heading="SEO">
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Titre du site</label>
                        <input type="text" wire:model="data.seo_title" class="fi-input mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Description du site</label>
                        <textarea wire:model="data.seo_description" rows="3" class="fi-input mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm"></textarea>
                    </div>
                </div>
            </x-filament::section>

            {{-- Bottom spacer so content isn't hidden behind sticky bar --}}
            <div style="height:60px;"></div>
        </div>

        {{-- Sticky save bar --}}
        <div style="position:sticky;bottom:0;z-index:20;display:flex;justify-content:flex-end;padding:12px 0;background:linear-gradient(to bottom, transparent, var(--fi-body-bg, rgb(17 24 39)) 30%);">
            <x-filament::button type="submit">
                Sauvegarder
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
