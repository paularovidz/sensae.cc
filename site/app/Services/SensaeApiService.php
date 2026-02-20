<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SensaeApiService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.sensae.api_url'), '/');
    }

    public function getSchedule(): ?array
    {
        return Cache::remember('sensae.schedule', 300, function () {
            try {
                $response = Http::timeout(5)->get("{$this->baseUrl}/public/availability/schedule");

                if ($response->successful()) {
                    return $response->json();
                }
            } catch (\Exception $e) {
                Log::warning('SensaeAPI schedule fetch failed: ' . $e->getMessage());
            }

            return null;
        });
    }

    public function getPricing(): array
    {
        return Cache::remember('sensae.pricing', 300, function () {
            try {
                $response = Http::timeout(5)->get("{$this->baseUrl}/public/pricing");

                if ($response->successful()) {
                    return $response->json()['data'] ?? [];
                }
            } catch (\Exception $e) {
                Log::warning('SensaeAPI pricing fetch failed: ' . $e->getMessage());
            }

            return [];
        });
    }

    public function getNextAvailability(): ?array
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/public/availability/dates", [
                'year' => now()->year,
                'month' => now()->month,
                'type' => 'regular',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $dates = $data['dates'] ?? [];

                if (empty($dates)) {
                    // Try next month
                    $nextMonth = now()->addMonth();
                    $response = Http::timeout(5)->get("{$this->baseUrl}/public/availability/dates", [
                        'year' => $nextMonth->year,
                        'month' => $nextMonth->month,
                        'type' => 'regular',
                    ]);

                    if ($response->successful()) {
                        $dates = $response->json()['dates'] ?? [];
                    }
                }

                if (!empty($dates)) {
                    $nextDate = $dates[0];
                    $daysUntil = (int) now()->startOfDay()->diffInDays($nextDate, false);

                    return [
                        'available' => true,
                        'next_date' => $nextDate,
                        'days_until' => max(0, $daysUntil),
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::warning('SensaeAPI availability fetch failed: ' . $e->getMessage());
        }

        return ['available' => false];
    }
}
