<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use App\Utils\UUID;
use PDO;

class BankImport
{
    public static function findById(string $id): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('
            SELECT bi.*, u.first_name, u.last_name
            FROM bank_imports bi
            JOIN users u ON u.id = bi.imported_by
            WHERE bi.id = :id
        ');
        $stmt->execute(['id' => $id]);
        $import = $stmt->fetch();
        return $import ?: null;
    }

    public static function getAll(int $limit = 50): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('
            SELECT bi.*, u.first_name, u.last_name
            FROM bank_imports bi
            JOIN users u ON u.id = bi.imported_by
            ORDER BY bi.import_date DESC
            LIMIT :limit
        ');
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function create(array $data): string
    {
        $db = Database::getInstance();
        $id = UUID::generate();

        $stmt = $db->prepare('
            INSERT INTO bank_imports (id, filename, rows_imported, rows_skipped, imported_by)
            VALUES (:id, :filename, :rows_imported, :rows_skipped, :imported_by)
        ');

        $stmt->execute([
            'id' => $id,
            'filename' => $data['filename'],
            'rows_imported' => $data['rows_imported'] ?? 0,
            'rows_skipped' => $data['rows_skipped'] ?? 0,
            'imported_by' => $data['imported_by']
        ]);

        return $id;
    }

    public static function updateCounts(string $id, int $imported, int $skipped): void
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('
            UPDATE bank_imports SET rows_imported = :imported, rows_skipped = :skipped WHERE id = :id
        ');
        $stmt->execute(['id' => $id, 'imported' => $imported, 'skipped' => $skipped]);
    }
}
