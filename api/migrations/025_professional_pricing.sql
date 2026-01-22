-- Migration: Professional pricing and booking delays
-- Date: 2026-01-22

-- Insert session price settings for associations
INSERT INTO `settings` (`key`, `value`, `type`, `label`, `description`, `category`) VALUES
('session_regular_price_association', '40', 'integer', 'Prix séance classique associations (€)', 'Prix en euros pour une séance classique (45 min) - Associations', 'pricing'),
('session_discovery_price_association', '50', 'integer', 'Prix séance découverte associations (€)', 'Prix en euros pour une séance découverte (1h15) - Associations', 'pricing'),
('booking_max_advance_days_association', '90', 'integer', 'Délai max réservation associations (jours)', 'Nombre maximum de jours à l\'avance pour réserver - Associations', 'booking')
ON DUPLICATE KEY UPDATE `key` = `key`;
