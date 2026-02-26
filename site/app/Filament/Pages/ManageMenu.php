<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class ManageMenu extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-bars-3';
    protected static ?string $navigationLabel = 'Menu';
    protected static ?string $title = 'Méga-menu';
    protected static ?int $navigationSort = 9;

    protected string $view = 'filament.pages.manage-menu';

    public ?array $data = [];

    public function mount(): void
    {
        $menu = Setting::get('menu', $this->getDefaultMenu());

        $this->form->fill($menu);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Identité')
                    ->description('Logo et nom affichés à gauche du menu')
                    ->components([
                        Grid::make(2)->components([
                            TextInput::make('logo_slug')
                                ->label('Slug du logo (média)')
                                ->helperText('Laisser vide pour afficher uniquement le texte'),
                            TextInput::make('text')
                                ->label('Texte')
                                ->required()
                                ->helperText('Affiché à côté ou à la place du logo'),
                        ]),
                    ]),

                Section::make('Entrées du menu')
                    ->description('De 0 à 6 entrées. Chaque entrée peut être un lien simple ou un menu déroulant.')
                    ->components([
                        Repeater::make('entries')
                            ->label('')
                            ->maxItems(6)
                            ->reorderable(true)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['label'] ?? 'Nouvelle entrée')
                            ->addActionLabel('Ajouter une entrée')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('label')
                                        ->label('Libellé')
                                        ->required()
                                        ->live(onBlur: true),
                                    Select::make('type')
                                        ->label('Type')
                                        ->options([
                                            'link' => 'Lien simple',
                                            'dropdown' => 'Menu déroulant',
                                            'typeform' => 'Typeform (contact)',
                                        ])
                                        ->default('link')
                                        ->required()
                                        ->live(),
                                ]),
                                TextInput::make('url')
                                    ->label('URL')
                                    ->visible(fn ($get) => in_array($get('type'), ['link', 'dropdown', null]))
                                    ->helperText('Relatif (/page) ou absolu (https://...). Pour un dropdown, lien au clic sur le libellé.'),
                                Repeater::make('submenus')
                                    ->label('Sous-menus')
                                    ->visible(fn ($get) => $get('type') === 'dropdown')
                                    ->maxItems(4)
                                    ->reorderable(true)
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'Sous-menu')
                                    ->addActionLabel('Ajouter un sous-menu')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            TextInput::make('title')
                                                ->label('Titre du sous-menu')
                                                ->live(onBlur: true),
                                            TextInput::make('image_slug')
                                                ->label('Image (slug média)')
                                                ->helperText('Facultatif'),
                                        ]),
                                        Repeater::make('links')
                                            ->label('Liens')
                                            ->reorderable(true)
                                            ->addActionLabel('Ajouter un lien')
                                            ->schema([
                                                Grid::make(2)->schema([
                                                    TextInput::make('label')
                                                        ->label('Libellé')
                                                        ->required(),
                                                    TextInput::make('url')
                                                        ->label('URL')
                                                        ->required(),
                                                ]),
                                            ]),
                                        Grid::make(2)->schema([
                                            TextInput::make('cta_text')
                                                ->label('Texte du CTA')
                                                ->helperText('Facultatif — bouton en bas du sous-menu'),
                                            TextInput::make('cta_url')
                                                ->label('URL du CTA'),
                                        ]),
                                    ]),
                            ]),
                    ]),

                Section::make('Appel à l\'action (CTA)')
                    ->description('Bouton affiché tout à droite du menu')
                    ->components([
                        Grid::make(3)->components([
                            TextInput::make('cta_text')
                                ->label('Texte du CTA'),
                            TextInput::make('cta_url')
                                ->label('URL')
                                ->helperText('Lien de redirection'),
                            TextInput::make('cta_action')
                                ->label('Action JS')
                                ->helperText('Alternatif à l\'URL (ex: openBooking())'),
                        ]),
                    ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $setting = Setting::find('menu');
        if (!$setting) {
            Setting::create([
                'key' => 'menu',
                'value' => json_encode($data),
                'type' => 'json',
                'group' => 'menu',
                'label' => 'Configuration du menu',
            ]);
        } else {
            $setting->update(['value' => json_encode($data)]);
        }

        Cache::forget('setting.menu');
        Cache::forget('settings.group.menu');

        Notification::make()
            ->title('Menu sauvegardé')
            ->success()
            ->send();
    }

    private function getDefaultMenu(): array
    {
        return [
            'logo_slug' => null,
            'text' => 'sensaë',
            'entries' => [],
            'cta_text' => '',
            'cta_url' => '',
            'cta_action' => '',
        ];
    }
}
