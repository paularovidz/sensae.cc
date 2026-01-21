#!/usr/bin/env php
<?php
// Tâches planifiées pour le système de réservation
//
// À exécuter via cron, par exemple :
//   Toutes les 15 min : 0,15,30,45 * * * * php booking-tasks.php >> /var/log/sensea-booking.log 2>&1
//
// Ou pour des tâches spécifiques :
//   Envoi des rappels (tous les jours à 18h) :
//     0 18 * * * php booking-tasks.php send-reminders
//
//   Rafraîchissement du cache calendrier (toutes les 5 minutes) :
//     */5 * * * * php booking-tasks.php refresh-calendar
//
//   Nettoyage complet (une fois par jour à 3h) :
//     0 3 * * * php booking-tasks.php cleanup
//     (magic links > 35j, refresh tokens > 30j, sessions pending > 24h)

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Models\Session;
use App\Models\RefreshToken;
use App\Services\CalendarService;
use App\Services\BookingMailService;
use App\Services\SMSService;

// Load environment variables
try {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
} catch (Exception $e) {
    // Continue without .env file (Docker env vars)
}

// Determine task to run
$task = $argv[1] ?? 'all';

echo "[" . date('Y-m-d H:i:s') . "] Starting booking tasks: {$task}\n";

switch ($task) {
    case 'send-reminders':
        sendReminders();
        break;

    case 'refresh-calendar':
        refreshCalendarCache();
        break;

    case 'cleanup-expired':
        cleanupExpiredPendingSessions();
        break;

    case 'cleanup-magic-links':
        cleanupOldMagicLinks();
        break;

    case 'cleanup-tokens':
        cleanupOldRefreshTokens();
        break;

    case 'cleanup':
        cleanupExpiredPendingSessions();
        cleanupOldMagicLinks();
        cleanupOldRefreshTokens();
        break;

    case 'all':
        refreshCalendarCache();
        sendReminders();
        cleanupExpiredPendingSessions();
        cleanupOldMagicLinks();
        cleanupOldRefreshTokens();
        break;

    default:
        echo "Unknown task: {$task}\n";
        echo "Available tasks: send-reminders, refresh-calendar, cleanup-expired, cleanup-magic-links, cleanup-tokens, cleanup, all\n";
        exit(1);
}

echo "[" . date('Y-m-d H:i:s') . "] Tasks completed.\n";

// ========================================
// TASK FUNCTIONS
// ========================================

/**
 * Envoie les rappels SMS et email pour les sessions de demain
 */
function sendReminders(): void
{
    echo "  -> Sending reminders for tomorrow's sessions...\n";

    $sessions = Session::getPendingReminders();

    if (empty($sessions)) {
        echo "     No reminders to send.\n";
        return;
    }

    $smsSent = 0;
    $emailSent = 0;
    $errors = 0;

    $mailService = new BookingMailService();

    foreach ($sessions as $session) {
        try {
            // Envoyer SMS si configuré et téléphone disponible
            if (SMSService::isConfigured() && !empty($session['client_phone'])) {
                if (SMSService::sendReminder($session)) {
                    Session::update($session['id'], [
                        'reminder_sms_sent_at' => (new DateTime())->format('Y-m-d H:i:s')
                    ]);
                    $smsSent++;
                    echo "     SMS sent to {$session['client_phone']}\n";
                }
            }

            // Envoyer email de rappel
            if ($mailService->sendReminderEmail($session)) {
                Session::update($session['id'], [
                    'reminder_email_sent_at' => (new DateTime())->format('Y-m-d H:i:s')
                ]);
                $emailSent++;
                echo "     Email sent to {$session['client_email']}\n";
            }

        } catch (Exception $e) {
            echo "     ERROR sending reminder for session {$session['id']}: {$e->getMessage()}\n";
            $errors++;
        }
    }

    echo "     SMS sent: {$smsSent}, Emails sent: {$emailSent}, Errors: {$errors}\n";
}

/**
 * Rafraîchit le cache du calendrier Google
 */
function refreshCalendarCache(): void
{
    echo "  -> Refreshing calendar cache...\n";

    try {
        if (CalendarService::refreshCache()) {
            echo "     Calendar cache refreshed successfully.\n";
        } else {
            echo "     WARNING: Calendar cache refresh returned false.\n";
        }
    } catch (Exception $e) {
        echo "     ERROR refreshing calendar cache: {$e->getMessage()}\n";
    }
}

/**
 * Nettoie les sessions en attente (pending depuis plus de 24h)
 */
function cleanupExpiredPendingSessions(): void
{
    echo "  -> Cleaning up expired pending sessions...\n";

    try {
        $db = \App\Config\Database::getInstance();

        // Trouver les sessions pending créées il y a plus de 24h
        $stmt = $db->prepare("
            SELECT s.id, u.email as client_email
            FROM sessions s
            LEFT JOIN users u ON s.user_id = u.id
            WHERE s.status = :pending
            AND s.created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        $stmt->execute(['pending' => Session::STATUS_PENDING]);
        $expiredSessions = $stmt->fetchAll();

        if (empty($expiredSessions)) {
            echo "     No expired sessions to cleanup.\n";
            return;
        }

        $cancelled = 0;
        foreach ($expiredSessions as $session) {
            Session::cancel($session['id']);
            echo "     Cancelled expired session {$session['id']} ({$session['client_email']})\n";
            $cancelled++;
        }

        echo "     Cancelled {$cancelled} expired session(s).\n";

    } catch (Exception $e) {
        echo "     ERROR cleaning up expired sessions: {$e->getMessage()}\n";
    }
}

/**
 * Supprime les magic links de plus de 35 jours
 */
function cleanupOldMagicLinks(): void
{
    echo "  -> Cleaning up old magic links...\n";

    try {
        $db = \App\Config\Database::getInstance();

        // Supprimer les magic links de plus de 35 jours
        $stmt = $db->prepare('
            DELETE FROM magic_links
            WHERE created_at < DATE_SUB(NOW(), INTERVAL 35 DAY)
        ');
        $stmt->execute();
        $deleted = $stmt->rowCount();

        if ($deleted > 0) {
            echo "     Deleted {$deleted} old magic link(s).\n";
        } else {
            echo "     No old magic links to cleanup.\n";
        }

    } catch (Exception $e) {
        echo "     ERROR cleaning up old magic links: {$e->getMessage()}\n";
    }
}

/**
 * Supprime les refresh tokens expirés/révoqués de plus de 30 jours
 */
function cleanupOldRefreshTokens(): void
{
    echo "  -> Cleaning up old refresh tokens...\n";

    try {
        $deleted = RefreshToken::cleanup();

        if ($deleted > 0) {
            echo "     Deleted {$deleted} old refresh token(s).\n";
        } else {
            echo "     No old refresh tokens to cleanup.\n";
        }

    } catch (Exception $e) {
        echo "     ERROR cleaning up old refresh tokens: {$e->getMessage()}\n";
    }
}
