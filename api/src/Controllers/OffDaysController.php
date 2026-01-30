<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Models\OffDay;
use App\Utils\Response;
use App\Utils\UUID;

class OffDaysController
{
    /**
     * GET /off-days - List all off days (admin only)
     */
    public function index(): void
    {
        AuthMiddleware::requireAdmin();

        $offDays = OffDay::getAll();

        Response::success([
            'off_days' => $offDays,
            'count' => count($offDays)
        ]);
    }

    /**
     * POST /off-days - Create a new off day (admin only)
     */
    public function store(): void
    {
        AuthMiddleware::requireAdmin();

        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $userId = AuthMiddleware::getCurrentUserId();

        // Validate required fields
        if (empty($data['date'])) {
            Response::validationError(['date' => 'La date est requise']);
            return;
        }

        // Validate date format
        $date = \DateTime::createFromFormat('Y-m-d', $data['date']);
        if (!$date) {
            Response::validationError(['date' => 'Format de date invalide (attendu: YYYY-MM-DD)']);
            return;
        }

        // Check if date already exists
        if (OffDay::dateExists($data['date'])) {
            Response::validationError(['date' => 'Cette date est déjà marquée comme jour off']);
            return;
        }

        $id = OffDay::create([
            'date' => $data['date'],
            'reason' => $data['reason'] ?? null,
            'created_by' => $userId
        ]);

        if (!$id) {
            Response::error('Impossible de créer le jour off', 500);
            return;
        }

        $offDay = OffDay::getById($id);

        Response::success([
            'message' => 'Jour off créé',
            'off_day' => $offDay
        ], 201);
    }

    /**
     * DELETE /off-days/{id} - Delete an off day (admin only)
     */
    public function destroy(string $id): void
    {
        AuthMiddleware::requireAdmin();

        if (!UUID::isValid($id)) {
            Response::validationError(['id' => 'ID invalide']);
            return;
        }

        $offDay = OffDay::getById($id);

        if (!$offDay) {
            Response::notFound('Jour off non trouvé');
            return;
        }

        OffDay::delete($id);

        Response::success([
            'message' => 'Jour off supprimé'
        ]);
    }
}
