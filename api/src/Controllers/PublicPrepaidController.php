<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\PrepaidPack;
use App\Models\Session;
use App\Models\User;
use App\Utils\Response;
use App\Utils\Validator;

/**
 * Contrôleur pour les endpoints publics des packs prépayés (sans authentification)
 * Utilisé par le wizard de réservation pour afficher le solde de crédits
 */
class PublicPrepaidController
{
    /**
     * GET /public/prepaid/balance
     * Récupère le solde de crédits prépayés pour un email
     *
     * Query params:
     * - email: Email du client
     * - duration_type: (optionnel) Type de séance pour filtrer les packs compatibles
     */
    public function balance(): void
    {
        $email = isset($_GET['email']) ? strtolower(trim($_GET['email'])) : null;
        $durationType = $_GET['duration_type'] ?? null;

        if (empty($email)) {
            Response::validationError(['email' => 'L\'email est requis']);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::validationError(['email' => 'Email invalide']);
        }

        if ($durationType !== null && !in_array($durationType, Session::TYPES)) {
            Response::validationError(['duration_type' => 'Type de séance invalide']);
        }

        // Find user by email
        $user = User::findByEmail($email);

        if (!$user) {
            // No user found = no credits
            Response::success([
                'has_credits' => false,
                'total_credits' => 0,
                'packs_count' => 0,
                'packs' => []
            ]);
            return;
        }

        // Get balance
        $balance = PrepaidPack::getBalance($user['id'], $durationType);

        // Format packs for public response (limited info)
        $publicPacks = array_map(function ($pack) {
            return [
                'id' => $pack['id'],
                'pack_type' => $pack['pack_type'],
                'sessions_remaining' => $pack['sessions_total'] - $pack['sessions_used'],
                'duration_type' => $pack['duration_type'],
                'expires_at' => $pack['expires_at']
            ];
        }, $balance['packs']);

        Response::success([
            'has_credits' => $balance['total_credits'] > 0,
            'total_credits' => $balance['total_credits'],
            'packs_count' => $balance['packs_count'],
            'packs' => $publicPacks
        ]);
    }

    /**
     * POST /public/prepaid/check
     * Vérifie si un client peut utiliser une séance prépayée pour sa réservation
     * Retourne les infos de pricing (0€ si crédit disponible)
     *
     * Body:
     * - email: Email du client
     * - duration_type: Type de séance (regular/discovery)
     */
    public function check(): void
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        $validator = new Validator($data);
        $validator
            ->required('email')
            ->email('email')
            ->required('duration_type')
            ->inArray('duration_type', Session::TYPES);

        $errors = $validator->validate();

        if (!empty($errors)) {
            Response::validationError($errors);
        }

        $email = strtolower(trim($data['email']));
        $durationType = $data['duration_type'];

        // Find user
        $user = User::findByEmail($email);

        if (!$user) {
            Response::success([
                'can_use_prepaid' => false,
                'total_credits' => 0,
                'credits_available' => 0,
                'message' => null
            ]);
            return;
        }

        // Get best pack for this session type
        $pack = PrepaidPack::getBestPackForSession($user['id'], $durationType);

        if (!$pack) {
            Response::success([
                'can_use_prepaid' => false,
                'total_credits' => 0,
                'credits_available' => 0,
                'message' => null
            ]);
            return;
        }

        // Get total balance for info
        $balance = PrepaidPack::getBalance($user['id'], $durationType);

        Response::success([
            'can_use_prepaid' => true,
            'total_credits' => $balance['total_credits'],
            'credits_available' => $balance['total_credits'],
            'pack_id' => $pack['id'],
            'message' => sprintf(
                'Vous avez %d séance%s prépayée%s disponible%s',
                $balance['total_credits'],
                $balance['total_credits'] > 1 ? 's' : '',
                $balance['total_credits'] > 1 ? 's' : '',
                $balance['total_credits'] > 1 ? 's' : ''
            )
        ]);
    }
}
