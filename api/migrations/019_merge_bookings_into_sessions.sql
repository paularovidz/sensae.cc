-- Migration 019: Fusion des tables bookings et sessions
-- Les bookings deviennent des sessions avec status pending/confirmed
-- Une session suit le cycle: pending -> confirmed -> completed (ou cancelled/no_show)

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- Étape 1: Ajouter les champs booking à la table sessions
-- --------------------------------------------------------

-- User ID (client qui a réservé)
ALTER TABLE sessions ADD COLUMN user_id CHAR(36) DEFAULT NULL AFTER booking_id;
ALTER TABLE sessions ADD KEY idx_sessions_user (user_id);
ALTER TABLE sessions ADD CONSTRAINT fk_sessions_client_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;

-- Type de durée et durée bloquée
ALTER TABLE sessions ADD COLUMN duration_type ENUM('discovery', 'regular') DEFAULT 'regular' AFTER duration_minutes;
ALTER TABLE sessions ADD COLUMN duration_blocked_minutes INT UNSIGNED DEFAULT NULL AFTER duration_type;

-- Prix
ALTER TABLE sessions ADD COLUMN price DECIMAL(10,2) DEFAULT NULL AFTER duration_blocked_minutes;

-- Status (remplace le booléen implicite "complété")
ALTER TABLE sessions ADD COLUMN status ENUM('pending', 'confirmed', 'completed', 'cancelled', 'no_show') DEFAULT 'completed' AFTER price;
ALTER TABLE sessions ADD KEY idx_sessions_status (status);

-- Confirmation
ALTER TABLE sessions ADD COLUMN confirmation_token VARCHAR(255) DEFAULT NULL AFTER status;
ALTER TABLE sessions ADD COLUMN confirmed_at DATETIME DEFAULT NULL AFTER confirmation_token;
ALTER TABLE sessions ADD UNIQUE KEY idx_sessions_token (confirmation_token);

-- RGPD
ALTER TABLE sessions ADD COLUMN gdpr_consent TINYINT(1) DEFAULT 0 AFTER confirmed_at;
ALTER TABLE sessions ADD COLUMN gdpr_consent_at DATETIME DEFAULT NULL AFTER gdpr_consent;

-- Notes admin
ALTER TABLE sessions ADD COLUMN admin_notes TEXT DEFAULT NULL AFTER gdpr_consent_at;

-- Rappels
ALTER TABLE sessions ADD COLUMN reminder_sms_sent_at DATETIME DEFAULT NULL AFTER admin_notes;
ALTER TABLE sessions ADD COLUMN reminder_email_sent_at DATETIME DEFAULT NULL AFTER reminder_sms_sent_at;

-- Métadonnées de réservation
ALTER TABLE sessions ADD COLUMN ip_address VARCHAR(45) DEFAULT NULL AFTER reminder_email_sent_at;
ALTER TABLE sessions ADD COLUMN user_agent VARCHAR(500) DEFAULT NULL AFTER ip_address;

-- --------------------------------------------------------
-- Étape 2: Migrer les données des bookings vers sessions
-- --------------------------------------------------------

-- 2a. Pour les bookings qui ont une session liée (completed)
-- Mettre à jour la session avec les infos du booking
UPDATE sessions s
INNER JOIN bookings b ON s.booking_id = b.id
SET s.user_id = b.user_id,
    s.duration_type = b.duration_type,
    s.duration_blocked_minutes = b.duration_blocked_minutes,
    s.price = b.price,
    s.status = 'completed',
    s.confirmation_token = b.confirmation_token,
    s.confirmed_at = b.confirmed_at,
    s.gdpr_consent = b.gdpr_consent,
    s.gdpr_consent_at = b.gdpr_consent_at,
    s.admin_notes = b.admin_notes,
    s.reminder_sms_sent_at = b.reminder_sms_sent_at,
    s.reminder_email_sent_at = b.reminder_email_sent_at,
    s.ip_address = b.ip_address,
    s.user_agent = b.user_agent
WHERE b.session_id IS NOT NULL;

-- 2b. Pour les bookings sans session (pending, confirmed, cancelled, no_show)
-- Créer de nouvelles entrées dans sessions
INSERT INTO sessions (
    id, booking_id, user_id, person_id, created_by,
    session_date, duration_minutes, duration_type, duration_blocked_minutes,
    price, status, confirmation_token, confirmed_at,
    gdpr_consent, gdpr_consent_at, admin_notes,
    reminder_sms_sent_at, reminder_email_sent_at,
    ip_address, user_agent, created_at, updated_at
)
SELECT
    b.id, -- Utiliser le même ID que le booking pour garder les références SMS
    NULL, -- booking_id sera supprimé
    b.user_id,
    b.person_id,
    COALESCE(b.user_id, (SELECT id FROM users WHERE role = 'admin' LIMIT 1)), -- created_by
    b.session_date,
    b.duration_display_minutes,
    b.duration_type,
    b.duration_blocked_minutes,
    b.price,
    b.status,
    b.confirmation_token,
    b.confirmed_at,
    b.gdpr_consent,
    b.gdpr_consent_at,
    b.admin_notes,
    b.reminder_sms_sent_at,
    b.reminder_email_sent_at,
    b.ip_address,
    b.user_agent,
    b.created_at,
    b.updated_at
FROM bookings b
WHERE b.session_id IS NULL;

-- --------------------------------------------------------
-- Étape 3: Mettre à jour sms_logs pour référencer sessions
-- --------------------------------------------------------

-- Renommer la colonne booking_id en session_id
ALTER TABLE sms_logs DROP FOREIGN KEY fk_sms_logs_booking;
ALTER TABLE sms_logs CHANGE COLUMN booking_id session_id CHAR(36) DEFAULT NULL;
ALTER TABLE sms_logs ADD CONSTRAINT fk_sms_logs_session FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE SET NULL;

-- --------------------------------------------------------
-- Étape 4: Nettoyer
-- --------------------------------------------------------

-- Supprimer la colonne booking_id de sessions (plus nécessaire)
ALTER TABLE sessions DROP FOREIGN KEY fk_sessions_booking;
ALTER TABLE sessions DROP KEY idx_sessions_booking;
ALTER TABLE sessions DROP COLUMN booking_id;

-- Supprimer la table bookings
DROP TABLE bookings;

-- --------------------------------------------------------
-- Étape 5: Mettre à jour les sessions existantes sans booking
-- --------------------------------------------------------

-- Pour les anciennes sessions sans booking, leur mettre status = 'completed'
UPDATE sessions SET status = 'completed' WHERE status IS NULL;

-- Générer des tokens pour les sessions qui n'en ont pas (pour l'unicité)
-- Utiliser une procédure pour éviter les doublons
UPDATE sessions SET confirmation_token = CONCAT('legacy-', id) WHERE confirmation_token IS NULL;

SET FOREIGN_KEY_CHECKS = 1;
