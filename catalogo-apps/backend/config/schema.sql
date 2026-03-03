CREATE DATABASE IF NOT EXISTS catalogo_apps
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE catalogo_apps;

CREATE TABLE users (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(120) NOT NULL,
    email      VARCHAR(180) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    role       ENUM('admin','viewer') NOT NULL DEFAULT 'viewer',
    active     TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE technologies (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(80) NOT NULL UNIQUE,
    color      VARCHAR(7) NOT NULL DEFAULT '#6366f1',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE projects (
    id                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name                 VARCHAR(180) NOT NULL,
    subtitle             VARCHAR(300) NULL,
    description          TEXT,
    status               ENUM('production','dev','stopped') NOT NULL DEFAULT 'dev',
    location             VARCHAR(200) NULL,
    dev_environment      VARCHAR(300) NULL,
    url                  VARCHAR(500) NULL,
    credentials_location VARCHAR(500) NULL,
    created_at           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FULLTEXT INDEX ft_name_desc (name, description)
) ENGINE=InnoDB;

CREATE TABLE project_users (
    project_id INT UNSIGNED NOT NULL,
    user_id    INT UNSIGNED NOT NULL,
    role       ENUM('owner','collaborator') NOT NULL DEFAULT 'collaborator',
    PRIMARY KEY (project_id, user_id),
    CONSTRAINT fk_pu_project  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    CONSTRAINT fk_pu_user     FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE project_technologies (
    project_id    INT UNSIGNED NOT NULL,
    technology_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (project_id, technology_id),
    CONSTRAINT fk_pt_project    FOREIGN KEY (project_id)    REFERENCES projects(id)     ON DELETE CASCADE,
    CONSTRAINT fk_pt_technology FOREIGN KEY (technology_id) REFERENCES technologies(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO users (name, email, password, role) VALUES
('Administrador', 'admin@empresa.com',  '$2y$12$eDoAghydlApHOvRbcFKEqumFAuEcAhtMhj.FXnP5fASSTjFWco.JC', 'admin'),
('Demo Viewer',   'viewer@empresa.com', '$2y$12$eDoAghydlApHOvRbcFKEqumFAuEcAhtMhj.FXnP5fASSTjFWco.JC', 'viewer');

INSERT INTO technologies (name, color) VALUES
('PHP','#7c3aed'),('MySQL','#0284c7'),('JavaScript','#d97706'),
('React','#0ea5e9'),('Vue.js','#16a34a'),('Python','#2563eb'),
('Docker','#0891b2'),('Laravel','#dc2626'),('Node.js','#15803d'),('PostgreSQL','#1d4ed8');

INSERT INTO projects (name, subtitle, description, status, location, dev_environment, url, credentials_location)
VALUES ('Portal RR.HH.','Gestión interna de RRHH','Sistema para gestionar nóminas y vacaciones.','production','srv-prod-01','VS Code, Laravel','https://rrhh.empresa.com','Vault > rrhh');

INSERT INTO project_users (project_id, user_id, role) VALUES (1,1,'owner'),(1,2,'collaborator');
INSERT INTO project_technologies (project_id, technology_id) VALUES (1,1),(1,2),(1,8);