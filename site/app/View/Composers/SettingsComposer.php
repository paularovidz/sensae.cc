<?php

namespace App\View\Composers;

use App\Models\Setting;
use Illuminate\View\View;

class SettingsComposer
{
    public function compose(View $view): void
    {
        $menu = Setting::get('menu', [
            'logo_slug' => null,
            'text' => 'sensaë',
            'entries' => [],
            'cta_text' => '',
            'cta_url' => '',
            'cta_action' => '',
        ]);

        $view->with([
            'colors' => Setting::getGroup('colors'),
            'contact' => Setting::getGroup('contact'),
            'social' => Setting::getGroup('social'),
            'seo' => Setting::getGroup('seo'),
            'rating' => Setting::getGroup('rating'),
            'menu' => $menu,
        ]);
    }
}
