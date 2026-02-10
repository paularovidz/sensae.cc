<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use App\Utils\UUID;
use PDO;

class MonthState
{
    public const STATE_ESTIMATED = 'estimated';
    public const STATE_ACTUAL = 'actual';

    public static function findByMonth(int $year, int $month): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM month_states WHERE year = :year AND month = :month');
        $stmt->execute(['year' => $year, 'month' => $month]);
        $state = $stmt->fetch();
        return $state ?: null;
    }

    public static function getByYear(int $year): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM month_states WHERE year = :year ORDER BY month');
        $stmt->execute(['year' => $year]);

        $results = [];
        foreach ($stmt->fetchAll() as $row) {
            $results[(int) $row['month']] = $row;
        }
        return $results;
    }

    public static function getState(int $year, int $month): string
    {
        $state = self::findByMonth($year, $month);
        return $state ? $state['state'] : self::STATE_ESTIMATED;
    }

    public static function isActual(int $year, int $month): bool
    {
        return self::getState($year, $month) === self::STATE_ACTUAL;
    }

    public static function setState(int $year, int $month, string $state, string $userId, ?string $notes = null): void
    {
        $db = Database::getInstance();
        $existing = self::findByMonth($year, $month);

        if ($existing) {
            $stmt = $db->prepare('
                UPDATE month_states
                SET state = :state,
                    locked_at = :locked_at,
                    locked_by = :locked_by,
                    notes = :notes
                WHERE year = :year AND month = :month
            ');
            $stmt->execute([
                'year' => $year,
                'month' => $month,
                'state' => $state,
                'locked_at' => $state === self::STATE_ACTUAL ? date('Y-m-d H:i:s') : null,
                'locked_by' => $state === self::STATE_ACTUAL ? $userId : null,
                'notes' => $notes
            ]);
        } else {
            $stmt = $db->prepare('
                INSERT INTO month_states (id, year, month, state, locked_at, locked_by, notes)
                VALUES (:id, :year, :month, :state, :locked_at, :locked_by, :notes)
            ');
            $stmt->execute([
                'id' => UUID::generate(),
                'year' => $year,
                'month' => $month,
                'state' => $state,
                'locked_at' => $state === self::STATE_ACTUAL ? date('Y-m-d H:i:s') : null,
                'locked_by' => $state === self::STATE_ACTUAL ? $userId : null,
                'notes' => $notes
            ]);
        }
    }

    public static function setActual(int $year, int $month, string $userId, ?string $notes = null): void
    {
        self::setState($year, $month, self::STATE_ACTUAL, $userId, $notes);
    }

    public static function setEstimated(int $year, int $month, string $userId, ?string $notes = null): void
    {
        self::setState($year, $month, self::STATE_ESTIMATED, $userId, $notes);
    }
}
