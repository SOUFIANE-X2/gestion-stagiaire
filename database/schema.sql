-- INARAIN — schéma de base de données
-- MySQL 8.0+ / MariaDB 10.4+
-- Encodage : utf8mb4

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS absences;
DROP TABLE IF EXISTS stagiaires;
DROP TABLE IF EXISTS groupes;
DROP TABLE IF EXISTS filieres;
DROP TABLE IF EXISTS administrators;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE administrators (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    matricule VARCHAR(30) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(60) NOT NULL,
    last_name VARCHAR(60) NOT NULL,
    email VARCHAR(160) NOT NULL,
    role ENUM('admin', 'manager') NOT NULL DEFAULT 'manager',
    last_login_at DATETIME NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_admin_matricule (matricule),
    UNIQUE KEY uniq_admin_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE filieres (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    code VARCHAR(20) NOT NULL,
    label VARCHAR(120) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_filiere_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE groupes (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    code VARCHAR(20) NOT NULL,
    label VARCHAR(120) NOT NULL,
    filiere_id INT UNSIGNED NOT NULL,
    academic_year VARCHAR(9) NOT NULL DEFAULT '2025-2026',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_groupe_code (code),
    KEY idx_groupe_filiere (filiere_id),
    CONSTRAINT fk_groupe_filiere FOREIGN KEY (filiere_id)
        REFERENCES filieres (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE stagiaires (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    code VARCHAR(20) NOT NULL,
    first_name VARCHAR(60) NOT NULL,
    last_name VARCHAR(60) NOT NULL,
    gender ENUM('F', 'M') NOT NULL,
    email VARCHAR(160) NULL DEFAULT NULL,
    phone VARCHAR(30) NULL DEFAULT NULL,
    group_id INT UNSIGNED NOT NULL,
    note DECIMAL(4,2) NOT NULL DEFAULT 20.00,
    status ENUM('active', 'graduated', 'archived') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_stagiaire_code (code),
    UNIQUE KEY uniq_stagiaire_email (email),
    KEY idx_stagiaire_group (group_id),
    KEY idx_stagiaire_status (status),
    CONSTRAINT fk_stagiaire_group FOREIGN KEY (group_id)
        REFERENCES groupes (id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_stagiaire_note CHECK (note >= 0 AND note <= 20)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE absences (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    stagiaire_id INT UNSIGNED NOT NULL,
    date DATE NOT NULL,
    slot TINYINT UNSIGNED NOT NULL,
    justified TINYINT(1) NOT NULL DEFAULT 0,
    reason VARCHAR(255) NULL DEFAULT NULL,
    justification VARCHAR(255) NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_absence (stagiaire_id, date, slot),
    KEY idx_absence_date (date),
    KEY idx_absence_justified (justified),
    CONSTRAINT fk_absence_stagiaire FOREIGN KEY (stagiaire_id)
        REFERENCES stagiaires (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT chk_absence_slot CHECK (slot BETWEEN 1 AND 4)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
