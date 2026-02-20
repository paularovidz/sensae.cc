<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Link extends Component
{
    public string $href;
    public bool $obfuscated;
    public bool $blank;

    public function __construct(string $url, bool $obfuscated = false, bool $blank = false)
    {
        $this->obfuscated = $obfuscated;
        $this->blank = $blank;
        $this->href = $this->resolveUrl($url);
    }

    private function resolveUrl(string $url): string
    {
        if (preg_match('#^https?://#i', $url)) {
            return $url;
        }

        $base = rtrim(config('app.url'), '/');

        return $base . '/' . ltrim($url, '/');
    }

    public function render()
    {
        return view('components.link');
    }
}
