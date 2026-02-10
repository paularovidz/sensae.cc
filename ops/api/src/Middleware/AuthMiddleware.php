<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Services\JWTService;
use App\Utils\Response;

class AuthMiddleware
{
    private static ?array $currentUser = null;

    public static function handle(): void
    {
        $token = JWTService::extractTokenFromHeader();

        if (!$token) {
            Response::unauthorized('Token manquant');
        }

        $payload = JWTService::verifyAccessToken($token);

        if (!$payload) {
            Response::unauthorized('Token invalide ou expire');
        }

        self::$currentUser = $payload;
    }

    public static function getCurrentUser(): ?array
    {
        return self::$currentUser;
    }

    public static function getUserId(): ?string
    {
        return self::$currentUser['user_id'] ?? null;
    }
}
