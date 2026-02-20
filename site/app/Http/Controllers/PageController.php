<?php

namespace App\Http\Controllers;

use App\Models\Page;

class PageController extends Controller
{
    public function show(string $slug)
    {
        $page = Page::published()->where('slug', $slug)->firstOrFail();

        $template = match ($page->template) {
            'location' => 'pages.location',
            'legal' => 'pages.legal',
            default => 'pages.default',
        };

        return view($template, ['page' => $page]);
    }

    public function location(string $city)
    {
        $slug = "salle-snoezelen-{$city}";
        $page = Page::published()->where('slug', $slug)->firstOrFail();

        return view('pages.location', ['page' => $page]);
    }
}
