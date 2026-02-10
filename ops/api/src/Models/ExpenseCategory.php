<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use App\Utils\UUID;
use PDO;

class ExpenseCategory
{
    public static function findById(string $id): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM expense_categories WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $category = $stmt->fetch();
        return $category ?: null;
    }

    public static function findBySlug(string $slug): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM expense_categories WHERE slug = :slug');
        $stmt->execute(['slug' => $slug]);
        $category = $stmt->fetch();
        return $category ?: null;
    }

    public static function getAll(bool $activeOnly = true): array
    {
        $db = Database::getInstance();
        $sql = 'SELECT * FROM expense_categories';
        if ($activeOnly) {
            $sql .= ' WHERE is_active = 1';
        }
        $sql .= ' ORDER BY sort_order ASC, name ASC';
        $stmt = $db->query($sql);
        return $stmt->fetchAll();
    }

    public static function create(array $data): string
    {
        $db = Database::getInstance();
        $id = UUID::generate();

        $stmt = $db->prepare('
            INSERT INTO expense_categories (id, name, slug, color, icon, sort_order, is_active)
            VALUES (:id, :name, :slug, :color, :icon, :sort_order, :is_active)
        ');

        $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'slug' => $data['slug'] ?? self::generateSlug($data['name']),
            'color' => $data['color'] ?? '#6B7280',
            'icon' => $data['icon'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $data['is_active'] ?? 1
        ]);

        return $id;
    }

    public static function update(string $id, array $data): bool
    {
        $db = Database::getInstance();
        $sets = [];
        $params = ['id' => $id];

        foreach (['name', 'slug', 'color', 'icon', 'sort_order', 'is_active'] as $field) {
            if (isset($data[$field])) {
                $sets[] = "{$field} = :{$field}";
                $params[$field] = $data[$field];
            }
        }

        if (empty($sets)) {
            return false;
        }

        $sql = "UPDATE expense_categories SET " . implode(', ', $sets) . " WHERE id = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    }

    public static function delete(string $id): bool
    {
        $db = Database::getInstance();

        // Check if category has expenses
        $stmt = $db->prepare('SELECT COUNT(*) FROM expenses WHERE category_id = :id');
        $stmt->execute(['id' => $id]);
        if ($stmt->fetchColumn() > 0) {
            return false; // Cannot delete category with expenses
        }

        $stmt = $db->prepare('DELETE FROM expense_categories WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public static function generateSlug(string $name): string
    {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }

    public static function getWithStats(int $year, int $month): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('
            SELECT
                c.*,
                COALESCE(SUM(e.amount), 0) as total_amount,
                COUNT(e.id) as expense_count
            FROM expense_categories c
            LEFT JOIN expenses e ON e.category_id = c.id
                AND YEAR(e.expense_date) = :year
                AND MONTH(e.expense_date) = :month
            WHERE c.is_active = 1
            GROUP BY c.id
            ORDER BY c.sort_order ASC, c.name ASC
        ');
        $stmt->execute(['year' => $year, 'month' => $month]);
        return $stmt->fetchAll();
    }
}
