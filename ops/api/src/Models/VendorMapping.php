<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use App\Utils\UUID;
use PDO;

class VendorMapping
{
    public static function findById(string $id): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('
            SELECT v.*, c.name as category_name, c.color as category_color
            FROM vendor_mappings v
            JOIN expense_categories c ON c.id = v.category_id
            WHERE v.id = :id
        ');
        $stmt->execute(['id' => $id]);
        $mapping = $stmt->fetch();
        return $mapping ?: null;
    }

    public static function getAll(): array
    {
        $db = Database::getInstance();
        $stmt = $db->query('
            SELECT v.*, c.name as category_name, c.color as category_color
            FROM vendor_mappings v
            JOIN expense_categories c ON c.id = v.category_id
            ORDER BY v.priority DESC, v.vendor_pattern ASC
        ');
        return $stmt->fetchAll();
    }

    public static function create(array $data): string
    {
        $db = Database::getInstance();
        $id = UUID::generate();

        $stmt = $db->prepare('
            INSERT INTO vendor_mappings (id, vendor_pattern, vendor_display_name, category_id, is_regex, priority, notes, created_by)
            VALUES (:id, :vendor_pattern, :vendor_display_name, :category_id, :is_regex, :priority, :notes, :created_by)
        ');

        $stmt->execute([
            'id' => $id,
            'vendor_pattern' => $data['vendor_pattern'],
            'vendor_display_name' => $data['vendor_display_name'] ?? null,
            'category_id' => $data['category_id'],
            'is_regex' => $data['is_regex'] ?? 0,
            'priority' => $data['priority'] ?? 0,
            'notes' => $data['notes'] ?? null,
            'created_by' => $data['created_by']
        ]);

        return $id;
    }

    public static function update(string $id, array $data): bool
    {
        $db = Database::getInstance();
        $sets = [];
        $params = ['id' => $id];

        foreach (['vendor_pattern', 'vendor_display_name', 'category_id', 'is_regex', 'priority', 'notes'] as $field) {
            if (array_key_exists($field, $data)) {
                $sets[] = "{$field} = :{$field}";
                $params[$field] = $data[$field];
            }
        }

        if (empty($sets)) {
            return false;
        }

        $sql = "UPDATE vendor_mappings SET " . implode(', ', $sets) . " WHERE id = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    }

    public static function delete(string $id): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('DELETE FROM vendor_mappings WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Find the best matching category for a vendor name
     * Returns the category_id if found, null otherwise
     */
    public static function findCategoryForVendor(string $vendorName): ?array
    {
        $mappings = self::getAll();

        foreach ($mappings as $mapping) {
            $matched = false;

            if ($mapping['is_regex']) {
                // Regex matching - wrap in try-catch for invalid patterns
                try {
                    $matched = @preg_match('/' . $mapping['vendor_pattern'] . '/i', $vendorName) === 1;
                } catch (\Exception $e) {
                    // Invalid regex pattern, skip this mapping
                    continue;
                }
                // Check for preg_match errors (returns false on error)
                if (preg_last_error() !== PREG_NO_ERROR) {
                    continue;
                }
            } else {
                // Simple contains matching (case insensitive)
                $matched = stripos($vendorName, $mapping['vendor_pattern']) !== false;
            }

            if ($matched) {
                return [
                    'category_id' => $mapping['category_id'],
                    'category_name' => $mapping['category_name'],
                    'vendor_display_name' => $mapping['vendor_display_name']
                ];
            }
        }

        return null;
    }

    /**
     * Search vendors for autocomplete
     * Returns distinct vendor names (display_name or pattern)
     */
    public static function search(string $query, int $limit = 10): array
    {
        $db = Database::getInstance();
        $searchPattern = '%' . $query . '%';
        $stmt = $db->prepare('
            SELECT DISTINCT
                COALESCE(vendor_display_name, vendor_pattern) as vendor_name,
                vendor_pattern,
                vendor_display_name
            FROM vendor_mappings
            WHERE vendor_display_name LIKE :query1
               OR vendor_pattern LIKE :query2
            ORDER BY vendor_display_name ASC, vendor_pattern ASC
            LIMIT :limit
        ');
        $stmt->bindValue('query1', $searchPattern, PDO::PARAM_STR);
        $stmt->bindValue('query2', $searchPattern, PDO::PARAM_STR);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get all unique vendor names for autocomplete (no filter)
     */
    public static function getAllVendorNames(): array
    {
        $db = Database::getInstance();
        $stmt = $db->query('
            SELECT DISTINCT
                COALESCE(vendor_display_name, vendor_pattern) as vendor_name
            FROM vendor_mappings
            ORDER BY vendor_name ASC
        ');
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Create a mapping from an existing expense vendor
     * This is used when the user categorizes an expense and wants to save the mapping
     */
    public static function createFromExpense(string $vendorName, string $categoryId, string $userId, ?string $displayName = null): string
    {
        // Check if mapping already exists
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT id FROM vendor_mappings WHERE vendor_pattern = :pattern');
        $stmt->execute(['pattern' => $vendorName]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Update the existing mapping
            self::update($existing['id'], [
                'category_id' => $categoryId,
                'vendor_display_name' => $displayName
            ]);
            return $existing['id'];
        }

        return self::create([
            'vendor_pattern' => $vendorName,
            'vendor_display_name' => $displayName,
            'category_id' => $categoryId,
            'is_regex' => 0,
            'priority' => 0,
            'created_by' => $userId
        ]);
    }
}
