<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use App\Utils\UUID;

class OffDay
{
    /**
     * Get all off days ordered by start_date
     */
    public static function getAll(): array
    {
        $db = Database::getInstance();
        $stmt = $db->query('
            SELECT od.*, u.first_name, u.last_name
            FROM off_days od
            LEFT JOIN users u ON od.created_by = u.id
            ORDER BY od.start_date ASC
        ');

        return $stmt->fetchAll();
    }

    /**
     * Get upcoming off days (end_date >= today)
     */
    public static function getUpcoming(int $limit = 50): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('
            SELECT od.*, u.first_name, u.last_name
            FROM off_days od
            LEFT JOIN users u ON od.created_by = u.id
            WHERE od.end_date >= CURDATE()
            ORDER BY od.start_date ASC
            LIMIT :limit
        ');
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

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
     * Get off days that overlap with a date range
     */
    public static function getInRange(\DateTime $start, \DateTime $end): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('
            SELECT * FROM off_days
            WHERE start_date <= :end AND end_date >= :start
            ORDER BY start_date ASC
        ');
        $stmt->execute([
            'start' => $start->format('Y-m-d'),
            'end' => $end->format('Y-m-d')
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Check if a specific date is within an off period
     */
    public static function isOffDay(\DateTime $date): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('
            SELECT COUNT(*) FROM off_days
            WHERE :date BETWEEN start_date AND end_date
        ');
        $stmt->execute(['date' => $date->format('Y-m-d')]);

        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Create a new off day/period
     */
    public static function create(array $data): ?string
    {
        $db = Database::getInstance();
        $id = UUID::generate();

        $startDate = $data['start_date'] instanceof \DateTime
            ? $data['start_date']->format('Y-m-d')
            : $data['start_date'];

        // If no end_date provided, use start_date (single day)
        $endDate = isset($data['end_date'])
            ? ($data['end_date'] instanceof \DateTime
                ? $data['end_date']->format('Y-m-d')
                : $data['end_date'])
            : $startDate;

        // Ensure end_date is not before start_date
        if ($endDate < $startDate) {
            $endDate = $startDate;
        }

        $stmt = $db->prepare('
            INSERT INTO off_days (id, start_date, end_date, reason, created_by)
            VALUES (:id, :start_date, :end_date, :reason, :created_by)
        ');

        $stmt->execute([
            'id' => $id,
            'start_date' => $startDate,
            'end_date' => $endDate,
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
     * Check if a period overlaps with existing off days
     */
    public static function periodOverlaps(string $startDate, string $endDate, ?string $excludeId = null): bool
    {
        $db = Database::getInstance();

        $sql = '
            SELECT COUNT(*) FROM off_days
            WHERE start_date <= :end_date AND end_date >= :start_date
        ';
        $params = [
            'start_date' => $startDate,
            'end_date' => $endDate
        ];

        if ($excludeId) {
            $sql .= ' AND id != :exclude_id';
            $params['exclude_id'] = $excludeId;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return (int)$stmt->fetchColumn() > 0;
    }
}
