-- Roles
CREATE TABLE roles (
  id TINYINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(20) UNIQUE NOT NULL
);

INSERT INTO roles (name) VALUES ('reportero'), ('validador'), ('admin');

-- Usuarios
CREATE TABLE users (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(180) UNIQUE,
  password_hash VARCHAR(255), -- solo para /super (validador/admin)
  role_id TINYINT UNSIGNED NOT NULL DEFAULT 1,
  status ENUM('active','blocked') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- Cuentas OAuth (Google/Microsoft)
CREATE TABLE oauth_accounts (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  provider ENUM('google','microsoft') NOT NULL,
  provider_user_id VARCHAR(191) NOT NULL,
  email VARCHAR(180),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_provider_user (provider, provider_user_id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Catálogos territoriales
CREATE TABLE provinces (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(120) UNIQUE NOT NULL
);

CREATE TABLE municipalities (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  province_id INT UNSIGNED NOT NULL,
  name VARCHAR(120) NOT NULL,
  UNIQUE KEY uniq_mun (province_id, name),
  FOREIGN KEY (province_id) REFERENCES provinces(id)
);

CREATE TABLE neighborhoods (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  municipality_id INT UNSIGNED NOT NULL,
  name VARCHAR(120) NOT NULL,
  UNIQUE KEY uniq_nbh (municipality_id, name),
  FOREIGN KEY (municipality_id) REFERENCES municipalities(id)
);

-- Tipos de incidencia
CREATE TABLE incident_types (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(80) UNIQUE NOT NULL,
  icon VARCHAR(80) DEFAULT NULL -- para Leaflet (clase o nombre de icono)
);

INSERT INTO incident_types (name) VALUES ('accidente'), ('pelea'), ('robo'), ('desastre');

-- Incidencias
CREATE TABLE incidents (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  reporter_id INT UNSIGNED NOT NULL,             -- user reportero
  occurred_at DATETIME NOT NULL,                 -- fecha de ocurrencia
  title VARCHAR(200) NOT NULL,
  description TEXT,
  province_id INT UNSIGNED,
  municipality_id INT UNSIGNED,
  neighborhood_id INT UNSIGNED,
  latitude DECIMAL(10,7),
  longitude DECIMAL(10,7),
  deaths INT UNSIGNED DEFAULT 0,
  injuries INT UNSIGNED DEFAULT 0,
  loss_dop DECIMAL(14,2) DEFAULT 0,              -- RD$
  social_link VARCHAR(300),
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  validated_by INT UNSIGNED,
  validated_at DATETIME,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (reporter_id) REFERENCES users(id),
  FOREIGN KEY (validated_by) REFERENCES users(id),
  FOREIGN KEY (province_id) REFERENCES provinces(id),
  FOREIGN KEY (municipality_id) REFERENCES municipalities(id),
  FOREIGN KEY (neighborhood_id) REFERENCES neighborhoods(id),
  INDEX idx_time_status (occurred_at, status),
  INDEX idx_geo (latitude, longitude)
);

-- N:M incidentes <-> tipos
CREATE TABLE incident_incident_type (
  incident_id BIGINT UNSIGNED NOT NULL,
  type_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (incident_id, type_id),
  FOREIGN KEY (incident_id) REFERENCES incidents(id) ON DELETE CASCADE,
  FOREIGN KEY (type_id) REFERENCES incident_types(id)
);

-- Fotos del hecho
CREATE TABLE incident_photos (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  incident_id BIGINT UNSIGNED NOT NULL,
  path VARCHAR(255) NOT NULL,    -- relative to /public/assets/uploads
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (incident_id) REFERENCES incidents(id) ON DELETE CASCADE
);

-- Comentarios (públicos por usuarios registrados)
CREATE TABLE comments (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  incident_id BIGINT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  content TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (incident_id) REFERENCES incidents(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Correcciones sugeridas (cabecera)
CREATE TABLE corrections (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  incident_id BIGINT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  status ENUM('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  reviewed_by INT UNSIGNED NULL,
  reviewed_at DATETIME NULL,
  FOREIGN KEY (incident_id) REFERENCES incidents(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (reviewed_by) REFERENCES users(id)
);

-- Detalle de corrección por campo (solo los permitidos)
-- allowed_field: deaths, injuries, province_id, municipality_id, loss_dop, latitude, longitude
CREATE TABLE correction_items (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  correction_id BIGINT UNSIGNED NOT NULL,
  field_name ENUM('deaths','injuries','province_id','municipality_id','loss_dop','latitude','longitude') NOT NULL,
  old_value VARCHAR(191) NULL,
  new_value VARCHAR(191) NOT NULL,
  FOREIGN KEY (correction_id) REFERENCES corrections(id) ON DELETE CASCADE
);

-- Registro de validaciones/cambios aplicados por validador
CREATE TABLE validations (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  incident_id BIGINT UNSIGNED NOT NULL,
  validator_id INT UNSIGNED NOT NULL,
  action ENUM('approve','reject','edit','merge') NOT NULL,
  notes TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (incident_id) REFERENCES incidents(id) ON DELETE CASCADE,
  FOREIGN KEY (validator_id) REFERENCES users(id)
);

-- Grupos de fusión (unir múltiples reportes similares)
CREATE TABLE merge_groups (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  main_incident_id BIGINT UNSIGNED NOT NULL,
  created_by INT UNSIGNED NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (main_incident_id) REFERENCES incidents(id) ON DELETE CASCADE,
  FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE merge_group_items (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  merge_group_id BIGINT UNSIGNED NOT NULL,
  incident_id BIGINT UNSIGNED NOT NULL,
  FOREIGN KEY (merge_group_id) REFERENCES merge_groups(id) ON DELETE CASCADE,
  FOREIGN KEY (incident_id) REFERENCES incidents(id) ON DELETE CASCADE,
  UNIQUE KEY uniq_merge_item (merge_group_id, incident_id)
);

-- Usuario inicial para /super (validador/admin)
INSERT INTO users (name, email, password_hash, role_id)
VALUES
('Validador Demo', 'super@example.com', '$2y$10$2kT7mQk5m4v1y0oQxFv7Ue7o7t0m3aE2wI0mM3qj1o0cQ7oR0A4H2', 2),
('Admin Demo', 'admin@example.com', '$2y$10$2kT7mQk5m4v1y0oQxFv7Ue7o7t0m3aE2wI0mM3qj1o0cQ7oR0A4H2', 3);
-- La contraseña de ejemplo en ambos es: Admin123*
