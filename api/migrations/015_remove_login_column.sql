-- Suivi Snoezelen - Remove login column from users table
-- Migration 015
-- The login field is no longer used

-- Drop unique index first
ALTER TABLE `users` DROP INDEX IF EXISTS `idx_users_login`;

-- Drop the column
ALTER TABLE `users` DROP COLUMN IF EXISTS `login`;
