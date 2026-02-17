-- Migration: Add group sessions (half_day, full_day) for associations
-- These sessions are for groups and don't require clinical tracking

-- 1. Modify duration_type enum to add half_day and full_day
ALTER TABLE sessions
MODIFY COLUMN duration_type ENUM('discovery', 'regular', 'half_day', 'full_day')
NOT NULL DEFAULT 'regular';

-- 2. Add with_accompaniment field (default true = with accompaniment)
ALTER TABLE sessions
ADD COLUMN with_accompaniment TINYINT(1) DEFAULT 1 AFTER duration_type;

-- 3. Settings for group session durations
INSERT INTO settings (`key`, `value`, `type`, `label`, `description`, `category`) VALUES
('session_half_day_display_minutes', '240', 'integer', 'Durée demi-journée (min)', 'Durée d\'une demi-journée : 4 heures', 'scheduling'),
('session_half_day_pause_minutes', '0', 'integer', 'Pause après demi-journée', 'Pas de pause nécessaire pour les demi-journées', 'scheduling'),
('session_full_day_display_minutes', '480', 'integer', 'Durée journée complète (min)', 'Durée d\'une journée complète : 8 heures', 'scheduling'),
('session_full_day_pause_minutes', '0', 'integer', 'Pause après journée', 'Pas de pause nécessaire pour les journées complètes', 'scheduling')
ON DUPLICATE KEY UPDATE `key` = `key`;

-- 4. Settings for group session pricing (4 combinations)
INSERT INTO settings (`key`, `value`, `type`, `label`, `description`, `category`) VALUES
('session_half_day_price_with', '200', 'integer', 'Demi-journée avec accompagnement (€)', 'Prix TTC pour une demi-journée avec présence de Céline', 'pricing'),
('session_half_day_price_without', '120', 'integer', 'Demi-journée sans accompagnement (€)', 'Prix TTC pour une demi-journée sans accompagnement', 'pricing'),
('session_full_day_price_with', '350', 'integer', 'Journée avec accompagnement (€)', 'Prix TTC pour une journée complète avec présence de Céline', 'pricing'),
('session_full_day_price_without', '200', 'integer', 'Journée sans accompagnement (€)', 'Prix TTC pour une journée complète sans accompagnement', 'pricing')
ON DUPLICATE KEY UPDATE `key` = `key`;
