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

        // Get prepaid packs revenue (pack sales)
        $prepaidRevenue = SenseaRevenueService::getPrepaidMonthlyRevenue($year, $month);

        // Get recurring expense total
        $recurringMonthly = RecurringExpense::getMonthlyTotal();

        // Calculate total revenue (sessions + prepaid packs)
        $sessionRevenue = $revenue['total'] ?? 0;
        $packRevenue = $prepaidRevenue['total'] ?? 0;
        $pack2Revenue = $prepaidRevenue['pack_2']['total'] ?? 0;
        $pack4Revenue = $prepaidRevenue['pack_4']['total'] ?? 0;
        $totalRevenue = $sessionRevenue + $packRevenue;

        // Calculate balance
        $balance = $totalRevenue - $expenseTotal;

        Response::success([
            'year' => $year,
            'month' => $month,
            'kpis' => [
                'revenue' => [
                    'total' => $totalRevenue,
                    'session_count' => $revenue['count'] ?? 0,
                    'sessions' => $sessionRevenue,
                    'prepaid_packs' => $packRevenue,
                    'prepaid_count' => $prepaidRevenue['count'] ?? 0,
                    'pack_2' => $pack2Revenue,
                    'pack_2_count' => $prepaidRevenue['pack_2']['count'] ?? 0,
                    'pack_4' => $pack4Revenue,
                    'pack_4_count' => $prepaidRevenue['pack_4']['count'] ?? 0
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

        // Get prepaid packs revenue for all months
        $yearlyPrepaid = SenseaRevenueService::getPrepaidYearlyRevenue($year);
        $prepaidByMonth = $yearlyPrepaid['months'] ?? [];

        // Build monthly data
        $months = [];
        $totalRevenue = 0;
        $totalExpenses = 0;
        $totalSessionRevenue = 0;
        $totalPrepaidRevenue = 0;
        $totalPack2Revenue = 0;
        $totalPack4Revenue = 0;

        for ($m = 1; $m <= 12; $m++) {
            $sessionRevenue = $revenueByMonth[$m]['total'] ?? 0;
            $prepaidRevenue = $prepaidByMonth[$m]['total'] ?? 0;
            $pack2Revenue = $prepaidByMonth[$m]['pack_2']['total'] ?? 0;
            $pack4Revenue = $prepaidByMonth[$m]['pack_4']['total'] ?? 0;
            $revenue = $sessionRevenue + $prepaidRevenue;
            $expenses = $expenseTotals[$m] ?? 0;

            $months[$m] = [
                'month' => $m,
                'revenue' => $revenue,
                'sessions' => $sessionRevenue,
                'prepaid_packs' => $prepaidRevenue,
                'pack_2' => $pack2Revenue,
                'pack_4' => $pack4Revenue,
                'expenses' => $expenses,
                'balance' => $revenue - $expenses
            ];

            $totalRevenue += $revenue;
            $totalSessionRevenue += $sessionRevenue;
            $totalPrepaidRevenue += $prepaidRevenue;
            $totalPack2Revenue += $pack2Revenue;
            $totalPack4Revenue += $pack4Revenue;
            $totalExpenses += $expenses;
        }

        Response::success([
            'year' => $year,
            'months' => $months,
            'totals' => [
                'revenue' => $totalRevenue,
                'sessions' => $totalSessionRevenue,
                'prepaid_packs' => $totalPrepaidRevenue,
                'pack_2' => $totalPack2Revenue,
                'pack_4' => $totalPack4Revenue,
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

        // Get daily prepaid revenue from Sensea
        $dailyPrepaid = SenseaRevenueService::getPrepaidDailyRevenue($year, $month);

        // Get number of days in month
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        // Build daily data
        $days = [];
        $totalSessionRevenue = 0;
        $totalPrepaidRevenue = 0;
        $totalPack2Revenue = 0;
        $totalPack4Revenue = 0;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $sessionRevenue = $dailyRevenue[$day] ?? 0;
            $prepaidData = $dailyPrepaid[$day] ?? ['total' => 0, 'pack_2' => ['total' => 0], 'pack_4' => ['total' => 0]];
            $prepaidRevenue = is_array($prepaidData) ? ($prepaidData['total'] ?? 0) : $prepaidData;
            $pack2Revenue = is_array($prepaidData) ? ($prepaidData['pack_2']['total'] ?? 0) : 0;
            $pack4Revenue = is_array($prepaidData) ? ($prepaidData['pack_4']['total'] ?? 0) : 0;
            $revenue = $sessionRevenue + $prepaidRevenue;
            $expenses = $dailyExpenses[$day] ?? 0;

            $days[$day] = [
                'day' => $day,
                'revenue' => $revenue,
                'sessions' => $sessionRevenue,
                'prepaid_packs' => $prepaidRevenue,
                'pack_2' => $pack2Revenue,
                'pack_4' => $pack4Revenue,
                'expenses' => $expenses,
                'balance' => $revenue - $expenses
            ];

            $totalSessionRevenue += $sessionRevenue;
            $totalPrepaidRevenue += $prepaidRevenue;
            $totalPack2Revenue += $pack2Revenue;
            $totalPack4Revenue += $pack4Revenue;
        }

        // Calculate totals
        $totalRevenue = $totalSessionRevenue + $totalPrepaidRevenue;
        $totalExpenses = array_sum(array_column($days, 'expenses'));

        Response::success([
            'year' => $year,
            'month' => $month,
            'days' => $days,
            'totals' => [
                'revenue' => $totalRevenue,
                'sessions' => $totalSessionRevenue,
                'prepaid_packs' => $totalPrepaidRevenue,
                'pack_2' => $totalPack2Revenue,
                'pack_4' => $totalPack4Revenue,
                'expenses' => $totalExpenses,
                'balance' => $totalRevenue - $totalExpenses
            ]
        ]);
    }
}
