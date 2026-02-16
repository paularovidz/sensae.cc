-- Migration: Prepaid Packs System
-- Allows clients to purchase session packages in advance

-- =========================================================================
-- TABLES
-- =========================================================================

-- Prepaid packs table
CREATE TABLE IF NOT EXISTS prepaid_packs (
    id CHAR(36) PRIMARY KEY,
    user_id CHAR(36) NOT NULL,
    pack_type VARCHAR(50) NOT NULL,              -- 'pack_5', 'pack_10'
    sessions_total INT UNSIGNED NOT NULL,         -- Number of sessions purchased
    sessions_used INT UNSIGNED DEFAULT 0,         -- Sessions consumed
    price_paid DECIMAL(10,2) NOT NULL,           -- Price paid for the pack
    duration_type ENUM('regular', 'discovery', 'any') DEFAULT 'any',
    expires_at DATETIME DEFAULT NULL,            -- Optional expiration
    purchased_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    admin_notes TEXT DEFAULT NULL,
    created_by CHAR(36) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,

    INDEX idx_prepaid_packs_user (user_id),
    INDEX idx_prepaid_packs_active (is_active),
    INDEX idx_prepaid_packs_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Pack usage tracking for audit trail
CREATE TABLE IF NOT EXISTS prepaid_pack_usages (
    id CHAR(36) PRIMARY KEY,
    pack_id CHAR(36) NOT NULL,
    session_id CHAR(36) NOT NULL,
    used_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uk_session (session_id),
    FOREIGN KEY (pack_id) REFERENCES prepaid_packs(id) ON DELETE CASCADE,
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE,

    INDEX idx_pack_usages_pack (pack_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- ALTER SESSIONS TABLE
-- =========================================================================

-- Add prepaid_pack_id to sessions
ALTER TABLE sessions
ADD COLUMN prepaid_pack_id CHAR(36) DEFAULT NULL AFTER promo_code_id,
ADD FOREIGN KEY fk_sessions_prepaid_pack (prepaid_pack_id) REFERENCES prepaid_packs(id) ON DELETE SET NULL;

-- =========================================================================
-- SETTINGS
-- =========================================================================

-- Pack 5 sessions
INSERT INTO settings (`key`, `value`, `type`, `label`, `description`, `category`, created_at)
VALUES ('prepaid_pack_5_sessions', '5', 'integer', 'Nombre de séances - Pack 5', 'Nombre de séances incluses dans le pack 5', 'prepaid', NOW())
ON DUPLICATE KEY UPDATE `key` = `key`;

INSERT INTO settings (`key`, `value`, `type`, `label`, `description`, `category`, created_at)
VALUES ('prepaid_pack_5_price', '200', 'integer', 'Prix Pack 5 séances', 'Prix du pack de 5 séances régulières (en euros)', 'prepaid', NOW())
ON DUPLICATE KEY UPDATE `key` = `key`;

-- Pack 10 sessions
INSERT INTO settings (`key`, `value`, `type`, `label`, `description`, `category`, created_at)
VALUES ('prepaid_pack_10_sessions', '10', 'integer', 'Nombre de séances - Pack 10', 'Nombre de séances incluses dans le pack 10', 'prepaid', NOW())
ON DUPLICATE KEY UPDATE `key` = `key`;

INSERT INTO settings (`key`, `value`, `type`, `label`, `description`, `category`, created_at)
VALUES ('prepaid_pack_10_price', '380', 'integer', 'Prix Pack 10 séances', 'Prix du pack de 10 séances régulières (en euros)', 'prepaid', NOW())
ON DUPLICATE KEY UPDATE `key` = `key`;

-- Expiration setting
INSERT INTO settings (`key`, `value`, `type`, `label`, `description`, `category`, created_at)
VALUES ('prepaid_default_expiry_months', '12', 'integer', 'Expiration packs (mois)', 'Nombre de mois avant expiration des packs prépayés (0 = jamais)', 'prepaid', NOW())
ON DUPLICATE KEY UPDATE `key` = `key`;
