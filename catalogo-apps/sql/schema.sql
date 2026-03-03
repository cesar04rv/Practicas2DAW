-- ============================================================
-- Catálogo de Aplicaciones Corporativas - Schema SQL
-- MySQL 8.0+
-- ============================================================

CREATE DATABASE IF NOT EXISTS catalogo_apps
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE catalogo_apps;

-- ------------------------------------------------------------
-- Tabla: users
-- ------------------------------------------------------------
CREATE TABLE users (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(120)  NOT NULL,
    email       VARCHAR(180)  NOT NULL UNIQUE,
    password    VARCHAR(255)  NOT NULL,
    role        ENUM('admin','viewer') NOT NULL DEFAULT 'viewer',
    active      TINYINT(1)    NOT NULL DEFAULT 1,
    created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role  (role)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabla: technologies
-- ------------------------------------------------------------
CREATE TABLE technologies (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(80)   NOT NULL UNIQUE,
    color       VARCHAR(7)    NOT NULL DEFAULT '#6366f1',  -- hex color para badge
    created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (name)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabla: projects
-- ------------------------------------------------------------
CREATE TABLE projects (
    id                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name                 VARCHAR(180)  NOT NULL,
    description          TEXT,
    owner_id             INT UNSIGNED  NOT NULL,          -- responsable principal
    secondary_owner_id   INT UNSIGNED  NULL,              -- responsable secundario
    git_repo             VARCHAR(500)  NULL,
    server               VARCHAR(200)  NULL,
    url                  VARCHAR(500)  NULL,
    status               ENUM('production','dev','stopped') NOT NULL DEFAULT 'dev',
    credentials_location VARCHAR(500)  NULL,              -- SOLO ubicación, nunca la contraseña
    created_at           DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at           DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Claves foráneas
    CONSTRAINT fk_project_owner
        FOREIGN KEY (owner_id) REFERENCES users(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_project_secondary_owner
        FOREIGN KEY (secondary_owner_id) REFERENCES users(id)
        ON DELETE SET NULL ON UPDATE CASCADE,

    -- Índices para filtros y búsqueda
    INDEX idx_status        (status),
    INDEX idx_owner         (owner_id),
    INDEX idx_secondary_own (secondary_owner_id),
    FULLTEXT INDEX ft_name_desc (name, description)   -- búsqueda fulltext eficiente
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabla pivote: project_technologies (many-to-many)
-- ------------------------------------------------------------
CREATE TABLE project_technologies (
    project_id    INT UNSIGNED NOT NULL,
    technology_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (project_id, technology_id),

    CONSTRAINT fk_pt_project
        FOREIGN KEY (project_id) REFERENCES projects(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_pt_technology
        FOREIGN KEY (technology_id) REFERENCES technologies(id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    INDEX idx_tech (technology_id)
) ENGINE=InnoDB;

-- ============================================================
-- Datos iniciales
-- ============================================================

-- Usuario admin por defecto: admin@empresa.com / Admin1234!
INSERT INTO users (name, email, password, role) VALUES
('Administrador', 'admin@empresa.com',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- Admin1234!
 'admin'),
('Demo Viewer', 'viewer@empresa.com',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'viewer');

INSERT INTO technologies (name, color) VALUES
('PHP',        '#7c3aed'),
('MySQL',      '#0284c7'),
('JavaScript', '#d97706'),
('React',      '#0ea5e9'),
('Vue.js',     '#16a34a'),
('Python',     '#2563eb'),
('Docker',     '#0891b2'),
('Laravel',    '#dc2626'),
('Node.js',    '#15803d'),
('PostgreSQL', '#1d4ed8');

-- Proyecto de ejemplo
INSERT INTO projects (name, description, owner_id, secondary_owner_id, git_repo, server, url, status, credentials_location)
VALUES (
    'Portal RR.HH.',
    'Sistema interno de gestión de recursos humanos. Permite gestionar nóminas, vacaciones y evaluaciones.',
    1, 2,
    'git@gitlab.empresa.com:rrhh/portal-rrhh.git',
    'srv-prod-01',
    'https://rrhh.empresa.com',
    'production',
    'Vault > secret/rrhh/portal > sección credenciales'
);

INSERT INTO project_technologies (project_id, technology_id) VALUES (1,1),(1,2),(1,8);