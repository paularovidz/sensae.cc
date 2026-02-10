<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\MonthlyForecast;
use App\Middleware\AuthMiddleware;
use App\Utils\Response;
use App\Utils\Validator;

class ForecastController
{
    public function __construct()
    {
        AuthMiddleware::handle();
    }

    public function index(): void
    {
        $year = (int) ($_GET['year'] ?? date('Y'));
        $forecasts = MonthlyForecast::getByYear($year);
        Response::success($forecasts);
    }

    public function show(string $year, string $month): void
    {
        $forecast = MonthlyForecast::findByMonth((int) $year, (int) $month);

        if (!$forecast) {
            Response::success([
                'year' => (int) $year,
                'month' => (int) $month,
                'revenue_forecast' => 0,
                'expense_forecast' => 0
            ]);
            return;
        }

        Response::success($forecast);
    }

    public function store(): void
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        $validator = Validator::make($data)
            ->required('year')
            ->numeric('year')
            ->required('forecasts');

        if ($validator->fails()) {
            Response::validationError($validator->errors());
        }

        if (!is_array($data['forecasts'])) {
            Response::error('forecasts doit être un tableau', 422);
        }

        $userId = AuthMiddleware::getUserId();
        MonthlyForecast::bulkUpsert((int) $data['year'], $data['forecasts'], $userId);

        $forecasts = MonthlyForecast::getByYear((int) $data['year']);
        Response::success($forecasts, 'Prévisions enregistrées');
    }

    public function update(string $year, string $month): void
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        $validator = Validator::make($data);
        if (isset($data['revenue_forecast'])) {
            $validator->numeric('revenue_forecast');
        }
        if (isset($data['expense_forecast'])) {
            $validator->numeric('expense_forecast');
        }

        if ($validator->fails()) {
            Response::validationError($validator->errors());
        }

        $userId = AuthMiddleware::getUserId();

        MonthlyForecast::upsert((int) $year, (int) $month, [
            'revenue_forecast' => $data['revenue_forecast'] ?? 0,
            'expense_forecast' => $data['expense_forecast'] ?? 0,
            'notes' => $data['notes'] ?? null,
            'created_by' => $userId
        ]);

        $forecast = MonthlyForecast::findByMonth((int) $year, (int) $month);
        Response::success($forecast, 'Prévision mise à jour');
    }

    public function getAnnualTotal(): void
    {
        $year = (int) ($_GET['year'] ?? date('Y'));
        $totals = MonthlyForecast::getAnnualTotal($year);
        Response::success($totals);
    }
}
