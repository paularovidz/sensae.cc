-- OPS Database Schema
-- Cockpit Financier pour sensea

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- USERS (Authentification magic link)
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id CHAR(36) PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    last_login_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- MAGIC LINKS
-- ============================================
CREATE TABLE IF NOT EXISTS magic_links (
    id CHAR(36) PRIMARY KEY,
    user_id CHAR(36) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME,
    ip_address VARCHAR(45),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- REFRESH TOKENS
-- ============================================
CREATE TABLE IF NOT EXISTS refresh_tokens (
    id CHAR(36) PRIMARY KEY,
    user_id CHAR(36) NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    expires_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token_hash (token_hash),
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- EXPENSE CATEGORIES
-- ============================================
CREATE TABLE IF NOT EXISTS expense_categories (
    id CHAR(36) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    color VARCHAR(7) DEFAULT '#6B7280',
    icon VARCHAR(50),
    sort_order INT UNSIGNED DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_sort_order (sort_order),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- EXPENSES (Depenses)
-- ============================================
CREATE TABLE IF NOT EXISTS expenses (
    id CHAR(36) PRIMARY KEY,
    category_id CHAR(36) NOT NULL,
    description VARCHAR(255) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    expense_date DATE NOT NULL,
    payment_method ENUM('cash','card','transfer','check','direct_debit') DEFAULT 'transfer',
    vendor VARCHAR(255),
    invoice_number VARCHAR(100),
    notes TEXT,
    recurring_expense_id CHAR(36),
    bank_import_id CHAR(36),
    created_by CHAR(36) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES expense_categories(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_category_id (category_id),
    INDEX idx_expense_date (expense_date),
    INDEX idx_recurring_expense_id (recurring_expense_id),
    INDEX idx_bank_import_id (bank_import_id),
    INDEX idx_created_by (created_by),
    INDEX idx_year_month (expense_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- RECURRING EXPENSES (Depenses recurrentes)
-- ============================================
CREATE TABLE IF NOT EXISTS recurring_expenses (
    id CHAR(36) PRIMARY KEY,
    category_id CHAR(36) NOT NULL,
    description VARCHAR(255) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    frequency ENUM('monthly','quarterly','yearly') DEFAULT 'monthly',
    day_of_month INT UNSIGNED DEFAULT 1,
    vendor VARCHAR(255),
    notes TEXT,
    is_active TINYINT(1) DEFAULT 1,
    start_date DATE NOT NULL,
    end_date DATE,
    last_generated_date DATE,
    created_by CHAR(36) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES expense_categories(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_category_id (category_id),
    INDEX idx_is_active (is_active),
    INDEX idx_frequency (frequency),
    INDEX idx_start_date (start_date),
    INDEX idx_end_date (end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add foreign key for recurring_expense_id after table exists
ALTER TABLE expenses
ADD FOREIGN KEY (recurring_expense_id) REFERENCES recurring_expenses(id) ON DELETE SET NULL;

-- ============================================
-- MONTHLY FORECASTS (Previsions CA)
-- ============================================
CREATE TABLE IF NOT EXISTS monthly_forecasts (
    id CHAR(36) PRIMARY KEY,
    year INT UNSIGNED NOT NULL,
    month INT UNSIGNED NOT NULL,
    revenue_forecast DECIMAL(10,2) NOT NULL,
    expense_forecast DECIMAL(10,2) DEFAULT 0,
    notes TEXT,
    created_by CHAR(36) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_year_month (year, month),
    INDEX idx_year (year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- MONTH STATES (Etat des mois: estime vs reel)
-- ============================================
CREATE TABLE IF NOT EXISTS month_states (
    id CHAR(36) PRIMARY KEY,
    year INT UNSIGNED NOT NULL,
    month INT UNSIGNED NOT NULL,
    state ENUM('estimated','actual') DEFAULT 'estimated',
    locked_at DATETIME,
    locked_by CHAR(36),
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (locked_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_year_month (year, month),
    INDEX idx_year (year),
    INDEX idx_state (state)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- VENDOR MAPPINGS (Auto-categorisation fournisseurs)
-- ============================================
CREATE TABLE IF NOT EXISTS vendor_mappings (
    id CHAR(36) PRIMARY KEY,
    vendor_pattern VARCHAR(255) NOT NULL,
    vendor_display_name VARCHAR(255),
    category_id CHAR(36) NOT NULL,
    is_regex TINYINT(1) DEFAULT 0,
    priority INT UNSIGNED DEFAULT 0,
    notes TEXT,
    created_by CHAR(36) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES expense_categories(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_pattern (vendor_pattern),
    INDEX idx_category_id (category_id),
    INDEX idx_priority (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- BANK IMPORTS (Historique imports CSV)
-- ============================================
CREATE TABLE IF NOT EXISTS bank_imports (
    id CHAR(36) PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    rows_imported INT UNSIGNED DEFAULT 0,
    rows_skipped INT UNSIGNED DEFAULT 0,
    import_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    imported_by CHAR(36) NOT NULL,
    FOREIGN KEY (imported_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_import_date (import_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add foreign key for bank_import_id after table exists
ALTER TABLE expenses
ADD FOREIGN KEY (bank_import_id) REFERENCES bank_imports(id) ON DELETE SET NULL;

-- ============================================
-- SEED: Categories par defaut
-- ============================================
INSERT INTO expense_categories (id, name, slug, color, icon, sort_order) VALUES
    (UUID(), 'Frais de local', 'local', '#EF4444', 'building', 1),
    (UUID(), 'Frais generaux', 'general', '#F59E0B', 'cog', 2),
    (UUID(), 'Prestataires', 'prestataires', '#10B981', 'users', 3),
    (UUID(), 'Personnel', 'personnel', '#3B82F6', 'user', 4),
    (UUID(), 'Impots / Taxes', 'impots', '#8B5CF6', 'document', 5),
    (UUID(), 'Formation', 'formation', '#EC4899', 'academic-cap', 6),
    (UUID(), 'Materiel', 'materiel', '#06B6D4', 'cube', 7),
    (UUID(), 'Marketing', 'marketing', '#F97316', 'megaphone', 8),
    (UUID(), 'Assurances', 'assurances', '#6366F1', 'shield-check', 9),
    (UUID(), 'Vehicule', 'vehicule', '#84CC16', 'truck', 10);

-- ============================================
-- SEED: Admin user (first account - magic link auth)
-- ============================================
INSERT INTO users (id, email, first_name, last_name) VALUES
    (UUID(), 'bonjour@sensea.cc', 'Admin', 'OPS');

SET FOREIGN_KEY_CHECKS = 1;
