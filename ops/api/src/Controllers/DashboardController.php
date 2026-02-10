<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Expense;
use App\Models\RecurringExpense;
use App\Services\SenseaRevenueService;
use App\Middleware\AuthMiddleware;
use App\Utils\Response;

class DashboardController
{
    public function __construct()
    {
        AuthMiddleware::handle();
    }

    public function index(): void
    {
        $year = (int) ($_GET['year'] ?? date('Y'));
        $month = (int) ($_GET['month'] ?? date('n'));

        // Get expenses
        $expenseTotal = Expense::getTotalByMonth($year, $month);
        $expensesByCategory = Expense::getByCategory($year, $month);

        // Get revenue from Sensea API (includes future confirmed sessions)
        $revenue = SenseaRevenueService::getMonthlyRevenue($year, $month);

        // Get recurring expense total
        $recurringMonthly = RecurringExpense::getMonthlyTotal();

        // Calculate balance
        $balance = ($revenue['total'] ?? 0) - $expenseTotal;

        Response::success([
            'year' => $year,
            'month' => $month,
            'kpis' => [
                'revenue' => [
                    'total' => $revenue['total'] ?? 0,
                    'session_count' => $revenue['count'] ?? 0
                ],
                'expenses' => [
                    'total' => $expenseTotal,
                    'recurring_monthly' => $recurringMonthly
                ],
                'balance' => $balance
            ],
            'expenses_by_category' => $expensesByCategory
        ]);
    }

    public function year(): void
    {
        $year = (int) ($_GET['year'] ?? date('Y'));

        // Get monthly expense totals
        $expenseTotals = Expense::getMonthlyTotals($year);

        // Get revenue from Sensea for all months
        $yearlyRevenue = SenseaRevenueService::getYearlyRevenue($year);
        $revenueByMonth = $yearlyRevenue['months'] ?? [];

        // Build monthly data
        $months = [];
        $totalRevenue = 0;
        $totalExpenses = 0;

        for ($m = 1; $m <= 12; $m++) {
            $revenue = $revenueByMonth[$m]['total'] ?? 0;
            $expenses = $expenseTotals[$m] ?? 0;

            $months[$m] = [
                'month' => $m,
                'revenue' => $revenue,
                'expenses' => $expenses,
                'balance' => $revenue - $expenses
            ];

            $totalRevenue += $revenue;
            $totalExpenses += $expenses;
        }

        Response::success([
            'year' => $year,
            'months' => $months,
            'totals' => [
                'revenue' => $totalRevenue,
                'expenses' => $totalExpenses,
                'balance' => $totalRevenue - $totalExpenses
            ]
        ]);
    }

    public function health(): void
    {
        $senseaAvailable = SenseaRevenueService::ping();

        Response::success([
            'status' => 'ok',
            'sensea_api' => $senseaAvailable ? 'connected' : 'disconnected'
        ]);
    }

    /**
     * Get daily breakdown for a specific month
     */
    public function daily(): void
    {
        $year = (int) ($_GET['year'] ?? date('Y'));
        $month = (int) ($_GET['month'] ?? date('n'));

        // Get daily expenses
        $dailyExpenses = Expense::getDailyTotals($year, $month);

        // Get daily revenue from Sensea
        $dailyRevenue = SenseaRevenueService::getDailyRevenue($year, $month);

        // Get number of days in month
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        // Build daily data
        $days = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $revenue = $dailyRevenue[$day] ?? 0;
            $expenses = $dailyExpenses[$day] ?? 0;
            $days[$day] = [
                'day' => $day,
                'revenue' => $revenue,
                'expenses' => $expenses,
                'balance' => $revenue - $expenses
            ];
        }

        // Calculate totals
        $totalRevenue = array_sum(array_column($days, 'revenue'));
        $totalExpenses = array_sum(array_column($days, 'expenses'));

        Response::success([
            'year' => $year,
            'month' => $month,
            'days' => $days,
            'totals' => [
                'revenue' => $totalRevenue,
                'expenses' => $totalExpenses,
                'balance' => $totalRevenue - $totalExpenses
            ]
        ]);
    }
}
