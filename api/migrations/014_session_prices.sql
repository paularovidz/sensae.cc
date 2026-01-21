-- Migration: Session prices settings
-- Date: 2026-01-21

-- Insert session price settings
INSERT INTO `settings` (`key`, `value`, `type`, `label`, `description`, `category`) VALUES
('session_regular_price', '45', 'integer', 'Prix séance classique (€)', 'Prix en euros pour une séance classique (45 min)', 'pricing'),
('session_discovery_price', '55', 'integer', 'Prix séance découverte (€)', 'Prix en euros pour une séance découverte (1h15)', 'pricing')
ON DUPLICATE KEY UPDATE `key` = `key`;
