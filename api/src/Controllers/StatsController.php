<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use App\Middleware\AuthMiddleware;
use App\Services\AuditService;
use App\Utils\Response;

class StatsController
{
    public function dashboard(): void
    {
        AuthMiddleware::requireAdmin();

        $db = Database::getInstance();

        // Total counts
        $stats = [
            'users' => [
                'total' => 0,
                'active' => 0,
                'admins' => 0
            ],
            'persons' => [
                'total' => 0
            ],
            'sessions' => [
                'total' => 0,
                'this_month' => 0,
                'last_30_days' => 0
            ],
            'sensory_proposals' => [
                'total' => 0,
                'by_type' => []
            ],
            'recent_activity' => []
        ];

        // Users stats
        $stmt = $db->query('SELECT COUNT(*) FROM users');
        $stats['users']['total'] = (int)$stmt->fetchColumn();

        $stmt = $db->query('SELECT COUNT(*) FROM users WHERE is_active = 1');
        $stats['users']['active'] = (int)$stmt->fetchColumn();

        $stmt = $db->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
        $stats['users']['admins'] = (int)$stmt->fetchColumn();

        // Persons stats
        $stmt = $db->query('SELECT COUNT(*) FROM persons');
        $stats['persons']['total'] = (int)$stmt->fetchColumn();

        // Sessions stats
        $stmt = $db->query('SELECT COUNT(*) FROM sessions');
        $stats['sessions']['total'] = (int)$stmt->fetchColumn();

        $stmt = $db->query("SELECT COUNT(*) FROM sessions WHERE MONTH(session_date) = MONTH(CURRENT_DATE()) AND YEAR(session_date) = YEAR(CURRENT_DATE())");
        $stats['sessions']['this_month'] = (int)$stmt->fetchColumn();

        $stmt = $db->query("SELECT COUNT(*) FROM sessions WHERE session_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $stats['sessions']['last_30_days'] = (int)$stmt->fetchColumn();

        // Average session duration
        $stmt = $db->query("SELECT AVG(duration_minutes) FROM sessions");
        $stats['sessions']['avg_duration'] = round((float)$stmt->fetchColumn(), 1);

        // Sensory proposals stats
        $stmt = $db->query('SELECT COUNT(*) FROM sensory_proposals');
        $stats['sensory_proposals']['total'] = (int)$stmt->fetchColumn();

        $stmt = $db->query('SELECT type, COUNT(*) as count FROM sensory_proposals GROUP BY type ORDER BY count DESC');
        $stats['sensory_proposals']['by_type'] = $stmt->fetchAll();

        // Sessions by month (last 6 months)
        $stmt = $db->query("
            SELECT
                DATE_FORMAT(session_date, '%Y-%m') as month,
                COUNT(*) as count
            FROM sessions
            WHERE session_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(session_date, '%Y-%m')
            ORDER BY month DESC
        ");
        $stats['sessions']['by_month'] = $stmt->fetchAll();

        // Behavior distribution (end of session)
        $stmt = $db->query("
            SELECT behavior_end, COUNT(*) as count
            FROM sessions
            WHERE behavior_end IS NOT NULL
            GROUP BY behavior_end
            ORDER BY count DESC
        ");
        $stats['sessions']['behavior_distribution'] = $stmt->fetchAll();

        // Wants to return stats
        $stmt = $db->query("
            SELECT
                SUM(CASE WHEN wants_to_return = 1 THEN 1 ELSE 0 END) as yes,
                SUM(CASE WHEN wants_to_return = 0 THEN 1 ELSE 0 END) as no,
                SUM(CASE WHEN wants_to_return IS NULL THEN 1 ELSE 0 END) as not_specified
            FROM sessions
        ");
        $stats['sessions']['wants_to_return'] = $stmt->fetch();

        // Recent activity (last 10 audit logs)
        $stats['recent_activity'] = AuditService::getRecent(10);

        Response::success($stats);
    }

    public function auditLogs(): void
    {
        AuthMiddleware::requireAdmin();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = min(100, max(1, (int)($_GET['limit'] ?? 50)));
        $offset = ($page - 1) * $limit;

        $logs = AuditService::getRecent($limit, $offset);

        $db = Database::getInstance();
        $stmt = $db->query('SELECT COUNT(*) FROM audit_logs');
        $total = (int)$stmt->fetchColumn();

        Response::success([
            'logs' => $logs,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ]);
    }
}
