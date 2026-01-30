-- Migration 028: Off Days Periods
-- Permet de définir des périodes de fermeture (pas seulement un jour)

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Renommer 'date' en 'start_date' et ajouter 'end_date'
ALTER TABLE `off_days`
    CHANGE COLUMN `date` `start_date` DATE NOT NULL,
    ADD COLUMN `end_date` DATE NOT NULL AFTER `start_date`;

-- Initialiser end_date avec start_date pour les entrées existantes
UPDATE `off_days` SET `end_date` = `start_date` WHERE `end_date` = '0000-00-00' OR `end_date` IS NULL;

-- Supprimer l'ancien index unique sur date
ALTER TABLE `off_days` DROP INDEX `idx_off_days_date`;

-- Ajouter des index pour les recherches par période
ALTER TABLE `off_days`
    ADD INDEX `idx_off_days_start_date` (`start_date`),
    ADD INDEX `idx_off_days_end_date` (`end_date`);

SET FOREIGN_KEY_CHECKS = 1;
