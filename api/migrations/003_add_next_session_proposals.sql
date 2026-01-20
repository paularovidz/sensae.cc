-- Migration 003: Add next_session_proposals field
-- Propositions pour une prochaine séance

ALTER TABLE `sessions`
ADD COLUMN `next_session_proposals` TEXT DEFAULT NULL COMMENT 'Encrypted - Propositions pour une prochaine séance'
AFTER `person_expression`;
