<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\RecurringExpense;
use App\Models\ExpenseCategory;
use App\Models\VendorMapping;
use App\Middleware\AuthMiddleware;
use App\Utils\Response;
use App\Utils\Validator;

class RecurringExpenseController
{
    public function __construct()
    {
        AuthMiddleware::handle();
    }

    public function index(): void
    {
        $activeOnly = !isset($_GET['all']);
        $expenses = RecurringExpense::getAll($activeOnly);
        Response::success($expenses);
    }

    public function show(string $id): void
    {
        $expense = RecurringExpense::findById($id);

        if (!$expense) {
            Response::notFound('Dépense récurrente non trouvée');
        }

        Response::success($expense);
    }

    public function store(): void
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        $validator = Validator::make($data)
            ->required('category_id')
            ->uuid('category_id')
            ->required('description')
            ->min('description', 2)
            ->required('amount')
            ->numeric('amount')
            ->positive('amount')
            ->required('start_date')
            ->date('start_date');

        if (isset($data['frequency'])) {
            $validator->in('frequency', ['monthly', 'quarterly', 'yearly']);
        }
        if (isset($data['day_of_month'])) {
            $validator->numeric('day_of_month')->min('day_of_month', 1)->max('day_of_month', 31);
        }
        if (isset($data['end_date'])) {
            $validator->date('end_date');
        }

        if ($validator->fails()) {
            Response::validationError($validator->errors());
        }

        // Verify category exists
        if (!ExpenseCategory::findById($data['category_id'])) {
            Response::error('Catégorie non trouvée', 404);
        }

        $userId = AuthMiddleware::getUserId();

        $id = RecurringExpense::create([
            'category_id' => $data['category_id'],
            'description' => $data['description'],
            'amount' => (float) $data['amount'],
            'frequency' => $data['frequency'] ?? 'monthly',
            'day_of_month' => (int) ($data['day_of_month'] ?? 1),
            'vendor' => $data['vendor'] ?? null,
            'notes' => $data['notes'] ?? null,
            'is_active' => $data['is_active'] ?? 1,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'] ?? null,
            'created_by' => $userId
        ]);

        // Save vendor mapping if requested
        if (!empty($data['vendor']) && !empty($data['save_vendor_mapping'])) {
            VendorMapping::createFromExpense(
                $data['vendor'],
                $data['category_id'],
                $userId
            );
        }

        $expense = RecurringExpense::findById($id);
        Response::success($expense, 'Dépense récurrente créée', 201);
    }

    public function update(string $id): void
    {
        $expense = RecurringExpense::findById($id);

        if (!$expense) {
            Response::notFound('Dépense récurrente non trouvée');
        }

        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        $validator = Validator::make($data);
        if (isset($data['category_id'])) {
            $validator->uuid('category_id');
        }
        if (isset($data['amount'])) {
            $validator->numeric('amount')->positive('amount');
        }
        if (isset($data['frequency'])) {
            $validator->in('frequency', ['monthly', 'quarterly', 'yearly']);
        }
        if (isset($data['day_of_month'])) {
            $validator->numeric('day_of_month');
        }
        if (isset($data['start_date'])) {
            $validator->date('start_date');
        }
        if (isset($data['end_date']) && $data['end_date'] !== null) {
            $validator->date('end_date');
        }

        if ($validator->fails()) {
            Response::validationError($validator->errors());
        }

        // Verify category exists if changing
        if (isset($data['category_id']) && !ExpenseCategory::findById($data['category_id'])) {
            Response::error('Catégorie non trouvée', 404);
        }

        RecurringExpense::update($id, $data);

        // Save vendor mapping if requested
        $userId = AuthMiddleware::getUserId();
        if (!empty($data['vendor']) && !empty($data['save_vendor_mapping'])) {
            $categoryId = $data['category_id'] ?? $expense['category_id'];
            VendorMapping::createFromExpense(
                $data['vendor'],
                $categoryId,
                $userId
            );
        }

        $updated = RecurringExpense::findById($id);
        Response::success($updated, 'Dépense récurrente mise à jour');
    }

    public function destroy(string $id): void
    {
        $expense = RecurringExpense::findById($id);

        if (!$expense) {
            Response::notFound('Dépense récurrente non trouvée');
        }

        RecurringExpense::delete($id);
        Response::success(null, 'Dépense récurrente supprimée');
    }

    public function generate(): void
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        $year = (int) ($data['year'] ?? date('Y'));
        $month = (int) ($data['month'] ?? date('n'));

        $userId = AuthMiddleware::getUserId();
        $generated = RecurringExpense::generateForMonth($year, $month, $userId);

        Response::success([
            'year' => $year,
            'month' => $month,
            'generated_count' => count($generated),
            'expense_ids' => $generated
        ], count($generated) . ' dépenses générées');
    }

    public function generateYear(): void
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        $year = (int) ($data['year'] ?? date('Y'));
        $userId = AuthMiddleware::getUserId();

        $allGenerated = [];
        $byMonth = [];

        for ($month = 1; $month <= 12; $month++) {
            $generated = RecurringExpense::generateForMonth($year, $month, $userId);
            $allGenerated = array_merge($allGenerated, $generated);
            if (count($generated) > 0) {
                $byMonth[$month] = count($generated);
            }
        }

        Response::success([
            'year' => $year,
            'generated_count' => count($allGenerated),
            'by_month' => $byMonth,
            'expense_ids' => $allGenerated
        ], count($allGenerated) . ' dépenses générées pour ' . $year);
    }

    public function getMonthlyTotal(): void
    {
        $total = RecurringExpense::getMonthlyTotal();
        Response::success(['total' => $total]);
    }
}
