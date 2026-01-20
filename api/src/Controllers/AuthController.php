<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Models\MagicLink;
use App\Models\RefreshToken;
use App\Services\JWTService;
use App\Services\MailService;
use App\Services\AuditService;
use App\Utils\Response;
use App\Utils\Validator;

class AuthController
{
    public function requestMagicLink(): void
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        $validator = new Validator($data);
        $validator->required('email')->email('email');
        $validator->validate();

        $email = strtolower(trim($data['email']));

        // Rate limit: max 3 requests per hour per email
        $recentRequests = MagicLink::countRecentRequestsByEmail($email, 60);
        if ($recentRequests >= 3) {
            Response::error('Trop de demandes de connexion. Veuillez patienter avant de réessayer.', 429);
        }

        $user = User::findByEmail($email);

        // Always return success to prevent email enumeration
        if (!$user || !$user['is_active']) {
            // Log attempt for security monitoring
            AuditService::log(null, 'magic_link_request_unknown_email', 'user', null, null, ['email' => $email]);

            // Fake delay to prevent timing attacks
            usleep(random_int(100000, 300000));

            Response::success(null, 'Si cette adresse email est associée à un compte, vous recevrez un lien de connexion.');
        }

        // Generate magic link
        $token = MagicLink::create($user['id'], AuditService::getClientIp());

        // Send email
        $mailService = new MailService();
        $sent = $mailService->sendMagicLink($user['email'], $user['first_name'], $token);

        AuditService::log($user['id'], 'magic_link_requested', 'user', $user['id']);

        if (!$sent) {
            error_log("Failed to send magic link email to: {$email}");
        }

        Response::success(null, 'Si cette adresse email est associée à un compte, vous recevrez un lien de connexion.');
    }

    public function verifyMagicLink(string $token): void
    {
        if (empty($token)) {
            Response::error('Token manquant', 400);
        }

        $magicLinkData = MagicLink::verify($token);

        if (!$magicLinkData) {
            AuditService::log(null, 'magic_link_invalid', null, null, null, ['token_prefix' => substr($token, 0, 8)]);
            Response::error('Lien invalide ou expiré. Veuillez demander un nouveau lien de connexion.', 401);
        }

        // Mark as used
        MagicLink::markAsUsed($magicLinkData['id']);

        $userId = $magicLinkData['user_id'];
        $user = User::findById($userId);

        if (!$user || !$user['is_active']) {
            Response::error('Compte désactivé', 401);
        }

        // Generate tokens
        $accessToken = JWTService::generateAccessToken([
            'user_id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role']
        ]);

        $refreshToken = RefreshToken::create(
            $user['id'],
            AuditService::getClientIp(),
            $_SERVER['HTTP_USER_AGENT'] ?? null
        );

        AuditService::log($user['id'], 'login_success', 'user', $user['id']);

        Response::success([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in' => JWTService::getAccessTokenExpiry(),
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'login' => $user['login'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'role' => $user['role']
            ]
        ], 'Connexion réussie');
    }

    public function refresh(): void
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        if (empty($data['refresh_token'])) {
            Response::error('Refresh token manquant', 400);
        }

        $tokenData = RefreshToken::verify($data['refresh_token']);

        if (!$tokenData) {
            Response::unauthorized('Refresh token invalide ou expiré');
        }

        $user = User::findById($tokenData['user_id']);

        if (!$user || !$user['is_active']) {
            RefreshToken::revoke($data['refresh_token']);
            Response::unauthorized('Compte désactivé');
        }

        // Rotate refresh token
        $newRefreshToken = RefreshToken::rotate(
            $data['refresh_token'],
            AuditService::getClientIp(),
            $_SERVER['HTTP_USER_AGENT'] ?? null
        );

        // Generate new access token
        $accessToken = JWTService::generateAccessToken([
            'user_id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role']
        ]);

        Response::success([
            'access_token' => $accessToken,
            'refresh_token' => $newRefreshToken,
            'expires_in' => JWTService::getAccessTokenExpiry()
        ], 'Token rafraîchi');
    }

    public function logout(): void
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        if (!empty($data['refresh_token'])) {
            RefreshToken::revoke($data['refresh_token']);
        }

        // Try to get user from access token for audit
        $token = JWTService::extractTokenFromHeader();
        if ($token) {
            $payload = JWTService::verifyAccessToken($token);
            if ($payload) {
                AuditService::log($payload['user_id'] ?? null, 'logout', 'user', $payload['user_id'] ?? null);
            }
        }

        Response::success(null, 'Déconnexion réussie');
    }
}
