-- Migration: Remove obsolete per-email booking limits
-- These were replaced by booking_max_per_person in migration 023

DELETE FROM settings WHERE `key` IN (
    'booking_max_per_email',
    'booking_max_per_email_association'
);
