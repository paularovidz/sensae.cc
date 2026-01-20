<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Services\JWTService;
use App\Models\User;
use App\Utils\Response;

class AuthMiddleware
{
    private static ?array $currentUser = null;

    public static function handle(): void
    {
        $token = JWTService::extractTokenFromHeader();

        if (!$token) {
            Response::unauthorized('Token d\'authentification manquant');
        }

        $payload = JWTService::verifyAccessToken($token);

        if (!$payload) {
            Response::unauthorized('Token invalide ou expiré');
        }

        // Verify user still exists and is active
        $user = User::findById($payload['user_id'] ?? '');

        if (!$user || !$user['is_active']) {
            Response::unauthorized('Compte désactivé ou inexistant');
        }

        self::$currentUser = $user;
    }

    public static function requireAdmin(): void
    {
        self::handle();

        if (self::$currentUser['role'] !== 'admin') {
            Response::forbidden('Accès réservé aux administrateurs');
        }
    }

    public static function getCurrentUser(): ?array
    {
        return self::$currentUser;
    }

    public static function getCurrentUserId(): ?string
    {
        return self::$currentUser['id'] ?? null;
    }

    public static function isAdmin(): bool
    {
        return (self::$currentUser['role'] ?? '') === 'admin';
    }

    public static function setCurrentUser(array $user): void
    {
        self::$currentUser = $user;
    }
}
