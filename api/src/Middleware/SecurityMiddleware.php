<?php

declare(strict_types=1);

namespace App\Middleware;

class SecurityMiddleware
{
    private static function env(string $key, ?string $default = null): ?string
    {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }

    public static function handle(): void
    {
        // Security headers
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

        // Content Security Policy
        header("Content-Security-Policy: default-src 'self'; frame-ancestors 'none'");

        // HSTS (only in production)
        if (self::env('ENV', 'production') === 'production') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }

        // Remove PHP version exposure
        header_remove('X-Powered-By');

        // Prevent clickjacking
        header('X-Frame-Options: SAMEORIGIN');
    }
}
