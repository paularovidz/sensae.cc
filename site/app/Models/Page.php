<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Page extends Model
{
    use HasUuids;

    protected $fillable = [
        'title', 'slug', 'content', 'h1', 'image',
        'map_latitude', 'map_longitude', 'map_city', 'map_travel_time', 'map_route_geometry',
        'meta_title', 'meta_description', 'template', 'is_published',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'map_latitude' => 'decimal:7',
            'map_longitude' => 'decimal:7',
            'map_route_geometry' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Page $page) {
            if ($page->isDirty('map_city') && $page->map_city) {
                $page->geocodeCity();
            }

            if (! $page->map_city) {
                $page->map_latitude = null;
                $page->map_longitude = null;
                $page->map_travel_time = null;
                $page->map_route_geometry = null;
            }
        });
    }

    public function geocodeCity(): void
    {
        try {
            // Nominatim → lat/lng
            $geo = Http::withHeaders(['User-Agent' => 'sensae-site/1.0'])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $this->map_city . ', France',
                    'format' => 'json',
                    'limit' => 1,
                ]);

            if (! $geo->ok() || empty($geo->json())) {
                return;
            }

            $this->map_latitude = $geo->json()[0]['lat'];
            $this->map_longitude = $geo->json()[0]['lon'];

            // OSRM → temps de trajet
            $baseLat = Setting::get('map_base_latitude', '50.8792100');
            $baseLng = Setting::get('map_base_longitude', '2.0746580');

            $route = Http::get(
                "https://router.project-osrm.org/route/v1/driving/{$this->map_longitude},{$this->map_latitude};{$baseLng},{$baseLat}?overview=full&geometries=geojson"
            );

            if ($route->ok() && ($routes = $route->json()['routes'] ?? null)) {
                $this->map_travel_time = (int) round($routes[0]['duration'] / 60);
                $this->map_route_geometry = $routes[0]['geometry']['coordinates'];
            }
        } catch (\Throwable $e) {
            Log::warning('Page geocoding failed for "' . $this->map_city . '": ' . $e->getMessage());
        }
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }
}
