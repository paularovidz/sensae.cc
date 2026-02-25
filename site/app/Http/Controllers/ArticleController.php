<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = Article::published()
            ->orderByDesc('created_at')
            ->paginate(12);

        return view('articles.index', [
            'articles' => $articles,
        ]);
    }

    public function show(string $slug)
    {
        $article = Article::published()->where('slug', $slug)->firstOrFail();

        // Generate TOC from h2 headings and ensure they have IDs
        $toc = [];
        $content = preg_replace_callback(
            '/<h2([^>]*)>(.*?)<\/h2>/si',
            function ($matches) use (&$toc) {
                $attrs = $matches[1];
                $text = strip_tags($matches[2]);
                $id = Str::slug($text);

                // If h2 already has an id, use it
                if (preg_match('/id=["\']([^"\']+)["\']/', $attrs, $idMatch)) {
                    $id = $idMatch[1];
                } else {
                    $attrs .= ' id="' . $id . '"';
                }

                $toc[] = ['id' => $id, 'text' => $text];

                return '<h2' . $attrs . '>' . $matches[2] . '</h2>';
            },
            $article->content
        );

        return view('articles.show', [
            'article' => $article,
            'content' => $content,
            'toc' => $toc,
        ]);
    }
}
