-- Migration: Suppression des données dupliquées dans bookings
-- Les infos client/personne sont maintenant récupérées via JOINs avec users/persons

-- Suppression des colonnes dupliquées
ALTER TABLE `bookings`
    DROP COLUMN `client_email`,
    DROP COLUMN `client_phone`,
    DROP COLUMN `client_first_name`,
    DROP COLUMN `client_last_name`,
    DROP COLUMN `client_type`,
    DROP COLUMN `company_name`,
    DROP COLUMN `siret`,
    DROP COLUMN `person_first_name`,
    DROP COLUMN `person_last_name`;

-- Note: user_id et person_id sont maintenant REQUIRED (créés à la volée lors du booking)
-- On garde les contraintes FK existantes mais on pourrait les renforcer avec NOT NULL
-- après avoir vérifié qu'aucun booking n'a de NULL (ce qui ne devrait pas arriver)
