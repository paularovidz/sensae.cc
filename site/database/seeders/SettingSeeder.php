<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Colors
            ['key' => 'color_primary', 'value' => '#721ad6', 'type' => 'string', 'group' => 'colors', 'label' => 'Couleur primaire'],
            ['key' => 'color_secondary', 'value' => '#D299FF', 'type' => 'string', 'group' => 'colors', 'label' => 'Couleur secondaire'],
            ['key' => 'color_accent', 'value' => '#1f122e', 'type' => 'string', 'group' => 'colors', 'label' => 'Couleur accent'],
            ['key' => 'color_body', 'value' => '#0e0c0c', 'type' => 'string', 'group' => 'colors', 'label' => 'Couleur fond'],
            ['key' => 'color_border', 'value' => '#242222', 'type' => 'string', 'group' => 'colors', 'label' => 'Couleur bordures'],
            ['key' => 'color_text_default', 'value' => '#C8C1C1', 'type' => 'string', 'group' => 'colors', 'label' => 'Couleur texte'],
            ['key' => 'color_text_light', 'value' => '#fff6f6', 'type' => 'string', 'group' => 'colors', 'label' => 'Couleur texte clair'],

            // Contact
            ['key' => 'contact_email', 'value' => 'bonjour@sensea.cc', 'type' => 'string', 'group' => 'contact', 'label' => 'Email'],
            ['key' => 'contact_phone', 'value' => '', 'type' => 'string', 'group' => 'contact', 'label' => 'Téléphone'],
            ['key' => 'contact_address', 'value' => '', 'type' => 'string', 'group' => 'contact', 'label' => 'Adresse'],

            // Social
            ['key' => 'social_facebook', 'value' => '', 'type' => 'string', 'group' => 'social', 'label' => 'Facebook'],
            ['key' => 'social_instagram', 'value' => '', 'type' => 'string', 'group' => 'social', 'label' => 'Instagram'],
            ['key' => 'social_linkedin', 'value' => '', 'type' => 'string', 'group' => 'social', 'label' => 'LinkedIn'],

            // Menu
            ['key' => 'menu', 'value' => json_encode([
                'logo_slug' => null,
                'text' => 'sensëa',
                'entries' => [
                    ['label' => 'Accueil', 'type' => 'link', 'url' => '/', 'submenus' => []],
                    ['label' => 'Conseils', 'type' => 'link', 'url' => '/conseils', 'submenus' => []],
                    ['label' => 'FAQ', 'type' => 'link', 'url' => '/faq', 'submenus' => []],
                ],
                'cta_text' => 'Réserver',
                'cta_url' => '/reserver',
                'cta_action' => '',
            ]), 'type' => 'json', 'group' => 'menu', 'label' => 'Configuration du menu'],

            // SEO
            ['key' => 'seo_title', 'value' => 'sensëa - Salle Snoezelen', 'type' => 'string', 'group' => 'seo', 'label' => 'Titre du site'],
            ['key' => 'seo_description', 'value' => 'Salle Snoezelen pour des séances de stimulation multisensorielle dans un environnement adapté et bienveillant.', 'type' => 'string', 'group' => 'seo', 'label' => 'Description du site'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
