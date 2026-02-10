<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\MonthState;
use App\Models\Expense;
use App\Middleware\AuthMiddleware;
use App\Utils\Response;

class MonthStateController
{
    public function __construct()
    {
        AuthMiddleware::handle();
    }

    public function index(): void
    {
        $year = (int) ($_GET['year'] ?? date('Y'));
        $states = MonthState::getByYear($year);
        Response::success($states);
    }

    public function show(string $year, string $month): void
    {
        $state = MonthState::findByMonth((int) $year, (int) $month);
        Response::success($state ?? [
            'year' => (int) $year,
            'month' => (int) $month,
            'state' => MonthState::STATE_ESTIMATED
        ]);
    }

    public function setActual(string $year, string $month): void
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $userId = AuthMiddleware::getUserId();

        MonthState::setActual((int) $year, (int) $month, $userId, $data['notes'] ?? null);

        $state = MonthState::findByMonth((int) $year, (int) $month);
        Response::success($state, 'Mois passe en mode reel');
    }

    public function setEstimated(string $year, string $month): void
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $userId = AuthMiddleware::getUserId();

        MonthState::setEstimated((int) $year, (int) $month, $userId, $data['notes'] ?? null);

        $state = MonthState::findByMonth((int) $year, (int) $month);
        Response::success($state, 'Mois passe en mode estime');
    }

    public function clearMonth(string $year, string $month): void
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $keepRecurring = $data['keep_recurring'] ?? true;

        $deleted = Expense::deleteByMonth((int) $year, (int) $month, $keepRecurring);

        Response::success([
            'deleted_count' => $deleted,
            'year' => (int) $year,
            'month' => (int) $month
        ], "{$deleted} depenses supprimees");
    }
}
