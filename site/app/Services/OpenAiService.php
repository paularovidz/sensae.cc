<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;

class OpenAiService
{
    private const COSTS = [
        'gpt-4o-mini' => ['input' => 0.15 / 1_000_000, 'output' => 0.60 / 1_000_000],
        'gpt-4o' => ['input' => 2.50 / 1_000_000, 'output' => 10.00 / 1_000_000],
    ];

    public function generateArticle(string $subject, int $wordCount, string $tone, ?string $context = null): array
    {
        $model = config('openai.model', 'gpt-4o-mini');
        $toneInstruction = $tone === 'je'
            ? 'Utilise la première personne du singulier (je).'
            : 'Utilise la première personne du pluriel (nous).';

        $contextBlock = $context ? "\n\nContexte supplémentaire fourni par l'utilisateur :\n{$context}" : '';

        $systemPrompt = <<<PROMPT
Tu es un rédacteur web expert en SEO pour sensaë, une salle Snoezelen (thérapie multisensorielle) située à Audruicq, dans le Pas-de-Calais. Tu rédiges des articles de blog destinés à des parents, aidants et professionnels du médico-social.

Règles de rédaction :
- Langage soutenu mais accessible, jamais condescendant
- Phrases de 15 à 20 mots maximum
- Pas de voix passive
- {$toneInstruction}
- Mots interdits : "en conclusion", "cependant", "néanmoins", "toutefois", "il est important de noter", "dans cet article", "en effet", "fondamentalement", "il convient de"
- Espaces insécables avant : ; ! ? et après les guillemets français «\u{00A0} \u{00A0}»
- Utilise <strong> sur 5 à 10% des mots clés SEO importants
- Maximum 30% du contenu en listes à puces
- Structure avec des <h2> et <h3> pertinents
- Inclus une introduction engageante (2-3 phrases) et une ouverture finale (pas de "conclusion")
- Le contenu doit faire environ {$wordCount} mots

Retourne UNIQUEMENT du JSON valide, sans markdown, sans bloc de code, avec cette structure exacte :
{
  "seo_title": "Titre SEO de 50-60 caractères",
  "meta_description": "Meta description de 150-160 caractères",
  "content": "<p>Introduction...</p><h2>...</h2><p>...</p>..."
}
PROMPT;

        $response = OpenAI::chat()->create([
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => "Rédige un article complet sur le sujet suivant : {$subject}{$contextBlock}"],
            ],
            'temperature' => 0.7,
            'response_format' => ['type' => 'json_object'],
        ]);

        $usage = $response->usage;
        $costs = self::COSTS[$model] ?? self::COSTS['gpt-4o-mini'];
        $costEstimate = ($usage->promptTokens * $costs['input']) + ($usage->completionTokens * $costs['output']);

        $content = json_decode($response->choices[0]->message->content, true);

        return [
            'seo_title' => $content['seo_title'] ?? '',
            'meta_description' => $content['meta_description'] ?? '',
            'content' => $content['content'] ?? '',
            'usage' => [
                'model' => $model,
                'prompt_tokens' => $usage->promptTokens,
                'completion_tokens' => $usage->completionTokens,
                'total_tokens' => $usage->totalTokens,
                'cost_estimate' => round($costEstimate, 6),
            ],
        ];
    }
}
