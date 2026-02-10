<?php

declare(strict_types=1);

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

class JWTService
{
    private const ACCESS_TOKEN_EXPIRY = 900; // 15 minutes

    private static function env(string $key, ?string $default = null): ?string
    {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }

    public static function generateAccessToken(array $payload): string
    {
        $issuedAt = time();
        $payload = array_merge($payload, [
            'iss' => 'ops.sensea.cc',
            'iat' => $issuedAt,
            'exp' => $issuedAt + self::ACCESS_TOKEN_EXPIRY,
            'type' => 'access'
        ]);

        return JWT::encode($payload, self::env('JWT_SECRET'), 'HS256');
    }

    public static function verifyAccessToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key(self::env('JWT_SECRET'), 'HS256'));
            $payload = (array)$decoded;

            if (($payload['type'] ?? '') !== 'access') {
                return null;
            }

            return $payload;
        } catch (ExpiredException $e) {
            return null;
        } catch (\Exception $e) {
            error_log('JWT verification error: ' . $e->getMessage());
            return null;
        }
    }

    public static function extractTokenFromHeader(): ?string
    {
        // First check Authorization header
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
            return $matches[1];
        }

        // Also check Apache-specific header (CGI mode)
        $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';

        if (preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
            return $matches[1];
        }

        // Fallback to query parameter (for document view/download in new tabs)
        if (!empty($_GET['token'])) {
            return $_GET['token'];
        }

        return null;
    }

    public static function getAccessTokenExpiry(): int
    {
        return self::ACCESS_TOKEN_EXPIRY;
    }

    /**
     * @deprecated Use verifyAccessToken instead
     */
    public static function decode(string $token): ?object
    {
        try {
            return JWT::decode($token, new Key(self::env('JWT_SECRET'), 'HS256'));
        } catch (\Exception $e) {
            return null;
        }
    }
}
