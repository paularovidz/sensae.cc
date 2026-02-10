<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use App\Utils\UUID;
use PDO;

class Expense
{
    public static function findById(string $id): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('
            SELECT e.*, c.name as category_name, c.color as category_color, c.slug as category_slug
            FROM expenses e
            JOIN expense_categories c ON c.id = e.category_id
            WHERE e.id = :id
        ');
        $stmt->execute(['id' => $id]);
        $expense = $stmt->fetch();
        return $expense ?: null;
    }

    public static function getAll(array $filters = []): array
    {
        $db = Database::getInstance();
        $where = [];
        $params = [];

        if (!empty($filters['year'])) {
            $where[] = 'YEAR(e.expense_date) = :year';
            $params['year'] = $filters['year'];
        }

        if (!empty($filters['month'])) {
            $where[] = 'MONTH(e.expense_date) = :month';
            $params['month'] = $filters['month'];
        }

        if (!empty($filters['category_id'])) {
            $where[] = 'e.category_id = :category_id';
            $params['category_id'] = $filters['category_id'];
        }

        if (!empty($filters['vendor'])) {
            $where[] = 'e.vendor LIKE :vendor';
            $params['vendor'] = '%' . $filters['vendor'] . '%';
        }

        if (!empty($filters['from_date'])) {
            $where[] = 'e.expense_date >= :from_date';
            $params['from_date'] = $filters['from_date'];
        }

        if (!empty($filters['to_date'])) {
            $where[] = 'e.expense_date <= :to_date';
            $params['to_date'] = $filters['to_date'];
        }

        $sql = '
            SELECT e.*, c.name as category_name, c.color as category_color, c.slug as category_slug
            FROM expenses e
            JOIN expense_categories c ON c.id = e.category_id
        ';

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY e.expense_date DESC, e.created_at DESC';

        if (!empty($filters['limit'])) {
            $sql .= ' LIMIT ' . (int) $filters['limit'];
            if (!empty($filters['offset'])) {
                $sql .= ' OFFSET ' . (int) $filters['offset'];
            }
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function create(array $data): string
    {
        $db = Database::getInstance();
        $id = UUID::generate();

        $stmt = $db->prepare('
            INSERT INTO expenses (id, category_id, description, amount, expense_date, payment_method, vendor, invoice_number, notes, recurring_expense_id, bank_import_id, created_by)
            VALUES (:id, :category_id, :description, :amount, :expense_date, :payment_method, :vendor, :invoice_number, :notes, :recurring_expense_id, :bank_import_id, :created_by)
        ');

        $stmt->execute([
            'id' => $id,
            'category_id' => $data['category_id'],
            'description' => $data['description'],
            'amount' => $data['amount'],
            'expense_date' => $data['expense_date'],
            'payment_method' => $data['payment_method'] ?? 'transfer',
            'vendor' => $data['vendor'] ?? null,
            'invoice_number' => $data['invoice_number'] ?? null,
            'notes' => $data['notes'] ?? null,
            'recurring_expense_id' => $data['recurring_expense_id'] ?? null,
            'bank_import_id' => $data['bank_import_id'] ?? null,
            'created_by' => $data['created_by']
        ]);

        return $id;
    }

    public static function update(string $id, array $data): bool
    {
        $db = Database::getInstance();
        $sets = [];
        $params = ['id' => $id];

        foreach (['category_id', 'description', 'amount', 'expense_date', 'payment_method', 'vendor', 'invoice_number', 'notes'] as $field) {
            if (array_key_exists($field, $data)) {
                $sets[] = "{$field} = :{$field}";
                $params[$field] = $data[$field];
            }
        }

        if (empty($sets)) {
            return false;
        }

        $sql = "UPDATE expenses SET " . implode(', ', $sets) . " WHERE id = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    }

    public static function delete(string $id): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('DELETE FROM expenses WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public static function getTotalByMonth(int $year, int $month): float
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('
            SELECT COALESCE(SUM(amount), 0)
            FROM expenses
            WHERE YEAR(expense_date) = :year AND MONTH(expense_date) = :month
        ');
        $stmt->execute(['year' => $year, 'month' => $month]);
        return (float) $stmt->fetchColumn();
    }

    public static function getByCategory(int $year, int $month): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('
            SELECT
                c.id,
                c.name,
                c.color,
                c.slug,
                COALESCE(SUM(e.amount), 0) as total
            FROM expense_categories c
            LEFT JOIN expenses e ON e.category_id = c.id
                AND YEAR(e.expense_date) = :year
                AND MONTH(e.expense_date) = :month
            WHERE c.is_active = 1
            GROUP BY c.id
            HAVING total > 0
            ORDER BY total DESC
        ');
        $stmt->execute(['year' => $year, 'month' => $month]);
        return $stmt->fetchAll();
    }

    public static function getMonthlyTotals(int $year): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('
            SELECT
                MONTH(expense_date) as month,
                SUM(amount) as total
            FROM expenses
            WHERE YEAR(expense_date) = :year
            GROUP BY MONTH(expense_date)
            ORDER BY month
        ');
        $stmt->execute(['year' => $year]);

        $results = [];
        foreach ($stmt->fetchAll() as $row) {
            $results[(int) $row['month']] = (float) $row['total'];
        }
        return $results;
    }

    public static function getDailyTotals(int $year, int $month): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('
            SELECT
                DAY(expense_date) as day,
                SUM(amount) as total
            FROM expenses
            WHERE YEAR(expense_date) = :year AND MONTH(expense_date) = :month
            GROUP BY DAY(expense_date)
            ORDER BY day
        ');
        $stmt->execute(['year' => $year, 'month' => $month]);

        $results = [];
        foreach ($stmt->fetchAll() as $row) {
            $results[(int) $row['day']] = (float) $row['total'];
        }
        return $results;
    }

    public static function deleteByMonth(int $year, int $month, bool $keepRecurring = true): int
    {
        $db = Database::getInstance();
        $sql = 'DELETE FROM expenses WHERE YEAR(expense_date) = :year AND MONTH(expense_date) = :month';
        if ($keepRecurring) {
            $sql .= ' AND recurring_expense_id IS NULL';
        }
        $stmt = $db->prepare($sql);
        $stmt->execute(['year' => $year, 'month' => $month]);
        return $stmt->rowCount();
    }
}
