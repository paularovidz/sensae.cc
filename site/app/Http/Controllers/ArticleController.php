<?php

namespace App\Http\Controllers;

use App\Models\Article;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = Article::published()
            ->orderByDesc('published_at')
            ->paginate(12);

        return view('articles.index', [
            'articles' => $articles,
        ]);
    }

    public function show(string $slug)
    {
        $article = Article::published()->where('slug', $slug)->firstOrFail();

        return view('articles.show', [
            'article' => $article,
        ]);
    }
}
