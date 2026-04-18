CREATE DATABASE IF NOT EXISTS sistema_login;
USE sistema_login;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'secundario', 'usuario') NOT NULL,
    telefono VARCHAR(20) DEFAULT '',
    descripcion VARCHAR(255) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS actividad_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    accion VARCHAR(100) NOT NULL,
    detalle TEXT DEFAULT '',
    ip VARCHAR(45) DEFAULT '',
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Contraseña para todos: "password"
INSERT INTO usuarios (nombre, email, password, rol, telefono, descripcion) VALUES
('Administrador Principal', 'admin@sistema.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin',      '+56 9 1234 5678', 'Administrador con acceso total al sistema.'),
('Admin Secundario',        'secundario@sistema.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'secundario', '+56 9 8765 4321', 'Administrador con acceso moderado.'),
('Usuario Normal',          'usuario@sistema.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'usuario',    '+56 9 1111 2222', 'Usuario estándar del sistema.');
