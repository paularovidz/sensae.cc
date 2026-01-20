-- Suivi Snoezelen - Initial Schema
-- Migration 001

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- Table: users (Comptes professionnels)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` CHAR(36) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `login` VARCHAR(100) NOT NULL,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `role` ENUM('member', 'admin') NOT NULL DEFAULT 'member',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_users_email` (`email`),
    UNIQUE KEY `idx_users_login` (`login`),
    KEY `idx_users_role` (`role`),
    KEY `idx_users_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: persons (Personnes suivies)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `persons` (
    `id` CHAR(36) NOT NULL,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `birth_date` DATE DEFAULT NULL,
    `notes` TEXT DEFAULT NULL COMMENT 'Encrypted',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_persons_name` (`last_name`, `first_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: user_persons (Liaison N-N users <-> persons)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `user_persons` (
    `user_id` CHAR(36) NOT NULL,
    `person_id` CHAR(36) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`, `person_id`),
    KEY `idx_user_persons_person` (`person_id`),
    CONSTRAINT `fk_user_persons_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_user_persons_person` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: sensory_proposals (Propositions sensorielles)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sensory_proposals` (
    `id` CHAR(36) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `type` ENUM('tactile', 'visual', 'olfactory', 'gustatory', 'auditory', 'proprioceptive') NOT NULL,
    `description` TEXT DEFAULT NULL,
    `created_by` CHAR(36) NOT NULL,
    `is_global` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_sensory_proposals_type` (`type`),
    KEY `idx_sensory_proposals_global` (`is_global`),
    KEY `idx_sensory_proposals_created_by` (`created_by`),
    FULLTEXT KEY `idx_sensory_proposals_search` (`title`, `description`),
    CONSTRAINT `fk_sensory_proposals_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: sessions (Séances Snoezelen)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sessions` (
    `id` CHAR(36) NOT NULL,
    `person_id` CHAR(36) NOT NULL,
    `created_by` CHAR(36) NOT NULL,
    `session_date` DATETIME NOT NULL,
    `duration_minutes` INT UNSIGNED NOT NULL,
    `sessions_per_month` INT UNSIGNED DEFAULT NULL,

    -- Début de séance
    `behavior_start` ENUM('calm', 'agitated', 'defensive', 'anxious', 'passive') DEFAULT NULL,
    `proposal_origin` ENUM('person', 'relative') DEFAULT NULL,
    `attitude_start` ENUM('accepts', 'indifferent', 'refuses') DEFAULT NULL,

    -- Pendant la séance
    `position` ENUM('standing', 'lying', 'sitting', 'moving') DEFAULT NULL,
    `communication` JSON DEFAULT NULL COMMENT 'Array of: body, verbal, vocal',

    -- Fin de séance
    `session_end` ENUM('accepts', 'refuses', 'interrupts') DEFAULT NULL,
    `behavior_end` ENUM('calm', 'agitated', 'tired', 'defensive', 'anxious', 'passive') DEFAULT NULL,
    `wants_to_return` TINYINT(1) DEFAULT NULL,

    -- Notes privées (chiffrées)
    `professional_notes` TEXT DEFAULT NULL COMMENT 'Encrypted - Impressions du professionnel',
    `person_expression` TEXT DEFAULT NULL COMMENT 'Encrypted - Impressions et expression de la personne',

    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_sessions_person` (`person_id`),
    KEY `idx_sessions_created_by` (`created_by`),
    KEY `idx_sessions_date` (`session_date`),
    CONSTRAINT `fk_sessions_person` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_sessions_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: session_proposals (Propositions utilisées dans une séance)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `session_proposals` (
    `id` CHAR(36) NOT NULL,
    `session_id` CHAR(36) NOT NULL,
    `sensory_proposal_id` CHAR(36) NOT NULL,
    `appreciation` ENUM('negative', 'neutral', 'positive') DEFAULT NULL,
    `order` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_session_proposals_session` (`session_id`),
    KEY `idx_session_proposals_proposal` (`sensory_proposal_id`),
    CONSTRAINT `fk_session_proposals_session` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_session_proposals_proposal` FOREIGN KEY (`sensory_proposal_id`) REFERENCES `sensory_proposals` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: magic_links (Liens de connexion)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `magic_links` (
    `id` CHAR(36) NOT NULL,
    `user_id` CHAR(36) NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `used_at` DATETIME DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_magic_links_token` (`token`),
    KEY `idx_magic_links_user` (`user_id`),
    KEY `idx_magic_links_expires` (`expires_at`),
    CONSTRAINT `fk_magic_links_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: refresh_tokens (Tokens de rafraîchissement)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `refresh_tokens` (
    `id` CHAR(36) NOT NULL,
    `user_id` CHAR(36) NOT NULL,
    `token_hash` VARCHAR(255) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `revoked_at` DATETIME DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` VARCHAR(500) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_refresh_tokens_user` (`user_id`),
    KEY `idx_refresh_tokens_hash` (`token_hash`),
    KEY `idx_refresh_tokens_expires` (`expires_at`),
    CONSTRAINT `fk_refresh_tokens_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: audit_logs (Journaux d'audit)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` CHAR(36) NOT NULL,
    `user_id` CHAR(36) DEFAULT NULL,
    `action` VARCHAR(100) NOT NULL,
    `entity_type` VARCHAR(50) DEFAULT NULL,
    `entity_id` CHAR(36) DEFAULT NULL,
    `old_values` JSON DEFAULT NULL,
    `new_values` JSON DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` VARCHAR(500) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_audit_logs_user` (`user_id`),
    KEY `idx_audit_logs_action` (`action`),
    KEY `idx_audit_logs_entity` (`entity_type`, `entity_id`),
    KEY `idx_audit_logs_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
