<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use App\Utils\UUID;
use App\Utils\Validator;
use App\Services\AvailabilityService;

/**
 * Modèle de gestion des réservations
 *
 * Les infos client et personne sont récupérées via JOINs avec users et persons.
 * Seuls user_id et person_id sont stockés dans bookings.
 */
class Booking
{
    // Statuts de réservation
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_NO_SHOW = 'no_show';

    // Types de durée
    public const TYPE_DISCOVERY = 'discovery';
    public const TYPE_REGULAR = 'regular';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
        self::STATUS_CANCELLED,
        self::STATUS_COMPLETED,
        self::STATUS_NO_SHOW
    ];

    public const TYPES = [
        self::TYPE_DISCOVERY,
        self::TYPE_REGULAR
    ];

    public const LABELS = [
        'status' => [
            'pending' => 'En attente de confirmation',
            'confirmed' => 'Confirmé',
            'cancelled' => 'Annulé',
            'completed' => 'Effectué',
            'no_show' => 'Absent'
        ],
        'duration_type' => [
            'discovery' => 'Séance découverte (1h15)',
            'regular' => 'Séance classique (45min)'
        ],
        'client_type' => [
            'personal' => 'Particulier',
            'association' => 'Association'
        ]
    ];

    /**
     * Colonnes SELECT communes pour les requêtes avec JOINs
     */
    private static function getSelectColumns(): string
    {
        return '
            b.id, b.user_id, b.person_id, b.session_id, b.session_date,
            b.duration_type, b.duration_display_minutes, b.duration_blocked_minutes,
            b.price, b.status, b.confirmation_token, b.confirmed_at,
            b.gdpr_consent, b.gdpr_consent_at, b.admin_notes,
            b.reminder_sms_sent_at, b.reminder_email_sent_at,
            b.ip_address, b.user_agent, b.created_at, b.updated_at,
            -- Infos client depuis users
            u.email AS client_email,
            u.phone AS client_phone,
            u.first_name AS client_first_name,
            u.last_name AS client_last_name,
            u.client_type,
            u.company_name,
            u.siret,
            -- Infos personne depuis persons
            p.first_name AS person_first_name,
            p.last_name AS person_last_name,
            -- Aliases supplémentaires pour rétro-compatibilité
            p.first_name AS linked_person_first_name,
            p.last_name AS linked_person_last_name,
            u.email AS user_email
        ';
    }

    /**
     * Trouve une réservation par son ID
     */
    public static function findById(string $id): ?array
    {
        $db = Database::getInstance();
        $columns = self::getSelectColumns();
        $stmt = $db->prepare("
            SELECT {$columns},
                   s.id as linked_session_id
            FROM bookings b
            LEFT JOIN users u ON b.user_id = u.id
            LEFT JOIN persons p ON b.person_id = p.id
            LEFT JOIN sessions s ON b.session_id = s.id
            WHERE b.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $booking = $stmt->fetch();

        return $booking ?: null;
    }

    /**
     * Trouve une réservation par son token de confirmation
     */
    public static function findByToken(string $token): ?array
    {
        $db = Database::getInstance();
        $columns = self::getSelectColumns();
        $stmt = $db->prepare("
            SELECT {$columns}
            FROM bookings b
            LEFT JOIN users u ON b.user_id = u.id
            LEFT JOIN persons p ON b.person_id = p.id
            WHERE b.confirmation_token = :token
        ");
        $stmt->execute(['token' => $token]);
        $booking = $stmt->fetch();

        return $booking ?: null;
    }

    /**
     * Trouve les réservations par email client (via user)
     */
    public static function findByEmail(string $email): array
    {
        $db = Database::getInstance();
        $columns = self::getSelectColumns();
        $stmt = $db->prepare("
            SELECT {$columns}
            FROM bookings b
            JOIN users u ON b.user_id = u.id
            LEFT JOIN persons p ON b.person_id = p.id
            WHERE u.email = :email
            ORDER BY b.session_date DESC
        ");
        $stmt->execute(['email' => strtolower($email)]);

        return $stmt->fetchAll();
    }

    /**
     * Trouve les personnes distinctes associées à un email (via user)
     */
    public static function findPersonsByEmail(string $email): array
    {
        $db = Database::getInstance();

        $stmt = $db->prepare('
            SELECT DISTINCT
                b.person_id,
                p.first_name,
                p.last_name,
                p.id as linked_person_id
            FROM bookings b
            JOIN users u ON b.user_id = u.id
            JOIN persons p ON b.person_id = p.id
            WHERE u.email = :email
            AND (b.status = :confirmed OR b.status = :completed)
            ORDER BY b.created_at DESC
        ');
        $stmt->execute([
            'email' => strtolower($email),
            'confirmed' => self::STATUS_CONFIRMED,
            'completed' => self::STATUS_COMPLETED
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Vérifie si un email existe dans les réservations (via user)
     */
    public static function emailExists(string $email): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('
            SELECT COUNT(*) FROM bookings b
            JOIN users u ON b.user_id = u.id
            WHERE u.email = :email
            AND (b.status = :confirmed OR b.status = :completed)
        ');
        $stmt->execute([
            'email' => strtolower($email),
            'confirmed' => self::STATUS_CONFIRMED,
            'completed' => self::STATUS_COMPLETED
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Trouve toutes les réservations avec filtres
     */
    public static function findAll(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $db = Database::getInstance();
        $columns = self::getSelectColumns();

        $where = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = 'b.status = :status';
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['date_from'])) {
            $where[] = 'b.session_date >= :date_from';
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = 'b.session_date <= :date_to';
            $params['date_to'] = $filters['date_to'];
        }

        if (!empty($filters['duration_type'])) {
            $where[] = 'b.duration_type = :duration_type';
            $params['duration_type'] = $filters['duration_type'];
        }

        if (!empty($filters['person_id'])) {
            $where[] = 'b.person_id = :person_id';
            $params['person_id'] = $filters['person_id'];
        }

        if (!empty($filters['no_session'])) {
            $where[] = 'b.session_id IS NULL';
        }

        if (!empty($filters['upcoming'])) {
            $where[] = 'b.session_date >= NOW()';
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $db->prepare("
            SELECT {$columns},
                   s.id as linked_session_id
            FROM bookings b
            LEFT JOIN users u ON b.user_id = u.id
            LEFT JOIN persons p ON b.person_id = p.id
            LEFT JOIN sessions s ON b.session_id = s.id
            {$whereClause}
            ORDER BY b.session_date DESC
            LIMIT :limit OFFSET :offset
        ");

        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Compte les réservations avec filtres
     */
    public static function count(array $filters = []): int
    {
        $db = Database::getInstance();

        $where = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = 'status = :status';
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['date_from'])) {
            $where[] = 'session_date >= :date_from';
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = 'session_date <= :date_to';
            $params['date_to'] = $filters['date_to'];
        }

        if (!empty($filters['person_id'])) {
            $where[] = 'person_id = :person_id';
            $params['person_id'] = $filters['person_id'];
        }

        if (!empty($filters['no_session'])) {
            $where[] = 'session_id IS NULL';
        }

        if (!empty($filters['upcoming'])) {
            $where[] = 'session_date >= NOW()';
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $db->prepare("SELECT COUNT(*) FROM bookings {$whereClause}");
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Crée une nouvelle réservation
     */
    public static function create(array $data): string
    {
        $db = Database::getInstance();
        $id = UUID::generate();
        $token = self::generateConfirmationToken();

        $durations = AvailabilityService::getDurations($data['duration_type']);

        $stmt = $db->prepare('
            INSERT INTO bookings (
                id, user_id, person_id, session_date, duration_type,
                duration_display_minutes, duration_blocked_minutes, price,
                status, confirmation_token,
                gdpr_consent, gdpr_consent_at,
                ip_address, user_agent
            ) VALUES (
                :id, :user_id, :person_id, :session_date, :duration_type,
                :duration_display_minutes, :duration_blocked_minutes, :price,
                :status, :confirmation_token,
                :gdpr_consent, :gdpr_consent_at,
                :ip_address, :user_agent
            )
        ');

        $stmt->execute([
            'id' => $id,
            'user_id' => $data['user_id'],
            'person_id' => $data['person_id'],
            'session_date' => $data['session_date'],
            'duration_type' => $data['duration_type'],
            'duration_display_minutes' => $durations['display'],
            'duration_blocked_minutes' => $durations['blocked'],
            'price' => $data['price'] ?? null,
            'status' => self::STATUS_PENDING,
            'confirmation_token' => $token,
            'gdpr_consent' => $data['gdpr_consent'] ? 1 : 0,
            'gdpr_consent_at' => $data['gdpr_consent'] ? (new \DateTime())->format('Y-m-d H:i:s') : null,
            'ip_address' => $data['ip_address'] ?? null,
            'user_agent' => $data['user_agent'] ?? null
        ]);

        return $id;
    }

    /**
     * Met à jour une réservation
     */
    public static function update(string $id, array $data): bool
    {
        $db = Database::getInstance();

        $fields = [];
        $params = ['id' => $id];

        $allowedFields = [
            'user_id', 'person_id', 'session_id', 'session_date', 'duration_type',
            'status', 'admin_notes', 'confirmed_at',
            'reminder_sms_sent_at', 'reminder_email_sent_at', 'price'
        ];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = :{$field}";
                $params[$field] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $sql = 'UPDATE bookings SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $db->prepare($sql);

        return $stmt->execute($params);
    }

    /**
     * Confirme une réservation
     */
    public static function confirm(string $id): bool
    {
        return self::update($id, [
            'status' => self::STATUS_CONFIRMED,
            'confirmed_at' => (new \DateTime())->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Annule une réservation
     */
    public static function cancel(string $id): bool
    {
        return self::update($id, [
            'status' => self::STATUS_CANCELLED
        ]);
    }

    /**
     * Marque une réservation comme effectuée et lie à une session
     */
    public static function complete(string $id, string $sessionId): bool
    {
        return self::update($id, [
            'status' => self::STATUS_COMPLETED,
            'session_id' => $sessionId
        ]);
    }

    /**
     * Marque une réservation comme no-show
     */
    public static function markNoShow(string $id): bool
    {
        return self::update($id, [
            'status' => self::STATUS_NO_SHOW
        ]);
    }

    /**
     * Supprime une réservation
     */
    public static function delete(string $id): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('DELETE FROM bookings WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Récupère les réservations pour une date donnée (pour calcul de disponibilité)
     */
    public static function getBookingsForDate(\DateTime $date): array
    {
        $db = Database::getInstance();
        $dateStr = $date->format('Y-m-d');

        $stmt = $db->prepare('
            SELECT id, session_date, duration_blocked_minutes, status
            FROM bookings
            WHERE DATE(session_date) = :date
            AND (status = :pending OR status = :confirmed)
            ORDER BY session_date
        ');
        $stmt->execute([
            'date' => $dateStr,
            'pending' => self::STATUS_PENDING,
            'confirmed' => self::STATUS_CONFIRMED
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Vérifie si un créneau est déjà réservé
     */
    public static function isSlotBooked(\DateTime $start, \DateTime $end): bool
    {
        $db = Database::getInstance();

        // On vérifie les réservations pending ou confirmed qui chevauchent le créneau
        $stmt = $db->prepare('
            SELECT COUNT(*) FROM bookings
            WHERE (status = :pending OR status = :confirmed)
            AND (
                (session_date < :end_time AND DATE_ADD(session_date, INTERVAL duration_blocked_minutes MINUTE) > :start_time)
            )
        ');
        $stmt->execute([
            'pending' => self::STATUS_PENDING,
            'confirmed' => self::STATUS_CONFIRMED,
            'start_time' => $start->format('Y-m-d H:i:s'),
            'end_time' => $end->format('Y-m-d H:i:s')
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Récupère les réservations confirmées pour une date (pour générer les Sessions)
     */
    public static function getConfirmedForDate(\DateTime $date): array
    {
        $db = Database::getInstance();
        $columns = self::getSelectColumns();
        $stmt = $db->prepare("
            SELECT {$columns},
                   u.id as linked_user_id
            FROM bookings b
            LEFT JOIN users u ON b.user_id = u.id
            LEFT JOIN persons p ON b.person_id = p.id
            WHERE DATE(b.session_date) = :date
            AND b.status = :confirmed
            AND b.session_id IS NULL
            ORDER BY b.session_date
        ");
        $stmt->execute([
            'date' => $date->format('Y-m-d'),
            'confirmed' => self::STATUS_CONFIRMED
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Récupère les réservations nécessitant un rappel
     * (confirmées, pour demain, rappel pas encore envoyé)
     */
    public static function getPendingReminders(): array
    {
        $db = Database::getInstance();
        $columns = self::getSelectColumns();
        $tomorrow = (new \DateTime('tomorrow'))->format('Y-m-d');

        $stmt = $db->prepare("
            SELECT {$columns}
            FROM bookings b
            JOIN users u ON b.user_id = u.id
            LEFT JOIN persons p ON b.person_id = p.id
            WHERE DATE(b.session_date) = :tomorrow
            AND b.status = :confirmed
            AND b.reminder_sms_sent_at IS NULL
            AND u.phone IS NOT NULL
            ORDER BY b.session_date
        ");
        $stmt->execute([
            'tomorrow' => $tomorrow,
            'confirmed' => self::STATUS_CONFIRMED
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Génère un token de confirmation sécurisé
     */
    public static function generateConfirmationToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Récupère le prix d'une séance selon son type
     */
    public static function getPriceForType(string $durationType): int
    {
        $key = $durationType === self::TYPE_DISCOVERY
            ? 'session_discovery_price'
            : 'session_regular_price';

        return Setting::getInteger($key, $durationType === self::TYPE_DISCOVERY ? 55 : 45);
    }

    /**
     * Compte les réservations à venir par IP
     */
    public static function countUpcomingByIp(string $ip): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('
            SELECT COUNT(*) FROM bookings
            WHERE ip_address = :ip
            AND session_date >= NOW()
            AND status IN (:pending, :confirmed)
        ');
        $stmt->execute([
            'ip' => $ip,
            'pending' => self::STATUS_PENDING,
            'confirmed' => self::STATUS_CONFIRMED
        ]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Compte les réservations à venir par email (via user)
     */
    public static function countUpcomingByEmail(string $email): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('
            SELECT COUNT(*) FROM bookings b
            JOIN users u ON b.user_id = u.id
            WHERE u.email = :email
            AND b.session_date >= NOW()
            AND b.status IN (:pending, :confirmed)
        ');
        $stmt->execute([
            'email' => strtolower($email),
            'pending' => self::STATUS_PENDING,
            'confirmed' => self::STATUS_CONFIRMED
        ]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Récupère les données de calendrier pour un mois (nombre de réservations par jour)
     */
    public static function getCalendarData(int $year, int $month): array
    {
        $db = Database::getInstance();

        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = sprintf('%04d-%02d-01', $month == 12 ? $year + 1 : $year, $month == 12 ? 1 : $month + 1);

        $stmt = $db->prepare('
            SELECT DATE(session_date) as date, COUNT(*) as count
            FROM bookings
            WHERE session_date >= :start_date
            AND session_date < :end_date
            AND (status = :pending OR status = :confirmed)
            GROUP BY DATE(session_date)
        ');
        $stmt->execute([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'pending' => self::STATUS_PENDING,
            'confirmed' => self::STATUS_CONFIRMED
        ]);

        $result = [];
        while ($row = $stmt->fetch()) {
            $result[$row['date']] = (int) $row['count'];
        }

        return $result;
    }

    /**
     * Statistiques des réservations
     */
    public static function getStats(): array
    {
        $db = Database::getInstance();

        // Réservations à venir
        $stmt = $db->query("
            SELECT COUNT(*) FROM bookings
            WHERE session_date >= NOW()
            AND (status = 'pending' OR status = 'confirmed')
        ");
        $upcoming = (int) $stmt->fetchColumn();

        // Réservations aujourd'hui
        $stmt = $db->query("
            SELECT COUNT(*) FROM bookings
            WHERE DATE(session_date) = CURDATE()
            AND (status = 'pending' OR status = 'confirmed')
        ");
        $today = (int) $stmt->fetchColumn();

        // En attente de confirmation
        $stmt = $db->query("
            SELECT COUNT(*) FROM bookings
            WHERE status = 'pending'
        ");
        $pending = (int) $stmt->fetchColumn();

        // Par statut ce mois
        $stmt = $db->query("
            SELECT status, COUNT(*) as count
            FROM bookings
            WHERE session_date >= DATE_FORMAT(NOW(), '%Y-%m-01')
            GROUP BY status
        ");
        $byStatus = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);

        return [
            'upcoming' => $upcoming,
            'today' => $today,
            'pending' => $pending,
            'by_status_this_month' => $byStatus
        ];
    }
}
