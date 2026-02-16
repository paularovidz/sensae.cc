-- Migration: Update prepaid packs to 2 and 4 sessions
-- Replace pack_5/pack_10 with pack_2/pack_4

-- Delete old pack settings
DELETE FROM settings WHERE `key` IN (
    'prepaid_pack_5_sessions',
    'prepaid_pack_5_price',
    'prepaid_pack_10_sessions',
    'prepaid_pack_10_price'
);

-- Pack 2 sessions (55€/séance = 110€)
INSERT INTO settings (`key`, `value`, `type`, `label`, `description`, `category`, created_at)
VALUES ('prepaid_pack_2_sessions', '2', 'integer', 'Pack 2 - Nombre de séances', 'Nombre de séances incluses dans le pack 2', 'pricing', NOW())
ON DUPLICATE KEY UPDATE `key` = `key`;

INSERT INTO settings (`key`, `value`, `type`, `label`, `description`, `category`, created_at)
VALUES ('prepaid_pack_2_price', '110', 'integer', 'Pack 2 - Prix total (€)', 'Prix du pack de 2 séances (55€/séance)', 'pricing', NOW())
ON DUPLICATE KEY UPDATE `key` = `key`;

-- Pack 4 sessions (50€/séance = 200€)
INSERT INTO settings (`key`, `value`, `type`, `label`, `description`, `category`, created_at)
VALUES ('prepaid_pack_4_sessions', '4', 'integer', 'Pack 4 - Nombre de séances', 'Nombre de séances incluses dans le pack 4', 'pricing', NOW())
ON DUPLICATE KEY UPDATE `key` = `key`;

INSERT INTO settings (`key`, `value`, `type`, `label`, `description`, `category`, created_at)
VALUES ('prepaid_pack_4_price', '200', 'integer', 'Pack 4 - Prix total (€)', 'Prix du pack de 4 séances (50€/séance)', 'pricing', NOW())
ON DUPLICATE KEY UPDATE `key` = `key`;

-- Move expiry setting to pricing category
UPDATE settings SET category = 'pricing' WHERE `key` = 'prepaid_default_expiry_months';

-- Update existing packs if any (pack_5 -> pack_2, pack_10 -> pack_4)
UPDATE prepaid_packs SET pack_type = 'pack_2' WHERE pack_type = 'pack_5';
UPDATE prepaid_packs SET pack_type = 'pack_4' WHERE pack_type = 'pack_10';
