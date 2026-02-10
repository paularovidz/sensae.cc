<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use App\Utils\UUID;
use PDO;

class User
{
    public static function findById(string $id): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT id, email, first_name, last_name, is_active, last_login_at, created_at FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function findByEmail(string $email): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => strtolower(trim($email))]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function getAll(): array
    {
        $db = Database::getInstance();
        $stmt = $db->query('SELECT id, email, first_name, last_name, is_active, last_login_at, created_at FROM users ORDER BY created_at DESC');
        return $stmt->fetchAll();
    }

    public static function create(array $data): string
    {
        $db = Database::getInstance();
        $id = UUID::generate();

        $stmt = $db->prepare('
            INSERT INTO users (id, email, first_name, last_name, is_active)
            VALUES (:id, :email, :first_name, :last_name, :is_active)
        ');

        $stmt->execute([
            'id' => $id,
            'email' => strtolower(trim($data['email'])),
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'is_active' => $data['is_active'] ?? 1
        ]);

        return $id;
    }

    public static function update(string $id, array $data): bool
    {
        $db = Database::getInstance();
        $sets = [];
        $params = ['id' => $id];

        foreach (['email', 'first_name', 'last_name', 'is_active'] as $field) {
            if (isset($data[$field])) {
                $sets[] = "{$field} = :{$field}";
                $params[$field] = $field === 'email' ? strtolower(trim($data[$field])) : $data[$field];
            }
        }

        if (empty($sets)) {
            return false;
        }

        $sql = "UPDATE users SET " . implode(', ', $sets) . " WHERE id = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    }

    public static function updateLastLogin(string $id): void
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public static function delete(string $id): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('DELETE FROM users WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
