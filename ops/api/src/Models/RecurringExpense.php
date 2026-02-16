<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use App\Utils\UUID;
use PDO;

class RecurringExpense
{
    public static function findById(string $id): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('
            SELECT r.*, c.name as category_name, c.color as category_color, c.slug as category_slug
            FROM recurring_expenses r
            JOIN expense_categories c ON c.id = r.category_id
            WHERE r.id = :id
        ');
        $stmt->execute(['id' => $id]);
        $expense = $stmt->fetch();
        return $expense ?: null;
    }

    public static function getAll(bool $activeOnly = true): array
    {
        $db = Database::getInstance();
        $sql = '
            SELECT r.*, c.name as category_name, c.color as category_color, c.slug as category_slug
            FROM recurring_expenses r
            JOIN expense_categories c ON c.id = r.category_id
        ';
        if ($activeOnly) {
            $sql .= ' WHERE r.is_active = 1';
        }
        $sql .= ' ORDER BY r.day_of_month ASC, r.description ASC';
        $stmt = $db->query($sql);
        return $stmt->fetchAll();
    }

    public static function getActiveForMonth(int $year, int $month): array
    {
        $db = Database::getInstance();
        $targetDate = sprintf('%04d-%02d-01', $year, $month);

        $stmt = $db->prepare("
            SELECT r.*, c.name as category_name, c.color as category_color, c.slug as category_slug
            FROM recurring_expenses r
            JOIN expense_categories c ON c.id = r.category_id
            WHERE r.is_active = 1
            AND r.start_date <= :start_check
            AND (r.end_date IS NULL OR r.end_date >= :end_check)
            ORDER BY r.day_of_month ASC
        ");
        $stmt->execute(['start_check' => $targetDate, 'end_check' => $targetDate]);
        return $stmt->fetchAll();
    }

    public static function create(array $data): string
    {
        $db = Database::getInstance();
        $id = UUID::generate();

        $stmt = $db->prepare('
            INSERT INTO recurring_expenses (id, category_id, description, amount, frequency, day_of_month, vendor, notes, is_active, start_date, end_date, created_by)
            VALUES (:id, :category_id, :description, :amount, :frequency, :day_of_month, :vendor, :notes, :is_active, :start_date, :end_date, :created_by)
        ');

        $stmt->execute([
            'id' => $id,
            'category_id' => $data['category_id'],
            'description' => $data['description'],
            'amount' => $data['amount'],
            'frequency' => $data['frequency'] ?? 'monthly',
            'day_of_month' => $data['day_of_month'] ?? 1,
            'vendor' => $data['vendor'] ?? null,
            'notes' => $data['notes'] ?? null,
            'is_active' => $data['is_active'] ?? 1,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'] ?? null,
            'created_by' => $data['created_by']
        ]);

        return $id;
    }

    public static function update(string $id, array $data): bool
    {
        $db = Database::getInstance();
        $sets = [];
        $params = ['id' => $id];

        foreach (['category_id', 'description', 'amount', 'frequency', 'day_of_month', 'vendor', 'notes', 'is_active', 'start_date', 'end_date'] as $field) {
            if (array_key_exists($field, $data)) {
                $sets[] = "{$field} = :{$field}";
                $params[$field] = $data[$field];
            }
        }

        if (empty($sets)) {
            return false;
        }

        $sql = "UPDATE recurring_expenses SET " . implode(', ', $sets) . " WHERE id = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    }

    public static function updateLastGenerated(string $id, string $date): void
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('UPDATE recurring_expenses SET last_generated_date = :date WHERE id = :id');
        $stmt->execute(['id' => $id, 'date' => $date]);
    }

    public static function delete(string $id): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('DELETE FROM recurring_expenses WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public static function generateForMonth(int $year, int $month, string $userId): array
    {
        $recurring = self::getActiveForMonth($year, $month);
        $generated = [];
        $targetMonth = sprintf('%04d-%02d', $year, $month);

        foreach ($recurring as $r) {
            // Check if we should generate based on frequency
            if (!self::shouldGenerateForMonth($r, $year, $month)) {
                continue;
            }

            // Check if already generated for this month
            $db = Database::getInstance();
            $stmt = $db->prepare('
                SELECT COUNT(*) FROM expenses
                WHERE recurring_expense_id = :rid
                AND expense_date LIKE :month_pattern
            ');
            $stmt->execute([
                'rid' => $r['id'],
                'month_pattern' => $targetMonth . '%'
            ]);

            if ($stmt->fetchColumn() > 0) {
                continue; // Already generated
            }

            // Calculate expense date
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $dayOfMonth = min((int) $r['day_of_month'], $daysInMonth);
            $expenseDate = sprintf('%04d-%02d-%02d', $year, $month, $dayOfMonth);

            // Create the expense
            $expenseId = Expense::create([
                'category_id' => $r['category_id'],
                'description' => $r['description'],
                'amount' => $r['amount'],
                'expense_date' => $expenseDate,
                'payment_method' => 'direct_debit',
                'vendor' => $r['vendor'],
                'notes' => 'Généré automatiquement depuis dépense récurrente',
                'recurring_expense_id' => $r['id'],
                'created_by' => $userId
            ]);

            self::updateLastGenerated($r['id'], $expenseDate);
            $generated[] = $expenseId;
        }

        return $generated;
    }

    private static function shouldGenerateForMonth(array $recurring, int $year, int $month): bool
    {
        $frequency = $recurring['frequency'];
        $timezone = new \DateTimeZone($_ENV['APP_TIMEZONE'] ?? 'Europe/Paris');
        $startDate = new \DateTime($recurring['start_date'], $timezone);
        $startMonth = (int) $startDate->format('n');
        $startYear = (int) $startDate->format('Y');

        if ($frequency === 'monthly') {
            return true;
        }

        if ($frequency === 'quarterly') {
            // Every 3 months from start
            $monthsDiff = (($year - $startYear) * 12) + ($month - $startMonth);
            return $monthsDiff % 3 === 0;
        }

        if ($frequency === 'yearly') {
            // Same month as start date
            return $month === $startMonth;
        }

        return false;
    }

    public static function getMonthlyTotal(): float
    {
        $db = Database::getInstance();
        $stmt = $db->query('
            SELECT COALESCE(SUM(amount), 0)
            FROM recurring_expenses
            WHERE is_active = 1 AND frequency = "monthly"
        ');
        return (float) $stmt->fetchColumn();
    }
}
