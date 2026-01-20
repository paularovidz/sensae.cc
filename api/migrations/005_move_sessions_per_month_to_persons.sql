-- Migration 005: Move sessions_per_month from sessions to persons

-- Add column to persons table
ALTER TABLE `persons`
ADD COLUMN `sessions_per_month` INT UNSIGNED DEFAULT NULL AFTER `notes`;

-- Note: sessions_per_month column in sessions table is kept for backward compatibility
-- but should no longer be used. It can be removed in a future migration.
