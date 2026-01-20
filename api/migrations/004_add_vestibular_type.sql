-- Migration 004: Add vestibular type to sensory_proposals

ALTER TABLE `sensory_proposals`
MODIFY COLUMN `type` ENUM('tactile', 'visual', 'olfactory', 'gustatory', 'auditory', 'proprioceptive', 'vestibular') NOT NULL;
