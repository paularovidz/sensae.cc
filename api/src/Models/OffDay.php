<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use App\Utils\UUID;

class OffDay
{
    /**
     * Get all off days ordered by date
     */
    public static function getAll(): array
    {
        $db = Database::getInstance();
        $stmt = $db->query('
            SELECT od.*, u.first_name, u.last_name
            FROM off_days od
            LEFT JOIN users u ON od.created_by = u.id
            ORDER BY od.date ASC
        ');

        return $stmt->fetchAll();
    }

    /**
     * Get off day by ID
     */
    public static function getById(string $id): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM off_days WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $offDay = $stmt->fetch();

        return $offDay ?: null;
    }

    /**
     * Get off day by date
     */
    public static function getByDate(\DateTime $date): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM off_days WHERE date = :date');
        $stmt->execute(['date' => $date->format('Y-m-d')]);
        $offDay = $stmt->fetch();

        return $offDay ?: null;
    }

    /**
     * Get off days in a date range (inclusive)
     */
    public static function getInRange(\DateTime $start, \DateTime $end): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('
            SELECT * FROM off_days
            WHERE date >= :start AND date <= :end
            ORDER BY date ASC
        ');
        $stmt->execute([
            'start' => $start->format('Y-m-d'),
            'end' => $end->format('Y-m-d')
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Get upcoming off days (from today)
     */
    public static function getUpcoming(int $limit = 50): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('
            SELECT od.*, u.first_name, u.last_name
            FROM off_days od
            LEFT JOIN users u ON od.created_by = u.id
            WHERE od.date >= CURDATE()
            ORDER BY od.date ASC
            LIMIT :limit
        ');
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Check if a date is an off day
     */
    public static function isOffDay(\DateTime $date): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT COUNT(*) FROM off_days WHERE date = :date');
        $stmt->execute(['date' => $date->format('Y-m-d')]);

        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Create a new off day
     */
    public static function create(array $data): ?string
    {
        $db = Database::getInstance();
        $id = UUID::generate();

        // Check if date already exists
        $date = $data['date'] instanceof \DateTime
            ? $data['date']->format('Y-m-d')
            : $data['date'];

        $stmt = $db->prepare('SELECT COUNT(*) FROM off_days WHERE date = :date');
        $stmt->execute(['date' => $date]);

        if ((int)$stmt->fetchColumn() > 0) {
            return null; // Date already exists
        }

        $stmt = $db->prepare('
            INSERT INTO off_days (id, date, reason, created_by)
            VALUES (:id, :date, :reason, :created_by)
        ');

        $stmt->execute([
            'id' => $id,
            'date' => $date,
            'reason' => isset($data['reason']) ? trim($data['reason']) : null,
            'created_by' => $data['created_by'] ?? null
        ]);

        return $id;
    }

    /**
     * Delete an off day
     */
    public static function delete(string $id): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('DELETE FROM off_days WHERE id = :id');

        return $stmt->execute(['id' => $id]);
    }

    /**
     * Check if a date exists
     */
    public static function dateExists(string $date): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT COUNT(*) FROM off_days WHERE date = :date');
        $stmt->execute(['date' => $date]);

        return (int)$stmt->fetchColumn() > 0;
    }
}
