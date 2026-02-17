<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use App\Middleware\ApiKeyMiddleware;
use App\Models\PrepaidPack;
use App\Utils\Response;

/**
 * Controller for OPS application to fetch revenue data
 * All endpoints require API key authentication
 */
class OpsRevenueController
{
    public function __construct()
    {
        ApiKeyMiddleware::verify();
    }

    /**
     * Get revenue for a specific month
     * GET /ops/revenue?year=2024&month=1
     *
     * Note: Excludes prepaid sessions (prepaid_pack_id IS NOT NULL) as their revenue
     * is already counted when the pack was purchased
     */
    public function getMonthlyRevenue(): void
    {
        $year = (int) ($_GET['year'] ?? date('Y'));
        $month = (int) ($_GET['month'] ?? date('n'));

        $db = Database::getInstance();

        // Get total revenue from completed sessions (excluding prepaid sessions)
        $stmt = $db->prepare("
            SELECT
                COALESCE(SUM(CASE WHEN is_free_session = 0 AND price IS NOT NULL AND prepaid_pack_id IS NULL THEN price ELSE 0 END), 0) as total,
                COUNT(*) as count,
                SUM(CASE WHEN is_paid = 1 THEN 1 ELSE 0 END) as paid_count,
                COALESCE(SUM(CASE WHEN is_paid = 1 AND is_free_session = 0 AND price IS NOT NULL AND prepaid_pack_id IS NULL THEN price ELSE 0 END), 0) as paid_total,
                SUM(CASE WHEN prepaid_pack_id IS NOT NULL THEN 1 ELSE 0 END) as prepaid_count,
                -- Breakdown par type de séance
                COALESCE(SUM(CASE WHEN duration_type IN ('discovery', 'regular') AND is_free_session = 0 AND price IS NOT NULL AND prepaid_pack_id IS NULL THEN price ELSE 0 END), 0) as individual_total,
                SUM(CASE WHEN duration_type IN ('discovery', 'regular') THEN 1 ELSE 0 END) as individual_count,
                COALESCE(SUM(CASE WHEN duration_type IN ('half_day', 'full_day') AND is_free_session = 0 AND price IS NOT NULL AND prepaid_pack_id IS NULL THEN price ELSE 0 END), 0) as privatization_total,
                SUM(CASE WHEN duration_type IN ('half_day', 'full_day') THEN 1 ELSE 0 END) as privatization_count
            FROM sessions
            WHERE YEAR(session_date) = :year
            AND MONTH(session_date) = :month
            AND status IN ('completed', 'confirmed')
        ");
        $stmt->execute(['year' => $year, 'month' => $month]);
        $result = $stmt->fetch();

        Response::success([
            'year' => $year,
            'month' => $month,
            'total' => (float) $result['total'],
            'count' => (int) $result['count'],
            'paid_count' => (int) $result['paid_count'],
            'paid_total' => (float) $result['paid_total'],
            'prepaid_count' => (int) $result['prepaid_count'],
            'by_type' => [
                'individual' => [
                    'total' => (float) $result['individual_total'],
                    'count' => (int) $result['individual_count']
                ],
                'privatization' => [
                    'total' => (float) $result['privatization_total'],
                    'count' => (int) $result['privatization_count']
                ]
            ]
        ]);
    }

    /**
     * Get revenue for entire year by month
     * GET /ops/revenue/year/2024
     *
     * Note: Excludes prepaid sessions
     */
    public function getYearlyRevenue(string $year): void
    {
        $year = (int) $year;
        $db = Database::getInstance();

        $stmt = $db->prepare("
            SELECT
                MONTH(session_date) as month,
                COALESCE(SUM(CASE WHEN is_free_session = 0 AND price IS NOT NULL AND prepaid_pack_id IS NULL THEN price ELSE 0 END), 0) as total,
                COUNT(*) as count,
                SUM(CASE WHEN is_paid = 1 THEN 1 ELSE 0 END) as paid_count,
                COALESCE(SUM(CASE WHEN is_paid = 1 AND is_free_session = 0 AND price IS NOT NULL AND prepaid_pack_id IS NULL THEN price ELSE 0 END), 0) as paid_total,
                SUM(CASE WHEN prepaid_pack_id IS NOT NULL THEN 1 ELSE 0 END) as prepaid_count,
                -- Breakdown par type de séance
                COALESCE(SUM(CASE WHEN duration_type IN ('discovery', 'regular') AND is_free_session = 0 AND price IS NOT NULL AND prepaid_pack_id IS NULL THEN price ELSE 0 END), 0) as individual_total,
                SUM(CASE WHEN duration_type IN ('discovery', 'regular') THEN 1 ELSE 0 END) as individual_count,
                COALESCE(SUM(CASE WHEN duration_type IN ('half_day', 'full_day') AND is_free_session = 0 AND price IS NOT NULL AND prepaid_pack_id IS NULL THEN price ELSE 0 END), 0) as privatization_total,
                SUM(CASE WHEN duration_type IN ('half_day', 'full_day') THEN 1 ELSE 0 END) as privatization_count
            FROM sessions
            WHERE YEAR(session_date) = :year
            AND status IN ('completed', 'confirmed')
            GROUP BY MONTH(session_date)
            ORDER BY month
        ");
        $stmt->execute(['year' => $year]);

        $months = [];
        foreach ($stmt->fetchAll() as $row) {
            $months[(int) $row['month']] = [
                'total' => (float) $row['total'],
                'count' => (int) $row['count'],
                'paid_count' => (int) $row['paid_count'],
                'paid_total' => (float) $row['paid_total'],
                'prepaid_count' => (int) $row['prepaid_count'],
                'by_type' => [
                    'individual' => [
                        'total' => (float) $row['individual_total'],
                        'count' => (int) $row['individual_count']
                    ],
                    'privatization' => [
                        'total' => (float) $row['privatization_total'],
                        'count' => (int) $row['privatization_count']
                    ]
                ]
            ];
        }

        // Fill in missing months with zeros
        for ($m = 1; $m <= 12; $m++) {
            if (!isset($months[$m])) {
                $months[$m] = [
                    'total' => 0,
                    'count' => 0,
                    'paid_count' => 0,
                    'paid_total' => 0,
                    'prepaid_count' => 0,
                    'by_type' => [
                        'individual' => ['total' => 0, 'count' => 0],
                        'privatization' => ['total' => 0, 'count' => 0]
                    ]
                ];
            }
        }

        ksort($months);

        Response::success([
            'year' => $year,
            'months' => $months
        ]);
    }

    /**
     * Get daily revenue for a month
     * GET /ops/revenue/daily?year=2024&month=1
     *
     * Note: Excludes prepaid sessions
     */
    public function getDailyRevenue(): void
    {
        $year = (int) ($_GET['year'] ?? date('Y'));
        $month = (int) ($_GET['month'] ?? date('n'));

        $db = Database::getInstance();

        $stmt = $db->prepare("
            SELECT
                DAY(session_date) as day,
                COALESCE(SUM(CASE WHEN is_free_session = 0 AND price IS NOT NULL AND prepaid_pack_id IS NULL THEN price ELSE 0 END), 0) as total,
                COUNT(*) as count,
                -- Breakdown par type de séance
                COALESCE(SUM(CASE WHEN duration_type IN ('discovery', 'regular') AND is_free_session = 0 AND price IS NOT NULL AND prepaid_pack_id IS NULL THEN price ELSE 0 END), 0) as individual_total,
                SUM(CASE WHEN duration_type IN ('discovery', 'regular') THEN 1 ELSE 0 END) as individual_count,
                COALESCE(SUM(CASE WHEN duration_type IN ('half_day', 'full_day') AND is_free_session = 0 AND price IS NOT NULL AND prepaid_pack_id IS NULL THEN price ELSE 0 END), 0) as privatization_total,
                SUM(CASE WHEN duration_type IN ('half_day', 'full_day') THEN 1 ELSE 0 END) as privatization_count
            FROM sessions
            WHERE YEAR(session_date) = :year
            AND MONTH(session_date) = :month
            AND status IN ('completed', 'confirmed')
            GROUP BY DAY(session_date)
            ORDER BY day
        ");
        $stmt->execute(['year' => $year, 'month' => $month]);

        $days = [];
        foreach ($stmt->fetchAll() as $row) {
            $days[(int) $row['day']] = [
                'total' => (float) $row['total'],
                'individual' => (float) $row['individual_total'],
                'privatization' => (float) $row['privatization_total']
            ];
        }

        Response::success($days);
    }

    /**
     * Get session count for a month
     * GET /ops/sessions/count?year=2024&month=1
     */
    public function getSessionCount(): void
    {
        $year = (int) ($_GET['year'] ?? date('Y'));
        $month = (int) ($_GET['month'] ?? date('n'));

        $db = Database::getInstance();

        $stmt = $db->prepare("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                SUM(CASE WHEN status = 'no_show' THEN 1 ELSE 0 END) as no_show
            FROM sessions
            WHERE YEAR(session_date) = :year
            AND MONTH(session_date) = :month
        ");
        $stmt->execute(['year' => $year, 'month' => $month]);
        $result = $stmt->fetch();

        Response::success([
            'year' => $year,
            'month' => $month,
            'count' => (int) $result['total'],
            'by_status' => [
                'completed' => (int) $result['completed'],
                'confirmed' => (int) $result['confirmed'],
                'pending' => (int) $result['pending'],
                'cancelled' => (int) $result['cancelled'],
                'no_show' => (int) $result['no_show']
            ]
        ]);
    }

    // =========================================================================
    // PREPAID PACKS REVENUE
    // =========================================================================

    /**
     * Get prepaid pack revenue for a month
     * GET /ops/prepaid-revenue?year=2024&month=1
     */
    public function getPrepaidRevenue(): void
    {
        $year = (int) ($_GET['year'] ?? date('Y'));
        $month = (int) ($_GET['month'] ?? date('n'));

        $revenue = PrepaidPack::getRevenue($year, $month);

        Response::success([
            'year' => $year,
            'month' => $month,
            'total' => $revenue['total'],
            'count' => $revenue['count'],
            'pack_2' => $revenue['pack_2'],
            'pack_4' => $revenue['pack_4']
        ]);
    }

    /**
     * Get prepaid pack revenue by month for a year
     * GET /ops/prepaid-revenue/year/2024
     */
    public function getPrepaidYearlyRevenue(string $year): void
    {
        $year = (int) $year;

        $months = PrepaidPack::getRevenueByMonth($year);

        Response::success([
            'year' => $year,
            'months' => $months
        ]);
    }

    /**
     * Get daily prepaid revenue for a month
     * GET /ops/prepaid-revenue/daily?year=2024&month=1
     */
    public function getPrepaidDailyRevenue(): void
    {
        $year = (int) ($_GET['year'] ?? date('Y'));
        $month = (int) ($_GET['month'] ?? date('n'));

        $days = PrepaidPack::getDailyRevenue($year, $month);

        Response::success($days);
    }
}
