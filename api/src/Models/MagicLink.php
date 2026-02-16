<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use App\Utils\UUID;

class MagicLink
{
    private const EXPIRY_MINUTES = 15;

    public static function create(string $userId, string $ipAddress = null): string
    {
        $db = Database::getInstance();

        // Invalidate existing unused tokens for this user
        $stmt = $db->prepare('
            UPDATE magic_links
            SET used_at = NOW()
            WHERE user_id = :user_id AND used_at IS NULL
        ');
        $stmt->execute(['user_id' => $userId]);

        // Generate secure token
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);

        $id = UUID::generate();
        $timezone = new \DateTimeZone($_ENV['APP_TIMEZONE'] ?? 'Europe/Paris');
        $expiresAt = (new \DateTime('now', $timezone))->modify('+' . self::EXPIRY_MINUTES . ' minutes');

        $stmt = $db->prepare('
            INSERT INTO magic_links (id, user_id, token, expires_at, ip_address)
            VALUES (:id, :user_id, :token, :expires_at, :ip_address)
        ');

        $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
            'token' => $tokenHash,
            'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
            'ip_address' => $ipAddress
        ]);

        return $token; // Return unhashed token to send via email
    }

    public static function verify(string $token): ?array
    {
        $db = Database::getInstance();
        $tokenHash = hash('sha256', $token);

        $stmt = $db->prepare('
            SELECT ml.*, u.email, u.first_name, u.last_name, u.role, u.is_active
            FROM magic_links ml
            INNER JOIN users u ON ml.user_id = u.id
            WHERE ml.token = :token
              AND ml.used_at IS NULL
              AND ml.expires_at > NOW()
        ');
        $stmt->execute(['token' => $tokenHash]);
        $result = $stmt->fetch();

        if (!$result) {
            return null;
        }

        if (!$result['is_active']) {
            return null;
        }

        return $result;
    }

    public static function markAsUsed(string $id): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('
            UPDATE magic_links
            SET used_at = NOW()
            WHERE id = :id
        ');

        return $stmt->execute(['id' => $id]);
    }

    public static function cleanup(): int
    {
        $db = Database::getInstance();

        // Delete expired tokens older than 24 hours
        $stmt = $db->prepare('
            DELETE FROM magic_links
            WHERE expires_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ');
        $stmt->execute();

        return $stmt->rowCount();
    }

    public static function countRecentRequests(string $userId, int $minutes = 60): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('
            SELECT COUNT(*)
            FROM magic_links
            WHERE user_id = :user_id
              AND created_at > DATE_SUB(NOW(), INTERVAL :minutes MINUTE)
        ');
        $stmt->execute([
            'user_id' => $userId,
            'minutes' => $minutes
        ]);

        return (int)$stmt->fetchColumn();
    }

    public static function countRecentRequestsByEmail(string $email, int $minutes = 60): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('
            SELECT COUNT(*)
            FROM magic_links ml
            INNER JOIN users u ON ml.user_id = u.id
            WHERE u.email = :email
              AND ml.created_at > DATE_SUB(NOW(), INTERVAL :minutes MINUTE)
        ');
        $stmt->execute([
            'email' => strtolower(trim($email)),
            'minutes' => $minutes
        ]);

        return (int)$stmt->fetchColumn();
    }

    public static function getOldestRecentRequestByEmail(string $email, int $minutes = 60): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('
            SELECT ml.*
            FROM magic_links ml
            INNER JOIN users u ON ml.user_id = u.id
            WHERE u.email = :email
              AND ml.created_at > DATE_SUB(NOW(), INTERVAL :minutes MINUTE)
            ORDER BY ml.created_at ASC
            LIMIT 1
        ');
        $stmt->execute([
            'email' => strtolower(trim($email)),
            'minutes' => $minutes
        ]);

        $result = $stmt->fetch();
        return $result ?: null;
    }
}
