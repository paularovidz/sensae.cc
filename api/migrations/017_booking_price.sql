-- Migration: Add price field to bookings table
-- Date: 2026-01-21
-- Store the session price at the time of booking

ALTER TABLE `bookings`
ADD COLUMN `price` DECIMAL(10,2) DEFAULT NULL AFTER `duration_blocked_minutes`;

-- Add comment for documentation
ALTER TABLE `bookings`
MODIFY COLUMN `price` DECIMAL(10,2) DEFAULT NULL COMMENT 'Prix de la séance en euros au moment de la réservation';
