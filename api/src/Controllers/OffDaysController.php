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
     * GET /off-days - List upcoming off days (admin only)
     */
    public function index(): void
    {
        AuthMiddleware::requireAdmin();

        // Only get upcoming off days (end_date >= today)
        $offDays = OffDay::getUpcoming();

        Response::success([
            'off_days' => $offDays,
            'count' => count($offDays)
        ]);
    }

    /**
     * POST /off-days - Create a new off day/period (admin only)
     */
    public function store(): void
    {
        AuthMiddleware::requireAdmin();

        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $userId = AuthMiddleware::getCurrentUserId();

        // Validate required fields
        if (empty($data['start_date'])) {
            Response::validationError(['start_date' => 'La date de début est requise']);
            return;
        }

        // Validate start_date format
        $startDate = \DateTime::createFromFormat('Y-m-d', $data['start_date']);
        if (!$startDate) {
            Response::validationError(['start_date' => 'Format de date invalide (attendu: YYYY-MM-DD)']);
            return;
        }

        // Validate end_date if provided
        $endDate = $startDate;
        if (!empty($data['end_date'])) {
            $endDate = \DateTime::createFromFormat('Y-m-d', $data['end_date']);
            if (!$endDate) {
                Response::validationError(['end_date' => 'Format de date invalide (attendu: YYYY-MM-DD)']);
                return;
            }

            // Ensure end_date is not before start_date
            if ($endDate < $startDate) {
                Response::validationError(['end_date' => 'La date de fin doit être postérieure ou égale à la date de début']);
                return;
            }
        }

        $id = OffDay::create([
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'] ?? $data['start_date'],
            'reason' => $data['reason'] ?? null,
            'created_by' => $userId
        ]);

        if (!$id) {
            Response::error('Impossible de créer la période off', 500);
            return;
        }

        $offDay = OffDay::getById($id);

        Response::success([
            'off_day' => $offDay
        ], 'Période off créée', 201);
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
            Response::notFound('Période off non trouvée');
            return;
        }

        OffDay::delete($id);

        Response::success([
            'message' => 'Période off supprimée'
        ]);
    }
}
