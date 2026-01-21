-- Suivi Snoezelen - Normalize phone numbers
-- Migration 016
-- Normalize phone numbers to international format (+33...)

-- Step 1: Remove spaces, dashes, dots and parentheses from phones
UPDATE `users`
SET `phone` = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(`phone`, ' ', ''), '-', ''), '.', ''), '(', ''), ')', '')
WHERE `phone` IS NOT NULL AND `phone` != '';

UPDATE `bookings`
SET `client_phone` = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(`client_phone`, ' ', ''), '-', ''), '.', ''), '(', ''), ')', '')
WHERE `client_phone` IS NOT NULL AND `client_phone` != '';

-- Step 2: Convert 00 prefix to + (international format)
UPDATE `users`
SET `phone` = CONCAT('+', SUBSTRING(`phone`, 3))
WHERE `phone` IS NOT NULL AND `phone` LIKE '00%';

UPDATE `bookings`
SET `client_phone` = CONCAT('+', SUBSTRING(`client_phone`, 3))
WHERE `client_phone` IS NOT NULL AND `client_phone` LIKE '00%';

-- Step 3: Add +33 to French numbers (starting with 0, not already with +)
UPDATE `users`
SET `phone` = CONCAT('+33', SUBSTRING(`phone`, 2))
WHERE `phone` IS NOT NULL
  AND `phone` LIKE '0%'
  AND `phone` NOT LIKE '+%';

UPDATE `bookings`
SET `client_phone` = CONCAT('+33', SUBSTRING(`client_phone`, 2))
WHERE `client_phone` IS NOT NULL
  AND `client_phone` LIKE '0%'
  AND `client_phone` NOT LIKE '+%';
