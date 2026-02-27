<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        Str::macro('cleanSlug', function (string $text): string {
            $stopWords = ['le', 'la', 'les', 'l', 'de', 'des', 'du', 'un', 'une',
                'et', 'en', 'au', 'aux', 'ce', 'ces', 'son', 'sa', 'ses',
                'mon', 'ma', 'mes', 'ton', 'ta', 'tes', 'que', 'qui', 'quoi',
                'dans', 'par', 'pour', 'sur', 'avec', 'est', 'sont', 'a', 'ont'];

            $slug = Str::slug($text);
            $parts = array_filter(explode('-', $slug), fn ($w) => !in_array($w, $stopWords));

            return implode('-', $parts) ?: $slug;
        });
    }
}
