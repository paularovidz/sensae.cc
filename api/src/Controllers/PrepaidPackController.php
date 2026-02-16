<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\PrepaidPack;
use App\Models\User;
use App\Middleware\AuthMiddleware;
use App\Services\AuditService;
use App\Utils\Response;
use App\Utils\Validator;

/**
 * Contrôleur admin pour la gestion des packs prépayés
 */
class PrepaidPackController
{
    /**
     * GET /prepaid-packs
     * Liste tous les packs prépayés
     */
    public function index(): void
    {
        AuthMiddleware::requireAdmin();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = min(100, max(1, (int)($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;
        $search = isset($_GET['search']) && $_GET['search'] !== '' ? trim($_GET['search']) : null;

        $filters = [];
        if (isset($_GET['is_active'])) {
            $filters['is_active'] = $_GET['is_active'] === 'true' || $_GET['is_active'] === '1';
        }
        if (!empty($_GET['user_id'])) {
            $filters['user_id'] = $_GET['user_id'];
        }
        if (isset($_GET['has_credits'])) {
            $filters['has_credits'] = true;
        }
        if (isset($_GET['not_expired'])) {
            $filters['not_expired'] = true;
        }

        $packs = PrepaidPack::findAll($limit, $offset, $search, $filters);
        $total = PrepaidPack::count($search, $filters);

        Response::success([
            'packs' => $packs,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => (int)ceil($total / $limit)
            ],
            'labels' => PrepaidPack::LABELS
        ]);
    }

    /**
     * GET /prepaid-packs/types
     * Liste les types de packs disponibles avec leurs prix
     */
    public function types(): void
    {
        AuthMiddleware::handle();

        Response::success([
            'types' => PrepaidPack::getPackTypes(),
            'duration_types' => PrepaidPack::DURATION_TYPES,
            'labels' => PrepaidPack::LABELS
        ]);
    }

    /**
     * GET /prepaid-packs/{id}
     * Détail d'un pack avec historique d'utilisation
     */
    public function show(string $id): void
    {
        AuthMiddleware::requireAdmin();

        $pack = PrepaidPack::findById($id);

        if (!$pack) {
            Response::notFound('Pack prépayé non trouvé');
        }

        // Get usage history
        $usages = PrepaidPack::getUsages($id);

        Response::success([
            'pack' => $pack,
            'usages' => $usages,
            'labels' => PrepaidPack::LABELS
        ]);
    }

    /**
     * POST /prepaid-packs
     * Créer un pack prépayé pour un client
     */
    public function store(): void
    {
        AuthMiddleware::requireAdmin();
        $user = AuthMiddleware::getCurrentUser();
        $data = json_decode(file_get_contents('php://input'), true);

        $errors = [];

        // Validation
        if (empty($data['user_id'])) {
            $errors['user_id'] = 'L\'utilisateur est requis';
        } else {
            $targetUser = User::findById($data['user_id']);
            if (!$targetUser) {
                $errors['user_id'] = 'Utilisateur non trouvé';
            }
        }

        if (empty($data['pack_type'])) {
            $errors['pack_type'] = 'Le type de pack est requis';
        } elseif (!in_array($data['pack_type'], PrepaidPack::PACK_TYPES)) {
            $errors['pack_type'] = 'Type de pack invalide';
        }

        // Get pack details based on type
        $packDetails = null;
        if (!empty($data['pack_type']) && in_array($data['pack_type'], PrepaidPack::PACK_TYPES)) {
            $packDetails = PrepaidPack::getPackDetails($data['pack_type']);
        }

        // Sessions total - use pack default or custom
        $defaultSessions = $packDetails ? ($packDetails['sessions'] ?? 0) : 0;
        $sessionsTotal = (int)($data['sessions_total'] ?? $defaultSessions);
        if ($sessionsTotal <= 0) {
            $errors['sessions_total'] = 'Le nombre de séances doit être supérieur à 0';
        }

        // Price paid - use pack default or custom
        $defaultPrice = $packDetails ? ($packDetails['price'] ?? 0) : 0;
        $pricePaid = isset($data['price_paid'])
            ? (float)$data['price_paid']
            : $defaultPrice;
        if ($pricePaid < 0) {
            $errors['price_paid'] = 'Le prix ne peut pas être négatif';
        }

        // Duration type
        if (!empty($data['duration_type']) && !in_array($data['duration_type'], PrepaidPack::DURATION_TYPES)) {
            $errors['duration_type'] = 'Type de durée invalide';
        }

        if (!empty($errors)) {
            Response::validationError($errors);
        }

        $packId = PrepaidPack::create([
            'user_id' => $data['user_id'],
            'pack_type' => $data['pack_type'],
            'sessions_total' => $sessionsTotal,
            'price_paid' => $pricePaid,
            'duration_type' => $data['duration_type'] ?? PrepaidPack::DURATION_ANY,
            'expires_at' => $data['expires_at'] ?? null,
            'admin_notes' => $data['admin_notes'] ?? null,
            'created_by' => $user['id']
        ]);

        $pack = PrepaidPack::findById($packId);

        AuditService::log(
            $user['id'],
            'prepaid_pack.create',
            'prepaid_pack',
            $packId,
            null,
            [
                'user_id' => $data['user_id'],
                'pack_type' => $data['pack_type'],
                'sessions_total' => $sessionsTotal,
                'price_paid' => $pricePaid
            ]
        );

        Response::success([
            'pack' => $pack,
            'message' => 'Pack prépayé créé avec succès'
        ], 'Pack prépayé créé avec succès', 201);
    }

    /**
     * PUT /prepaid-packs/{id}
     * Modifier un pack (notes, expiration)
     */
    public function update(string $id): void
    {
        AuthMiddleware::requireAdmin();
        $user = AuthMiddleware::getCurrentUser();
        $data = json_decode(file_get_contents('php://input'), true);

        $pack = PrepaidPack::findById($id);
        if (!$pack) {
            Response::notFound('Pack prépayé non trouvé');
        }

        $errors = [];

        // Validate if sessions_total is being changed
        if (isset($data['sessions_total'])) {
            $newTotal = (int)$data['sessions_total'];
            if ($newTotal < $pack['sessions_used']) {
                $errors['sessions_total'] = 'Le nombre de séances ne peut pas être inférieur aux séances déjà utilisées';
            }
        }

        if (!empty($errors)) {
            Response::validationError($errors);
        }

        $oldValues = [
            'admin_notes' => $pack['admin_notes'],
            'expires_at' => $pack['expires_at'],
            'is_active' => $pack['is_active'],
            'sessions_total' => $pack['sessions_total']
        ];

        $updateData = [];
        if (array_key_exists('admin_notes', $data)) {
            $updateData['admin_notes'] = $data['admin_notes'];
        }
        if (array_key_exists('expires_at', $data)) {
            $updateData['expires_at'] = $data['expires_at'];
        }
        if (array_key_exists('is_active', $data)) {
            $updateData['is_active'] = $data['is_active'];
        }
        if (array_key_exists('sessions_total', $data)) {
            $updateData['sessions_total'] = (int)$data['sessions_total'];
        }

        if (empty($updateData)) {
            Response::error('Aucune donnée à mettre à jour', 400);
        }

        PrepaidPack::update($id, $updateData);

        $updatedPack = PrepaidPack::findById($id);

        AuditService::log(
            $user['id'],
            'prepaid_pack.update',
            'prepaid_pack',
            $id,
            $oldValues,
            $updateData
        );

        Response::success([
            'pack' => $updatedPack,
            'message' => 'Pack prépayé mis à jour'
        ]);
    }

    /**
     * DELETE /prepaid-packs/{id}
     * Désactiver un pack (soft delete)
     */
    public function destroy(string $id): void
    {
        AuthMiddleware::requireAdmin();
        $user = AuthMiddleware::getCurrentUser();

        $pack = PrepaidPack::findById($id);
        if (!$pack) {
            Response::notFound('Pack prépayé non trouvé');
        }

        // Only allow deactivation if pack has remaining credits
        // If fully used, allow hard delete
        if ($pack['sessions_used'] > 0) {
            // Soft delete - just deactivate
            PrepaidPack::deactivate($id);

            AuditService::log(
                $user['id'],
                'prepaid_pack.deactivate',
                'prepaid_pack',
                $id,
                ['is_active' => true],
                ['is_active' => false]
            );

            Response::success(['message' => 'Pack prépayé désactivé']);
        } else {
            // Hard delete - no usages
            PrepaidPack::delete($id);

            AuditService::log(
                $user['id'],
                'prepaid_pack.delete',
                'prepaid_pack',
                $id,
                $pack,
                null
            );

            Response::success(['message' => 'Pack prépayé supprimé']);
        }
    }

    /**
     * GET /prepaid-packs/{id}/usages
     * Historique d'utilisation d'un pack
     */
    public function usages(string $id): void
    {
        AuthMiddleware::requireAdmin();

        $pack = PrepaidPack::findById($id);
        if (!$pack) {
            Response::notFound('Pack prépayé non trouvé');
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = min(100, max(1, (int)($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;

        $usages = PrepaidPack::getUsages($id, $limit, $offset);

        Response::success([
            'usages' => $usages,
            'pack' => $pack
        ]);
    }

    /**
     * GET /users/{id}/prepaid-packs
     * Liste les packs d'un utilisateur
     */
    public function byUser(string $userId): void
    {
        AuthMiddleware::handle();
        $currentUser = AuthMiddleware::getCurrentUser();

        // Allow admin or the user themselves
        if ($currentUser['role'] !== 'admin' && $currentUser['id'] !== $userId) {
            Response::forbidden('Accès non autorisé');
        }

        $user = User::findById($userId);
        if (!$user) {
            Response::notFound('Utilisateur non trouvé');
        }

        $packs = PrepaidPack::findByUser($userId);
        $balance = PrepaidPack::getBalance($userId);

        Response::success([
            'packs' => $packs,
            'balance' => $balance,
            'labels' => PrepaidPack::LABELS
        ]);
    }
}
