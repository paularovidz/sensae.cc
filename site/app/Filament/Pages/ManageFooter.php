<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class ManageFooter extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-bars-3-bottom-right';
    protected static ?string $navigationLabel = 'Footer';
    protected static ?string $title = 'Footer';
    protected static ?int $navigationSort = 9;

    protected string $view = 'filament.pages.manage-footer';

    public ?array $data = [];

    public function mount(): void
    {
        $footer = Setting::get('footer', $this->getDefaultFooter());

        $this->form->fill($footer);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Description')
                    ->description('Paragraphe de présentation affiché sous le logo')
                    ->components([
                        Textarea::make('description')
                            ->label('Texte de présentation')
                            ->rows(4)
                            ->required(),
                    ]),

                Section::make('Colonnes de liens')
                    ->description('Colonnes affichées à droite (max 4)')
                    ->components([
                        Repeater::make('columns')
                            ->label('')
                            ->maxItems(4)
                            ->reorderable(true)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'Nouvelle colonne')
                            ->addActionLabel('Ajouter une colonne')
                            ->schema([
                                TextInput::make('title')
                                    ->label('Titre')
                                    ->required()
                                    ->live(onBlur: true),
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
                            ]),
                    ]),

                Section::make('Barre de copyright')
                    ->description('Liens et crédit affichés en bas du footer')
                    ->components([
                        Repeater::make('bottom_links')
                            ->label('Liens bas de page')
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
                        Grid::make(2)->components([
                            TextInput::make('credit_name')
                                ->label('Nom du crédit')
                                ->helperText('Ex: Paul Delcloy'),
                            TextInput::make('credit_url')
                                ->label('URL du crédit')
                                ->helperText('Ex: https://pauld.fr'),
                        ]),
                    ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $setting = Setting::find('footer');
        if (!$setting) {
            Setting::create([
                'key' => 'footer',
                'value' => json_encode($data),
                'type' => 'json',
                'group' => 'footer',
                'label' => 'Configuration du footer',
            ]);
        } else {
            $setting->update(['value' => json_encode($data)]);
        }

        Cache::forget('setting.footer');
        Cache::forget('settings.group.footer');

        Notification::make()
            ->title('Footer sauvegardé')
            ->success()
            ->send();
    }

    private function getDefaultFooter(): array
    {
        return [
            'description' => 'sensëa est une salle multi-sensorielle basée à Audruicq. Fondée par Céline Delcloy, éducatrice spécialisée libéral depuis 2023 et formée par Pétrarque, redécouvrez vos sens et profitez d\'une bulle de paix lors de séances adaptées à tous et toutes.',
            'columns' => [
                [
                    'title' => 'Ressources',
                    'links' => [
                        ['label' => 'Comprendre', 'url' => '/comprendre-sens/'],
                        ['label' => 'Conseils', 'url' => '/conseils/'],
                        ['label' => 'FAQ', 'url' => '/faq/'],
                    ],
                ],
                [
                    'title' => 'Liens rapides',
                    'links' => [
                        ['label' => 'Salle snoezelen Calais', 'url' => '/salle-snoezelen-calais/'],
                        ['label' => 'Salle snoezelen Dunkerque', 'url' => '/salle-snoezelen-dunkerque/'],
                        ['label' => 'Salle snoezelen Boulogne-sur-mer', 'url' => '/salle-snoezelen-boulogne-sur-mer/'],
                        ['label' => 'Salle snoezelen Saint-Omer', 'url' => '/salle-snoezelen-saint-omer/'],
                        ['label' => 'Salle snoezelen Ardres', 'url' => '/salle-snoezelen-ardres/'],
                        ['label' => 'Salle snoezelen Pas-de-Calais', 'url' => '/salle-snoezelen-pas-de-calais/'],
                    ],
                ],
            ],
            'bottom_links' => [
                ['label' => 'Mentions légales', 'url' => '/mentions-legales/'],
            ],
            'credit_name' => 'Paul Delcloy',
            'credit_url' => 'https://pauld.fr',
        ];
    }
}
