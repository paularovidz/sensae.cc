<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Utils\Response;
use App\Services\AuditService;

class RateLimitMiddleware
{
    private static string $cacheDir = '/tmp/rate_limit';

    private static function env(string $key, ?string $default = null): ?string
    {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }

    public static function handle(int $maxRequests = null, int $windowSeconds = null): void
    {
        $maxRequests = $maxRequests ?? (int)self::env('RATE_LIMIT_REQUESTS', '60');
        $windowSeconds = $windowSeconds ?? (int)self::env('RATE_LIMIT_WINDOW', '60');

        $ip = AuditService::getClientIp();
        $key = md5($ip . $_SERVER['REQUEST_URI']);

        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0755, true);
        }

        $file = self::$cacheDir . '/' . $key;
        $now = time();

        $data = ['requests' => [], 'blocked_until' => null];

        if (file_exists($file)) {
            $content = file_get_contents($file);
            $data = json_decode($content, true) ?: $data;
        }

        // Check if IP is blocked
        if (isset($data['blocked_until']) && $data['blocked_until'] > 0) {
            if ($now < $data['blocked_until']) {
                $retryAfter = $data['blocked_until'] - $now;
                header("Retry-After: {$retryAfter}");
                Response::error('Too many requests. Please try again later.', 429);
            }
            // Block expired, reset
            $data['blocked_until'] = null;
            $data['requests'] = [];
            file_put_contents($file, json_encode($data));
        }

        // Clean old requests
        $data['requests'] = array_filter(
            $data['requests'],
            fn($timestamp) => $timestamp > ($now - $windowSeconds)
        );

        // Check rate limit
        if (count($data['requests']) >= $maxRequests) {
            $data['blocked_until'] = $now + $windowSeconds;
            file_put_contents($file, json_encode($data));

            header("Retry-After: {$windowSeconds}");
            Response::error('Too many requests. Please try again later.', 429);
        }

        // Add current request
        $data['requests'][] = $now;
        $data['blocked_until'] = null;

        file_put_contents($file, json_encode($data));

        // Add rate limit headers
        $remaining = max(0, $maxRequests - count($data['requests']));
        header("X-RateLimit-Limit: {$maxRequests}");
        header("X-RateLimit-Remaining: {$remaining}");
        header("X-RateLimit-Reset: " . ($now + $windowSeconds));
    }

    public static function handleStrict(): void
    {
        // Stricter rate limit for sensitive endpoints (e.g., magic link requests)
        self::handle(5, 300); // 5 requests per 5 minutes
    }

    public static function clearCache(): void
    {
        if (is_dir(self::$cacheDir)) {
            $files = glob(self::$cacheDir . '/*');
            foreach ($files as $file) {
                unlink($file);
            }
        }
    }
}
