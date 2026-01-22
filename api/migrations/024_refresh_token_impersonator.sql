-- Migration: Add impersonator_id to refresh_tokens
-- Permet de préserver l'état d'impersonation côté serveur lors du refresh

ALTER TABLE refresh_tokens
ADD COLUMN impersonator_id CHAR(36) NULL AFTER user_id,
ADD CONSTRAINT fk_refresh_tokens_impersonator
    FOREIGN KEY (impersonator_id) REFERENCES users(id) ON DELETE CASCADE;
