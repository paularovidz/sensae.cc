<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Page;
use App\Models\Sens;

class SitemapController extends Controller
{
    public function index()
    {
        $staticUrls = [
            ['loc' => '/', 'priority' => '1.0', 'changefreq' => 'weekly'],
            ['loc' => '/comprendre-sens', 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['loc' => '/conseils', 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['loc' => '/faq', 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['loc' => '/tarifs-snoezelen-audruicq', 'priority' => '0.6', 'changefreq' => 'monthly'],
            ['loc' => '/voyage-sensoriel', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => '/premiere-seance', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => '/cadeau-naissance-sensoriel', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => '/cadeau-femme-enceinte-sensoriel', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => '/seance-snoezelen-autisme', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => '/seance-snoezelen-alzheimer', 'priority' => '0.7', 'changefreq' => 'monthly'],
        ];

        $sens = Sens::published()->select('slug', 'updated_at')->get();
        $articles = Article::published()->select('slug', 'updated_at')->get();
        $pages = Page::published()->select('slug', 'template', 'updated_at')->get();

        return response()
            ->view('sitemap', compact('staticUrls', 'sens', 'articles', 'pages'))
            ->header('Content-Type', 'application/xml');
    }
}
