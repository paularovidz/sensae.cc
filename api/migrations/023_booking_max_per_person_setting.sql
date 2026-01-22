-- Migration: Add booking_max_per_person setting
-- Limite de 4 séances par Personne (remplace la limite par email)

INSERT INTO settings (`key`, `value`, `type`, `label`, `description`, `category`, created_at)
VALUES
('booking_max_per_person', '4', 'integer', 'Séances max par personne', 'Nombre maximum de séances à venir par personne/bénéficiaire', 'booking', NOW())
ON DUPLICATE KEY UPDATE `key` = `key`;
