-- Base de datos principal
USE webb_db;

-- Tabla de roles
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE
);

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol_id INT NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rol_id) REFERENCES roles(id)
);

-- Insertar roles
INSERT INTO roles (nombre) VALUES
    ('director'),
    ('profesor'),
    ('auxiliar'),
    ('estudiante'),
    ('apoderado');

-- Insertar usuarios de prueba
-- Contraseña para todos: Test1234
-- Hash generado con password_hash('Test1234', PASSWORD_BCRYPT)
INSERT INTO usuarios (nombre, email, password, rol_id) VALUES
    ('Director García',   'director@webb.cl',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
    ('Profesor Martínez', 'profesor@webb.cl',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2),
    ('Auxiliar López',    'auxiliar@webb.cl',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3),
    ('Estudiante Pérez',  'estudiante@webb.cl',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4),
    ('Apoderado Silva',   'apoderado@webb.cl',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 5);
