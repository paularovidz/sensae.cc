<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use App\Utils\UUID;
use PDO;

class MonthlyForecast
{
    public static function findByMonth(int $year, int $month): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM monthly_forecasts WHERE year = :year AND month = :month');
        $stmt->execute(['year' => $year, 'month' => $month]);
        $forecast = $stmt->fetch();
        return $forecast ?: null;
    }

    public static function getByYear(int $year): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM monthly_forecasts WHERE year = :year ORDER BY month');
        $stmt->execute(['year' => $year]);

        $results = [];
        foreach ($stmt->fetchAll() as $row) {
            $results[(int) $row['month']] = $row;
        }
        return $results;
    }

    public static function upsert(int $year, int $month, array $data): string
    {
        $db = Database::getInstance();
        $existing = self::findByMonth($year, $month);

        if ($existing) {
            $stmt = $db->prepare('
                UPDATE monthly_forecasts
                SET revenue_forecast = :revenue_forecast,
                    expense_forecast = :expense_forecast,
                    notes = :notes
                WHERE year = :year AND month = :month
            ');
            $stmt->execute([
                'year' => $year,
                'month' => $month,
                'revenue_forecast' => $data['revenue_forecast'],
                'expense_forecast' => $data['expense_forecast'] ?? 0,
                'notes' => $data['notes'] ?? null
            ]);
            return $existing['id'];
        }

        $id = UUID::generate();
        $stmt = $db->prepare('
            INSERT INTO monthly_forecasts (id, year, month, revenue_forecast, expense_forecast, notes, created_by)
            VALUES (:id, :year, :month, :revenue_forecast, :expense_forecast, :notes, :created_by)
        ');
        $stmt->execute([
            'id' => $id,
            'year' => $year,
            'month' => $month,
            'revenue_forecast' => $data['revenue_forecast'],
            'expense_forecast' => $data['expense_forecast'] ?? 0,
            'notes' => $data['notes'] ?? null,
            'created_by' => $data['created_by']
        ]);

        return $id;
    }

    public static function bulkUpsert(int $year, array $forecasts, string $userId): void
    {
        foreach ($forecasts as $month => $data) {
            if (isset($data['revenue_forecast'])) {
                self::upsert($year, (int) $month, [
                    'revenue_forecast' => $data['revenue_forecast'],
                    'expense_forecast' => $data['expense_forecast'] ?? 0,
                    'notes' => $data['notes'] ?? null,
                    'created_by' => $userId
                ]);
            }
        }
    }

    public static function getAnnualTotal(int $year): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('
            SELECT
                COALESCE(SUM(revenue_forecast), 0) as revenue,
                COALESCE(SUM(expense_forecast), 0) as expenses
            FROM monthly_forecasts
            WHERE year = :year
        ');
        $stmt->execute(['year' => $year]);
        return $stmt->fetch();
    }
}
