<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Models\MagicLink;
use App\Models\RefreshToken;
use App\Services\JWTService;
use App\Services\MailService;
use App\Middleware\AuthMiddleware;
use App\Utils\Response;
use App\Utils\Validator;

class AuthController
{
    private static function getClientIp(): string
    {
        $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public function requestMagicLink(): void
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        $validator = Validator::make($data)
            ->required('email')
            ->email('email');

        if ($validator->fails()) {
            Response::validationError($validator->errors());
        }

        $email = strtolower(trim($data['email']));

        // Rate limit: max 2 requests per 5 minutes per email
        $windowMinutes = 5;
        $recentRequests = MagicLink::countRecentRequestsByEmail($email, $windowMinutes);
        if ($recentRequests >= 2) {
            $oldestRequest = MagicLink::getOldestRecentRequestByEmail($email, $windowMinutes);
            $waitSeconds = $oldestRequest ? ($windowMinutes * 60) - (time() - strtotime($oldestRequest['created_at'])) : $windowMinutes * 60;
            $waitMinutes = ceil($waitSeconds / 60);
            Response::error("Trop de demandes de connexion. Veuillez patienter {$waitMinutes} minute(s) avant de reessayer.", 429);
        }

        $user = User::findByEmail($email);

        // Always return success to prevent email enumeration
        if (!$user || !$user['is_active']) {
            // Fake delay to prevent timing attacks
            usleep(random_int(100000, 300000));

            Response::success(null, 'Si cette adresse email est associee a un compte, vous recevrez un lien de connexion.');
        }

        // Generate magic link
        $token = MagicLink::create($user['id'], self::getClientIp());

        // Send email
        $mailService = new MailService();
        $sent = $mailService->sendMagicLink($user['email'], $user['first_name'], $token);

        if (!$sent) {
            error_log("Failed to send magic link email to: {$email}");
        }

        Response::success(null, 'Si cette adresse email est associee a un compte, vous recevrez un lien de connexion.');
    }

    public function verifyMagicLink(string $token): void
    {
        if (empty($token)) {
            Response::error('Token manquant', 400);
        }

        $magicLinkData = MagicLink::verify($token);

        if (!$magicLinkData) {
            Response::error('Lien invalide ou expire. Veuillez demander un nouveau lien de connexion.', 401);
        }

        // Mark as used
        MagicLink::markAsUsed($magicLinkData['id']);

        $userId = $magicLinkData['user_id'];
        $user = User::findById($userId);

        if (!$user || !$user['is_active']) {
            Response::error('Compte desactive', 401);
        }

        // Update last login
        User::updateLastLogin($userId);

        // Generate tokens
        $accessToken = JWTService::generateAccessToken([
            'user_id' => $user['id'],
            'email' => $user['email']
        ]);

        $refreshToken = RefreshToken::create(
            $user['id'],
            self::getClientIp(),
            $_SERVER['HTTP_USER_AGENT'] ?? null
        );

        Response::success([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in' => JWTService::getAccessTokenExpiry(),
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name']
            ]
        ], 'Connexion reussie');
    }

    public function refresh(): void
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        if (empty($data['refresh_token'])) {
            Response::error('Refresh token manquant', 400);
        }

        $tokenData = RefreshToken::verify($data['refresh_token']);

        if (!$tokenData) {
            Response::unauthorized('Refresh token invalide ou expire');
        }

        $user = User::findById($tokenData['user_id']);

        if (!$user || !$user['is_active']) {
            RefreshToken::revoke($data['refresh_token']);
            Response::unauthorized('Compte desactive');
        }

        // Rotate refresh token
        $newRefreshToken = RefreshToken::rotate(
            $data['refresh_token'],
            self::getClientIp(),
            $_SERVER['HTTP_USER_AGENT'] ?? null
        );

        if (!$newRefreshToken) {
            Response::unauthorized('Impossible de renouveler le token');
        }

        // Generate new access token
        $accessToken = JWTService::generateAccessToken([
            'user_id' => $user['id'],
            'email' => $user['email']
        ]);

        Response::success([
            'access_token' => $accessToken,
            'refresh_token' => $newRefreshToken,
            'expires_in' => JWTService::getAccessTokenExpiry()
        ], 'Token rafraichi');
    }

    public function logout(): void
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        if (!empty($data['refresh_token'])) {
            RefreshToken::revoke($data['refresh_token']);
        }

        Response::success(null, 'Deconnexion reussie');
    }

    public function me(): void
    {
        AuthMiddleware::handle();
        $user = AuthMiddleware::getCurrentUser();

        if (!$user) {
            Response::unauthorized();
        }

        $fullUser = User::findById($user['user_id']);
        Response::success($fullUser);
    }
}
