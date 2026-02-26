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
            ['key' => 'contact_email', 'value' => 'bonjour@sensae.cc', 'type' => 'string', 'group' => 'contact', 'label' => 'Email'],
            ['key' => 'contact_phone', 'value' => '', 'type' => 'string', 'group' => 'contact', 'label' => 'Téléphone'],
            ['key' => 'contact_address', 'value' => '', 'type' => 'string', 'group' => 'contact', 'label' => 'Adresse'],
            ['key' => 'typeform_id', 'value' => 'EjSgPPjE', 'type' => 'string', 'group' => 'contact', 'label' => 'ID Typeform (contact)'],
            ['key' => 'typeform_gift_id', 'value' => '', 'type' => 'string', 'group' => 'contact', 'label' => 'ID Typeform (carte cadeau)'],

            // Social
            ['key' => 'social_facebook', 'value' => '', 'type' => 'string', 'group' => 'social', 'label' => 'Facebook'],
            ['key' => 'social_instagram', 'value' => '', 'type' => 'string', 'group' => 'social', 'label' => 'Instagram'],
            ['key' => 'social_linkedin', 'value' => '', 'type' => 'string', 'group' => 'social', 'label' => 'LinkedIn'],

            // Menu
            ['key' => 'menu', 'value' => json_encode([
                'logo_slug' => null,
                'text' => 'sensaë',
                'entries' => [
                    ['label' => 'Accueil', 'type' => 'link', 'url' => '/', 'submenus' => []],
                    ['label' => 'Conseils', 'type' => 'link', 'url' => '/conseils', 'submenus' => []],
                    ['label' => 'FAQ', 'type' => 'link', 'url' => '/faq', 'submenus' => []],
                ],
                'cta_text' => 'Réserver',
                'cta_url' => '/reserver',
                'cta_action' => '',
            ]), 'type' => 'json', 'group' => 'menu', 'label' => 'Configuration du menu'],

            // Footer
            ['key' => 'footer', 'value' => json_encode([
                'description' => 'sensëa est une salle multi-sensorielle basée à Audruicq. Fondée par Céline Delcloy, éducatrice spécialisée libéral depuis 2023 et formée par Pétrarque, redécouvrez vos sens et profitez d\'une bulle de paix lors de séances adaptées à tous et toutes.',
                'columns' => [
                    [
                        'title' => 'Ressources',
                        'links' => [
                            ['label' => 'Comprendre', 'url' => '/comprendre-sens/'],
                            ['label' => 'Conseils', 'url' => '/conseils/'],
                            ['label' => 'FAQ', 'url' => '/faq/'],
                        ],
                    ],
                    [
                        'title' => 'Liens rapides',
                        'links' => [
                            ['label' => 'Salle snoezelen Calais', 'url' => '/salle-snoezelen-calais/'],
                            ['label' => 'Salle snoezelen Dunkerque', 'url' => '/salle-snoezelen-dunkerque/'],
                            ['label' => 'Salle snoezelen Boulogne-sur-mer', 'url' => '/salle-snoezelen-boulogne-sur-mer/'],
                            ['label' => 'Salle snoezelen Saint-Omer', 'url' => '/salle-snoezelen-saint-omer/'],
                            ['label' => 'Salle snoezelen Ardres', 'url' => '/salle-snoezelen-ardres/'],
                            ['label' => 'Salle snoezelen Pas-de-Calais', 'url' => '/salle-snoezelen-pas-de-calais/'],
                        ],
                    ],
                ],
                'bottom_links' => [
                    ['label' => 'Mentions légales', 'url' => '/mentions-legales/'],
                ],
                'credit_name' => 'Paul Delcloy',
                'credit_url' => 'https://pauld.fr',
            ]), 'type' => 'json', 'group' => 'footer', 'label' => 'Configuration du footer'],

            // Rating (CTA section)
            ['key' => 'rating_score', 'value' => '', 'type' => 'string', 'group' => 'rating', 'label' => 'Note (ex: 4.95/5)'],
            ['key' => 'rating_label', 'value' => '', 'type' => 'string', 'group' => 'rating', 'label' => 'Libellé (ex: Avis Google)'],

            // Map
            ['key' => 'map_base_name', 'value' => 'Audruicq', 'type' => 'string', 'group' => 'map', 'label' => 'Nom du lieu de base'],
            ['key' => 'map_base_latitude', 'value' => '50.8792100', 'type' => 'string', 'group' => 'map', 'label' => 'Latitude du lieu de base'],
            ['key' => 'map_base_longitude', 'value' => '2.0746580', 'type' => 'string', 'group' => 'map', 'label' => 'Longitude du lieu de base'],

            // SEO
            ['key' => 'seo_title', 'value' => 'sensaë - Salle Snoezelen', 'type' => 'string', 'group' => 'seo', 'label' => 'Titre du site'],
            ['key' => 'seo_description', 'value' => 'Salle Snoezelen pour des séances de stimulation multisensorielle dans un environnement adapté et bienveillant.', 'type' => 'string', 'group' => 'seo', 'label' => 'Description du site'],
            ['key' => 'seo_og_image', 'value' => '', 'type' => 'string', 'group' => 'seo', 'label' => 'Image Open Graph'],
            ['key' => 'seo_canonical_url', 'value' => 'https://sensae.cc', 'type' => 'string', 'group' => 'seo', 'label' => 'URL canonique'],
            ['key' => 'seo_plausible_domain', 'value' => '', 'type' => 'string', 'group' => 'seo', 'label' => 'Domaine Plausible'],
            ['key' => 'seo_schema_type', 'value' => 'LocalBusiness', 'type' => 'string', 'group' => 'seo', 'label' => 'Type Schema.org'],
            ['key' => 'seo_schema_price_range', 'value' => '€€', 'type' => 'string', 'group' => 'seo', 'label' => 'Fourchette de prix'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
