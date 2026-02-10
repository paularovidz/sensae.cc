<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use App\Utils\UUID;
use PDO;

class RefreshToken
{
    private const EXPIRY_DAYS = 7;

    public static function create(string $userId, string $ipAddress = null, string $userAgent = null): string
    {
        $db = Database::getInstance();

        // Generate secure token
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);

        $id = UUID::generate();
        $expiresAt = (new \DateTime())->modify('+' . self::EXPIRY_DAYS . ' days');

        $stmt = $db->prepare('
            INSERT INTO refresh_tokens (id, user_id, token_hash, ip_address, user_agent, expires_at)
            VALUES (:id, :user_id, :token_hash, :ip_address, :user_agent, :expires_at)
        ');

        $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
            'token_hash' => $tokenHash,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent ? substr($userAgent, 0, 500) : null,
            'expires_at' => $expiresAt->format('Y-m-d H:i:s')
        ]);

        return $token; // Return unhashed token
    }

    public static function verify(string $token): ?array
    {
        $db = Database::getInstance();
        $tokenHash = hash('sha256', $token);

        $stmt = $db->prepare('
            SELECT rt.*, u.email, u.first_name, u.last_name, u.is_active
            FROM refresh_tokens rt
            INNER JOIN users u ON rt.user_id = u.id
            WHERE rt.token_hash = :token_hash
              AND rt.expires_at > NOW()
        ');
        $stmt->execute(['token_hash' => $tokenHash]);
        $result = $stmt->fetch();

        if (!$result) {
            return null;
        }

        if (!$result['is_active']) {
            return null;
        }

        return $result;
    }

    public static function revoke(string $token): bool
    {
        $db = Database::getInstance();
        $tokenHash = hash('sha256', $token);
        $stmt = $db->prepare('DELETE FROM refresh_tokens WHERE token_hash = :token_hash');
        return $stmt->execute(['token_hash' => $tokenHash]);
    }

    public static function rotate(string $oldToken, string $ipAddress = null, string $userAgent = null): ?string
    {
        $tokenData = self::verify($oldToken);

        if (!$tokenData) {
            return null;
        }

        // Revoke old token
        self::revoke($oldToken);

        // Create new token
        return self::create($tokenData['user_id'], $ipAddress, $userAgent);
    }

    public static function deleteAllForUser(string $userId): void
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('DELETE FROM refresh_tokens WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
    }

    public static function cleanup(): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('DELETE FROM refresh_tokens WHERE expires_at < NOW()');
        $stmt->execute();
        return $stmt->rowCount();
    }
}
