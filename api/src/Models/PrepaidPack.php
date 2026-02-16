<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use App\Utils\UUID;

class PrepaidPack
{
    // Pack types
    public const TYPE_PACK_2 = 'pack_2';
    public const TYPE_PACK_4 = 'pack_4';

    public const PACK_TYPES = [
        self::TYPE_PACK_2,
        self::TYPE_PACK_4
    ];

    // Duration types
    public const DURATION_REGULAR = 'regular';
    public const DURATION_DISCOVERY = 'discovery';
    public const DURATION_ANY = 'any';

    public const DURATION_TYPES = [
        self::DURATION_REGULAR,
        self::DURATION_DISCOVERY,
        self::DURATION_ANY
    ];

    public const LABELS = [
        'pack_type' => [
            'pack_2' => 'Pack 2 séances',
            'pack_4' => 'Pack 4 séances'
        ],
        'duration_type' => [
            'regular' => 'Séances classiques uniquement',
            'discovery' => 'Séances découverte uniquement',
            'any' => 'Tous types de séances'
        ]
    ];

    // =========================================================================
    // RECHERCHE
    // =========================================================================

    public static function findById(string $id): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('
            SELECT pp.*,
                   u.first_name as user_first_name,
                   u.last_name as user_last_name,
                   u.email as user_email,
                   c.first_name as creator_first_name,
                   c.last_name as creator_last_name
            FROM prepaid_packs pp
            INNER JOIN users u ON pp.user_id = u.id
            LEFT JOIN users c ON pp.created_by = c.id
            WHERE pp.id = :id
        ');
        $stmt->execute(['id' => $id]);
        $pack = $stmt->fetch();

        if ($pack) {
            $pack = self::castBooleans($pack);
            $pack['sessions_remaining'] = $pack['sessions_total'] - $pack['sessions_used'];
            $pack['is_expired'] = self::isExpired($pack);
            $pack['is_exhausted'] = $pack['sessions_remaining'] <= 0;
        }

        return $pack ?: null;
    }

    public static function findAll(int $limit = 50, int $offset = 0, ?string $search = null, array $filters = []): array
    {
        $db = Database::getInstance();

        $where = [];
        $params = [];

        if ($search !== null && $search !== '') {
            $where[] = '(u.first_name LIKE :s1 OR u.last_name LIKE :s2 OR u.email LIKE :s3 OR CONCAT(u.first_name, " ", u.last_name) LIKE :s4)';
            $params['s1'] = '%' . $search . '%';
            $params['s2'] = '%' . $search . '%';
            $params['s3'] = '%' . $search . '%';
            $params['s4'] = '%' . $search . '%';
        }

        if (isset($filters['user_id'])) {
            $where[] = 'pp.user_id = :user_id';
            $params['user_id'] = $filters['user_id'];
        }

        if (isset($filters['is_active'])) {
            $where[] = 'pp.is_active = :is_active';
            $params['is_active'] = $filters['is_active'] ? 1 : 0;
        }

        if (isset($filters['has_credits'])) {
            $where[] = 'pp.sessions_used < pp.sessions_total';
        }

        if (isset($filters['not_expired'])) {
            $where[] = '(pp.expires_at IS NULL OR pp.expires_at > NOW())';
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "
            SELECT pp.*,
                   u.first_name as user_first_name,
                   u.last_name as user_last_name,
                   u.email as user_email,
                   c.first_name as creator_first_name,
                   c.last_name as creator_last_name
            FROM prepaid_packs pp
            INNER JOIN users u ON pp.user_id = u.id
            LEFT JOIN users c ON pp.created_by = c.id
            {$whereClause}
            ORDER BY pp.created_at DESC
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $packs = $stmt->fetchAll();
        foreach ($packs as &$pack) {
            $pack = self::castBooleans($pack);
            $pack['sessions_remaining'] = $pack['sessions_total'] - $pack['sessions_used'];
            $pack['is_expired'] = self::isExpired($pack);
            $pack['is_exhausted'] = $pack['sessions_remaining'] <= 0;
        }

        return $packs;
    }

    public static function count(?string $search = null, array $filters = []): int
    {
        $db = Database::getInstance();

        $where = [];
        $params = [];

        if ($search !== null && $search !== '') {
            $where[] = '(u.first_name LIKE :s1 OR u.last_name LIKE :s2 OR u.email LIKE :s3)';
            $params['s1'] = '%' . $search . '%';
            $params['s2'] = '%' . $search . '%';
            $params['s3'] = '%' . $search . '%';
        }

        if (isset($filters['user_id'])) {
            $where[] = 'pp.user_id = :user_id';
            $params['user_id'] = $filters['user_id'];
        }

        if (isset($filters['is_active'])) {
            $where[] = 'pp.is_active = :is_active';
            $params['is_active'] = $filters['is_active'] ? 1 : 0;
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT COUNT(*) FROM prepaid_packs pp INNER JOIN users u ON pp.user_id = u.id {$whereClause}";

        $stmt = $db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    // =========================================================================
    // CRÉATION ET MISE À JOUR
    // =========================================================================

    public static function create(array $data): string
    {
        $db = Database::getInstance();
        $id = UUID::generate();

        // Calculate expiration if configured
        $expiresAt = null;
        if (!empty($data['expires_at'])) {
            $expiresAt = $data['expires_at'];
        } else {
            $expiryMonths = Setting::getInteger('prepaid_default_expiry_months', 12);
            if ($expiryMonths > 0) {
                $expiresAt = (new \DateTime())->modify("+{$expiryMonths} months")->format('Y-m-d H:i:s');
            }
        }

        $stmt = $db->prepare('
            INSERT INTO prepaid_packs (
                id, user_id, pack_type, sessions_total, sessions_used,
                price_paid, duration_type, expires_at, purchased_at,
                admin_notes, created_by, is_active
            ) VALUES (
                :id, :user_id, :pack_type, :sessions_total, :sessions_used,
                :price_paid, :duration_type, :expires_at, :purchased_at,
                :admin_notes, :created_by, :is_active
            )
        ');

        $stmt->execute([
            'id' => $id,
            'user_id' => $data['user_id'],
            'pack_type' => $data['pack_type'],
            'sessions_total' => (int)$data['sessions_total'],
            'sessions_used' => (int)($data['sessions_used'] ?? 0),
            'price_paid' => (float)$data['price_paid'],
            'duration_type' => $data['duration_type'] ?? self::DURATION_ANY,
            'expires_at' => $expiresAt,
            'purchased_at' => $data['purchased_at'] ?? (new \DateTime())->format('Y-m-d H:i:s'),
            'admin_notes' => !empty($data['admin_notes']) ? trim($data['admin_notes']) : null,
            'created_by' => $data['created_by'] ?? null,
            'is_active' => ($data['is_active'] ?? true) ? 1 : 0
        ]);

        return $id;
    }

    public static function update(string $id, array $data): bool
    {
        $db = Database::getInstance();

        $fields = [];
        $params = ['id' => $id];

        $allowedFields = [
            'sessions_total', 'sessions_used', 'price_paid', 'duration_type',
            'expires_at', 'admin_notes', 'is_active'
        ];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = :{$field}";
                $value = $data[$field];

                if ($field === 'is_active') {
                    $value = $value ? 1 : 0;
                } elseif (in_array($field, ['sessions_total', 'sessions_used'])) {
                    $value = (int)$value;
                } elseif ($field === 'price_paid') {
                    $value = (float)$value;
                } elseif ($field === 'admin_notes') {
                    $value = !empty($value) ? trim($value) : null;
                }

                $params[$field] = $value;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $sql = 'UPDATE prepaid_packs SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $db->prepare($sql);

        return $stmt->execute($params);
    }

    public static function delete(string $id): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('DELETE FROM prepaid_packs WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public static function deactivate(string $id): bool
    {
        return self::update($id, ['is_active' => false]);
    }

    // =========================================================================
    // BALANCE & CREDIT MANAGEMENT
    // =========================================================================

    /**
     * Get available credits for a user (for booking wizard)
     * Returns total remaining sessions across all valid packs
     */
    public static function getBalance(string $userId, ?string $durationType = null): array
    {
        $packs = self::getValidPacks($userId, $durationType);

        $total = 0;
        foreach ($packs as $pack) {
            $total += ($pack['sessions_total'] - $pack['sessions_used']);
        }

        return [
            'total_credits' => $total,
            'packs_count' => count($packs),
            'packs' => $packs
        ];
    }

    /**
     * Get valid (active, not expired, has credits) packs for a user
     * Sorted by expiration date (FIFO) and then by purchase date
     */
    public static function getValidPacks(string $userId, ?string $durationType = null): array
    {
        $db = Database::getInstance();

        $where = [
            'pp.user_id = :user_id',
            'pp.is_active = 1',
            'pp.sessions_used < pp.sessions_total',
            '(pp.expires_at IS NULL OR pp.expires_at > NOW())'
        ];
        $params = ['user_id' => $userId];

        // Filter by duration type if specified
        if ($durationType !== null) {
            $where[] = "(pp.duration_type = 'any' OR pp.duration_type = :duration_type)";
            $params['duration_type'] = $durationType;
        }

        $whereClause = 'WHERE ' . implode(' AND ', $where);

        // Sort by: expiring packs first, then by purchase date (oldest first) = FIFO
        $sql = "
            SELECT pp.*
            FROM prepaid_packs pp
            {$whereClause}
            ORDER BY
                CASE WHEN pp.expires_at IS NULL THEN 1 ELSE 0 END,
                pp.expires_at ASC,
                pp.purchased_at ASC
        ";

        $stmt = $db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->execute();

        $packs = $stmt->fetchAll();
        foreach ($packs as &$pack) {
            $pack = self::castBooleans($pack);
            $pack['sessions_remaining'] = $pack['sessions_total'] - $pack['sessions_used'];
        }

        return $packs;
    }

    /**
     * Get the best pack to use for a session (FIFO: expiring first, then oldest)
     */
    public static function getBestPackForSession(string $userId, string $durationType): ?array
    {
        $packs = self::getValidPacks($userId, $durationType);
        return !empty($packs) ? $packs[0] : null;
    }

    /**
     * Use a credit from a pack for a session
     */
    public static function useCredit(string $packId, string $sessionId): bool
    {
        $db = Database::getInstance();

        try {
            $db->beginTransaction();

            // Increment sessions_used
            $stmt = $db->prepare('
                UPDATE prepaid_packs
                SET sessions_used = sessions_used + 1
                WHERE id = :id AND sessions_used < sessions_total
            ');
            $stmt->execute(['id' => $packId]);

            if ($stmt->rowCount() === 0) {
                $db->rollBack();
                return false;
            }

            // Record usage
            $stmt = $db->prepare('
                INSERT INTO prepaid_pack_usages (id, pack_id, session_id)
                VALUES (:id, :pack_id, :session_id)
            ');
            $stmt->execute([
                'id' => UUID::generate(),
                'pack_id' => $packId,
                'session_id' => $sessionId
            ]);

            $db->commit();
            return true;
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Refund a credit to a pack (when session is cancelled)
     */
    public static function refundCredit(string $sessionId): bool
    {
        $db = Database::getInstance();

        try {
            $db->beginTransaction();

            // Find the usage record
            $stmt = $db->prepare('SELECT pack_id FROM prepaid_pack_usages WHERE session_id = :session_id');
            $stmt->execute(['session_id' => $sessionId]);
            $usage = $stmt->fetch();

            if (!$usage) {
                $db->rollBack();
                return false;
            }

            // Decrement sessions_used
            $stmt = $db->prepare('
                UPDATE prepaid_packs
                SET sessions_used = sessions_used - 1
                WHERE id = :id AND sessions_used > 0
            ');
            $stmt->execute(['id' => $usage['pack_id']]);

            // Delete usage record
            $stmt = $db->prepare('DELETE FROM prepaid_pack_usages WHERE session_id = :session_id');
            $stmt->execute(['session_id' => $sessionId]);

            $db->commit();
            return true;
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Check if a session used a prepaid credit
     */
    public static function hasUsedCredit(string $sessionId): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT 1 FROM prepaid_pack_usages WHERE session_id = :session_id');
        $stmt->execute(['session_id' => $sessionId]);
        return $stmt->fetch() !== false;
    }

    // =========================================================================
    // PACK TYPES & PRICING
    // =========================================================================

    /**
     * Get available pack types with their prices
     */
    public static function getPackTypes(): array
    {
        $pack2Sessions = Setting::getInteger('prepaid_pack_2_sessions', 2);
        $pack2Price = Setting::getInteger('prepaid_pack_2_price', 110);
        $pack4Sessions = Setting::getInteger('prepaid_pack_4_sessions', 4);
        $pack4Price = Setting::getInteger('prepaid_pack_4_price', 200);

        return [
            self::TYPE_PACK_2 => [
                'type' => self::TYPE_PACK_2,
                'label' => self::LABELS['pack_type'][self::TYPE_PACK_2],
                'sessions' => $pack2Sessions,
                'price' => $pack2Price,
                'price_per_session' => $pack2Sessions > 0 ? round($pack2Price / $pack2Sessions, 2) : 0
            ],
            self::TYPE_PACK_4 => [
                'type' => self::TYPE_PACK_4,
                'label' => self::LABELS['pack_type'][self::TYPE_PACK_4],
                'sessions' => $pack4Sessions,
                'price' => $pack4Price,
                'price_per_session' => $pack4Sessions > 0 ? round($pack4Price / $pack4Sessions, 2) : 0
            ]
        ];
    }

    /**
     * Get sessions and price for a pack type
     */
    public static function getPackDetails(string $packType): ?array
    {
        $types = self::getPackTypes();
        return $types[$packType] ?? null;
    }

    // =========================================================================
    // USAGE HISTORY
    // =========================================================================

    /**
     * Get usage history for a pack
     */
    public static function getUsages(string $packId, int $limit = 50, int $offset = 0): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT ppu.*,
                   s.session_date,
                   s.duration_type,
                   p.first_name as person_first_name,
                   p.last_name as person_last_name
            FROM prepaid_pack_usages ppu
            INNER JOIN sessions s ON ppu.session_id = s.id
            LEFT JOIN persons p ON s.person_id = p.id
            WHERE ppu.pack_id = :pack_id
            ORDER BY ppu.used_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':pack_id', $packId);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // =========================================================================
    // USER PACKS
    // =========================================================================

    /**
     * Get all packs for a user (for user detail page)
     */
    public static function findByUser(string $userId): array
    {
        return self::findAll(100, 0, null, ['user_id' => $userId]);
    }

    // =========================================================================
    // REVENUE (for OPS)
    // =========================================================================

    /**
     * Get prepaid pack revenue for a period
     */
    public static function getRevenue(int $year, ?int $month = null): array
    {
        $db = Database::getInstance();

        if ($month !== null) {
            $stmt = $db->prepare("
                SELECT SUM(price_paid) as total, COUNT(*) as count
                FROM prepaid_packs
                WHERE YEAR(purchased_at) = :year AND MONTH(purchased_at) = :month
            ");
            $stmt->execute(['year' => $year, 'month' => $month]);
        } else {
            $stmt = $db->prepare("
                SELECT SUM(price_paid) as total, COUNT(*) as count
                FROM prepaid_packs
                WHERE YEAR(purchased_at) = :year
            ");
            $stmt->execute(['year' => $year]);
        }

        $result = $stmt->fetch();

        return [
            'total' => (float)($result['total'] ?? 0),
            'count' => (int)($result['count'] ?? 0)
        ];
    }

    /**
     * Get prepaid pack revenue by month for a year
     */
    public static function getRevenueByMonth(int $year): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT MONTH(purchased_at) as month,
                   SUM(price_paid) as total,
                   COUNT(*) as count
            FROM prepaid_packs
            WHERE YEAR(purchased_at) = :year
            GROUP BY MONTH(purchased_at)
            ORDER BY MONTH(purchased_at)
        ");
        $stmt->execute(['year' => $year]);

        $results = $stmt->fetchAll();
        $byMonth = [];

        foreach ($results as $row) {
            $byMonth[(int)$row['month']] = [
                'total' => (float)$row['total'],
                'count' => (int)$row['count']
            ];
        }

        // Fill missing months with zeros
        for ($m = 1; $m <= 12; $m++) {
            if (!isset($byMonth[$m])) {
                $byMonth[$m] = ['total' => 0, 'count' => 0];
            }
        }

        ksort($byMonth);
        return $byMonth;
    }

    /**
     * Get daily prepaid revenue for a month
     */
    public static function getDailyRevenue(int $year, int $month): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT DAY(purchased_at) as day,
                   SUM(price_paid) as total,
                   COUNT(*) as count
            FROM prepaid_packs
            WHERE YEAR(purchased_at) = :year AND MONTH(purchased_at) = :month
            GROUP BY DAY(purchased_at)
            ORDER BY DAY(purchased_at)
        ");
        $stmt->execute(['year' => $year, 'month' => $month]);

        $results = $stmt->fetchAll();
        $byDay = [];

        foreach ($results as $row) {
            $byDay[(int)$row['day']] = [
                'total' => (float)$row['total'],
                'count' => (int)$row['count']
            ];
        }

        return $byDay;
    }

    // =========================================================================
    // UTILITAIRES
    // =========================================================================

    private static function isExpired(array $pack): bool
    {
        if (empty($pack['expires_at'])) {
            return false;
        }
        return new \DateTime($pack['expires_at']) < new \DateTime();
    }

    private static function castBooleans(array $pack): array
    {
        if (isset($pack['is_active'])) {
            $pack['is_active'] = (bool)$pack['is_active'];
        }
        return $pack;
    }
}
