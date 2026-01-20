<?php

declare(strict_types=1);

namespace App\Services;

use App\Config\Database;
use App\Utils\UUID;

class AuditService
{
    public static function log(
        ?string $userId,
        string $action,
        ?string $entityType = null,
        ?string $entityId = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare('
                INSERT INTO audit_logs (id, user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent, created_at)
                VALUES (:id, :user_id, :action, :entity_type, :entity_id, :old_values, :new_values, :ip_address, :user_agent, NOW())
            ');

            $stmt->execute([
                'id' => UUID::generate(),
                'user_id' => $userId,
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'old_values' => $oldValues ? json_encode($oldValues) : null,
                'new_values' => $newValues ? json_encode($newValues) : null,
                'ip_address' => self::getClientIp(),
                'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500)
            ]);
        } catch (\Exception $e) {
            error_log('Audit log failed: ' . $e->getMessage());
        }
    }

    public static function getClientIp(): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0';
    }

    public static function getRecent(int $limit = 100, int $offset = 0): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('
            SELECT
                al.*,
                u.first_name,
                u.last_name,
                u.email
            FROM audit_logs al
            LEFT JOIN users u ON al.user_id = u.id
            ORDER BY al.created_at DESC
            LIMIT :limit OFFSET :offset
        ');
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function getByUser(string $userId, int $limit = 50): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('
            SELECT * FROM audit_logs
            WHERE user_id = :user_id
            ORDER BY created_at DESC
            LIMIT :limit
        ');
        $stmt->bindValue(':user_id', $userId);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function getByEntity(string $entityType, string $entityId, int $limit = 50): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('
            SELECT
                al.*,
                u.first_name,
                u.last_name
            FROM audit_logs al
            LEFT JOIN users u ON al.user_id = u.id
            WHERE al.entity_type = :entity_type AND al.entity_id = :entity_id
            ORDER BY al.created_at DESC
            LIMIT :limit
        ');
        $stmt->bindValue(':entity_type', $entityType);
        $stmt->bindValue(':entity_id', $entityId);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
