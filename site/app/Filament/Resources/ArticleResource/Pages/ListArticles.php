<?php

namespace App\Filament\Resources\ArticleResource\Pages;

use App\Filament\Resources\ArticleResource;
use App\Models\AiGeneration;
use App\Models\Article;
use App\Models\Setting;
use App\Services\OpenAiService;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Str;

class ListArticles extends ListRecords
{
    protected static string $resource = ArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generateWithAi')
                ->label('Générer avec IA')
                ->icon('heroicon-o-sparkles')
                ->color('warning')
                ->form([
                    TextInput::make('subject')
                        ->label('Sujet de l\'article')
                        ->placeholder('Ex : Les bienfaits du Snoezelen pour les personnes autistes')
                        ->required(),
                    TextInput::make('word_count')
                        ->label('Nombre de mots')
                        ->numeric()
                        ->minValue(400)
                        ->maxValue(1200)
                        ->default(800)
                        ->required(),
                    Textarea::make('context')
                        ->label('Contexte supplémentaire')
                        ->placeholder('Informations spécifiques à inclure, angle particulier...')
                        ->rows(3),
                    Select::make('tone')
                        ->label('Tonalité')
                        ->options([
                            'je' => 'Je (première personne du singulier)',
                            'nous' => 'Nous (première personne du pluriel)',
                        ])
                        ->default(fn () => Setting::get('ai_default_tone', 'nous'))
                        ->required(),
                ])
                ->modalHeading('Générer un article avec l\'IA')
                ->modalDescription('L\'article sera créé en brouillon. Vous pourrez le modifier avant publication.')
                ->modalSubmitActionLabel('Générer')
                ->action(function (array $data) {
                    try {
                        $service = new OpenAiService();
                        $result = $service->generateArticle(
                            $data['subject'],
                            (int) $data['word_count'],
                            $data['tone'],
                            $data['context'] ?? null,
                        );

                        $article = Article::create([
                            'title' => $result['seo_title'],
                            'slug' => Str::slug($result['seo_title']),
                            'content' => $result['content'],
                            'excerpt' => $result['meta_description'],
                            'is_published' => false,
                        ]);

                        AiGeneration::create([
                            'subject' => $data['subject'],
                            'word_count' => $data['word_count'],
                            'tone' => $data['tone'],
                            'context' => $data['context'] ?? null,
                            'model' => $result['usage']['model'],
                            'prompt_tokens' => $result['usage']['prompt_tokens'],
                            'completion_tokens' => $result['usage']['completion_tokens'],
                            'total_tokens' => $result['usage']['total_tokens'],
                            'cost_estimate' => $result['usage']['cost_estimate'],
                            'article_id' => $article->id,
                        ]);

                        Notification::make()
                            ->title('Article généré avec succès')
                            ->body("Tokens : {$result['usage']['total_tokens']} — Coût : \${$result['usage']['cost_estimate']}")
                            ->success()
                            ->send();

                        return redirect(ArticleResource::getUrl('edit', ['record' => $article]));
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Erreur lors de la génération')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Actions\CreateAction::make(),
        ];
    }
}
