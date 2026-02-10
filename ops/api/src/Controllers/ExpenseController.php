<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\VendorMapping;
use App\Middleware\AuthMiddleware;
use App\Utils\Response;
use App\Utils\Validator;

class ExpenseController
{
    public function __construct()
    {
        AuthMiddleware::handle();
    }

    public function index(): void
    {
        $filters = [
            'year' => isset($_GET['year']) ? (int) $_GET['year'] : null,
            'month' => isset($_GET['month']) ? (int) $_GET['month'] : null,
            'category_id' => $_GET['category_id'] ?? null,
            'vendor' => $_GET['vendor'] ?? null,
            'from_date' => $_GET['from_date'] ?? null,
            'to_date' => $_GET['to_date'] ?? null,
            'limit' => isset($_GET['limit']) ? (int) $_GET['limit'] : null,
            'offset' => isset($_GET['offset']) ? (int) $_GET['offset'] : null
        ];

        $expenses = Expense::getAll(array_filter($filters));
        Response::success($expenses);
    }

    public function show(string $id): void
    {
        $expense = Expense::findById($id);

        if (!$expense) {
            Response::notFound('Dépense non trouvée');
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
            ->required('expense_date')
            ->date('expense_date');

        if (isset($data['payment_method'])) {
            $validator->in('payment_method', ['cash', 'card', 'transfer', 'check', 'direct_debit']);
        }

        if ($validator->fails()) {
            Response::validationError($validator->errors());
        }

        // Verify category exists
        if (!ExpenseCategory::findById($data['category_id'])) {
            Response::error('Catégorie non trouvée', 404);
        }

        $userId = AuthMiddleware::getUserId();

        $id = Expense::create([
            'category_id' => $data['category_id'],
            'description' => $data['description'],
            'amount' => (float) $data['amount'],
            'expense_date' => $data['expense_date'],
            'payment_method' => $data['payment_method'] ?? 'transfer',
            'vendor' => $data['vendor'] ?? null,
            'invoice_number' => $data['invoice_number'] ?? null,
            'notes' => $data['notes'] ?? null,
            'created_by' => $userId
        ]);

        // If vendor is provided and save_vendor_mapping is true, create the mapping
        if (!empty($data['vendor']) && !empty($data['save_vendor_mapping'])) {
            VendorMapping::createFromExpense(
                $data['vendor'],
                $data['category_id'],
                $userId,
                $data['vendor_display_name'] ?? null
            );
        }

        $expense = Expense::findById($id);
        Response::success($expense, 'Dépense créée', 201);
    }

    public function update(string $id): void
    {
        $expense = Expense::findById($id);

        if (!$expense) {
            Response::notFound('Dépense non trouvée');
        }

        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        $validator = Validator::make($data);
        if (isset($data['category_id'])) {
            $validator->uuid('category_id');
        }
        if (isset($data['amount'])) {
            $validator->numeric('amount')->positive('amount');
        }
        if (isset($data['expense_date'])) {
            $validator->date('expense_date');
        }
        if (isset($data['payment_method'])) {
            $validator->in('payment_method', ['cash', 'card', 'transfer', 'check', 'direct_debit']);
        }

        if ($validator->fails()) {
            Response::validationError($validator->errors());
        }

        // Verify category exists if changing
        if (isset($data['category_id']) && !ExpenseCategory::findById($data['category_id'])) {
            Response::error('Catégorie non trouvée', 404);
        }

        Expense::update($id, $data);

        // If vendor is provided and save_vendor_mapping is true, create/update the mapping
        $userId = AuthMiddleware::getUserId();
        if (!empty($data['vendor']) && !empty($data['save_vendor_mapping'])) {
            $categoryId = $data['category_id'] ?? $expense['category_id'];
            VendorMapping::createFromExpense(
                $data['vendor'],
                $categoryId,
                $userId,
                $data['vendor_display_name'] ?? null
            );
        }

        $updated = Expense::findById($id);
        Response::success($updated, 'Dépense mise à jour');
    }

    public function destroy(string $id): void
    {
        $expense = Expense::findById($id);

        if (!$expense) {
            Response::notFound('Dépense non trouvée');
        }

        Expense::delete($id);
        Response::success(null, 'Dépense supprimée');
    }

    public function byCategory(): void
    {
        $year = (int) ($_GET['year'] ?? date('Y'));
        $month = (int) ($_GET['month'] ?? date('n'));

        $data = Expense::getByCategory($year, $month);
        Response::success($data);
    }

    public function monthlyTotals(): void
    {
        $year = (int) ($_GET['year'] ?? date('Y'));
        $data = Expense::getMonthlyTotals($year);
        Response::success($data);
    }
}
