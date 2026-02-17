-- Migration: Allow NULL person_id for privatizations
-- Privatizations (half_day, full_day) are linked to an association, not a specific person

ALTER TABLE sessions
MODIFY COLUMN person_id CHAR(36) NULL;
