<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Utils\Response;

class ApiKeyMiddleware
{
    public static function verify(): void
    {
        $apiKey = self::getApiKey();
        $expectedKey = $_ENV['OPS_API_KEY'] ?? getenv('OPS_API_KEY') ?: null;

        if (!$expectedKey) {
            Response::error('API Key not configured on server', 500);
        }

        if (!$apiKey || $apiKey !== $expectedKey) {
            Response::unauthorized('Invalid API Key');
        }
    }

    private static function getApiKey(): ?string
    {
        // Check header first
        $headers = apache_request_headers();
        $apiKey = $headers['X-API-Key'] ?? $headers['x-api-key'] ?? null;

        if ($apiKey) {
            return $apiKey;
        }

        // Fallback to query parameter
        return $_GET['api_key'] ?? null;
    }
}
