<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use App\Utils\UUID;
use App\Utils\Encryption;

class Person
{
    public static function findById(string $id): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM persons WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $person = $stmt->fetch();

        if ($person) {
            $person['notes'] = Encryption::decrypt($person['notes']);
        }

        return $person ?: null;
    }

    public static function findAll(int $limit = 100, int $offset = 0, ?string $search = null): array
    {
        $db = Database::getInstance();

        $sql = 'SELECT * FROM persons';

        if ($search !== null && $search !== '') {
            $sql .= ' WHERE (
                first_name LIKE :s1
                OR last_name LIKE :s2
                OR CONCAT(first_name, " ", last_name) LIKE :s3
            )';
        }

        $sql .= ' ORDER BY last_name, first_name LIMIT :limit OFFSET :offset';

        $stmt = $db->prepare($sql);
        if ($search !== null && $search !== '') {
            $searchPattern = '%' . $search . '%';
            $stmt->bindValue(':s1', $searchPattern);
            $stmt->bindValue(':s2', $searchPattern);
            $stmt->bindValue(':s3', $searchPattern);
        }
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $persons = $stmt->fetchAll();

        foreach ($persons as &$person) {
            $person['notes'] = Encryption::decrypt($person['notes']);
        }

        return $persons;
    }

    public static function findByUser(string $userId, int $limit = 100, int $offset = 0, ?string $search = null): array
    {
        $db = Database::getInstance();

        $sql = '
            SELECT p.*
            FROM persons p
            INNER JOIN user_persons up ON p.id = up.person_id
            WHERE up.user_id = :user_id
        ';

        if ($search !== null && $search !== '') {
            $sql .= ' AND (
                p.first_name LIKE :s1
                OR p.last_name LIKE :s2
                OR CONCAT(p.first_name, " ", p.last_name) LIKE :s3
            )';
        }

        $sql .= ' ORDER BY p.last_name, p.first_name LIMIT :limit OFFSET :offset';

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        if ($search !== null && $search !== '') {
            $searchPattern = '%' . $search . '%';
            $stmt->bindValue(':s1', $searchPattern);
            $stmt->bindValue(':s2', $searchPattern);
            $stmt->bindValue(':s3', $searchPattern);
        }
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $persons = $stmt->fetchAll();

        foreach ($persons as &$person) {
            $person['notes'] = Encryption::decrypt($person['notes']);
        }

        return $persons;
    }

    public static function count(?string $search = null): int
    {
        $db = Database::getInstance();

        $sql = 'SELECT COUNT(*) FROM persons';

        if ($search !== null && $search !== '') {
            $sql .= ' WHERE (
                first_name LIKE :s1
                OR last_name LIKE :s2
                OR CONCAT(first_name, " ", last_name) LIKE :s3
            )';
        }

        $stmt = $db->prepare($sql);
        if ($search !== null && $search !== '') {
            $searchPattern = '%' . $search . '%';
            $stmt->bindValue(':s1', $searchPattern);
            $stmt->bindValue(':s2', $searchPattern);
            $stmt->bindValue(':s3', $searchPattern);
        }
        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    public static function countByUser(string $userId, ?string $search = null): int
    {
        $db = Database::getInstance();

        $sql = '
            SELECT COUNT(*) FROM persons p
            INNER JOIN user_persons up ON p.id = up.person_id
            WHERE up.user_id = :user_id
        ';

        if ($search !== null && $search !== '') {
            $sql .= ' AND (
                p.first_name LIKE :s1
                OR p.last_name LIKE :s2
                OR CONCAT(p.first_name, " ", p.last_name) LIKE :s3
            )';
        }

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        if ($search !== null && $search !== '') {
            $searchPattern = '%' . $search . '%';
            $stmt->bindValue(':s1', $searchPattern);
            $stmt->bindValue(':s2', $searchPattern);
            $stmt->bindValue(':s3', $searchPattern);
        }
        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    public static function create(array $data): string
    {
        $db = Database::getInstance();
        $id = UUID::generate();

        $stmt = $db->prepare('
            INSERT INTO persons (id, first_name, last_name, birth_date, notes, sessions_per_month)
            VALUES (:id, :first_name, :last_name, :birth_date, :notes, :sessions_per_month)
        ');

        $stmt->execute([
            'id' => $id,
            'first_name' => trim($data['first_name']),
            'last_name' => trim($data['last_name']),
            'birth_date' => $data['birth_date'] ?? null,
            'notes' => Encryption::encrypt($data['notes'] ?? null),
            'sessions_per_month' => $data['sessions_per_month'] ?? null
        ]);

        return $id;
    }

    public static function update(string $id, array $data): bool
    {
        $db = Database::getInstance();

        $fields = [];
        $params = ['id' => $id];

        $allowedFields = ['first_name', 'last_name', 'birth_date', 'notes', 'sessions_per_month'];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = :{$field}";
                $value = $data[$field];

                if ($field === 'notes') {
                    $value = Encryption::encrypt($value);
                } elseif (is_string($value)) {
                    $value = trim($value);
                }

                $params[$field] = $value;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $sql = 'UPDATE persons SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $db->prepare($sql);

        return $stmt->execute($params);
    }

    public static function delete(string $id): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('DELETE FROM persons WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public static function assignToUser(string $personId, string $userId): bool
    {
        $db = Database::getInstance();

        // Check if already assigned
        $stmt = $db->prepare('
            SELECT COUNT(*) FROM user_persons
            WHERE user_id = :user_id AND person_id = :person_id
        ');
        $stmt->execute(['user_id' => $userId, 'person_id' => $personId]);

        if ((int)$stmt->fetchColumn() > 0) {
            return true; // Already assigned
        }

        $stmt = $db->prepare('
            INSERT INTO user_persons (user_id, person_id)
            VALUES (:user_id, :person_id)
        ');

        return $stmt->execute(['user_id' => $userId, 'person_id' => $personId]);
    }

    public static function unassignFromUser(string $personId, string $userId): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('
            DELETE FROM user_persons
            WHERE user_id = :user_id AND person_id = :person_id
        ');

        return $stmt->execute(['user_id' => $userId, 'person_id' => $personId]);
    }

    public static function getAssignedUsers(string $personId): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('
            SELECT u.id, u.email, u.first_name, u.last_name
            FROM users u
            INNER JOIN user_persons up ON u.id = up.user_id
            WHERE up.person_id = :person_id
            ORDER BY u.last_name, u.first_name
        ');
        $stmt->execute(['person_id' => $personId]);

        return $stmt->fetchAll();
    }

    public static function isAssignedToUser(string $personId, string $userId): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('
            SELECT COUNT(*) FROM user_persons
            WHERE user_id = :user_id AND person_id = :person_id
        ');
        $stmt->execute(['user_id' => $userId, 'person_id' => $personId]);

        return (int)$stmt->fetchColumn() > 0;
    }

    public static function getAge(?string $birthDate): ?int
    {
        if (!$birthDate) {
            return null;
        }

        $birth = new \DateTime($birthDate);
        $now = new \DateTime();

        return $birth->diff($now)->y;
    }

    public static function withAge(array $person): array
    {
        $person['age'] = self::getAge($person['birth_date'] ?? null);
        return $person;
    }
}
