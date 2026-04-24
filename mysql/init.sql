USE webb_db;

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    rol ENUM('director', 'profesor', 'auxiliar', 'estudiante', 'apoderado') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de estudiantes (información adicional)
CREATE TABLE IF NOT EXISTS estudiantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    curso_id INT,
    FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla de apoderados - estudiantes (relación muchos a muchos)
CREATE TABLE IF NOT EXISTS apoderado_estudiante (
    apoderado_id INT NOT NULL,
    estudiante_id INT NOT NULL,
    PRIMARY KEY (apoderado_id, estudiante_id),
    FOREIGN KEY (apoderado_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (estudiante_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla de cursos
CREATE TABLE IF NOT EXISTS cursos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    descripcion TEXT,
    anio INT NOT NULL
);

-- Tabla de asignaturas
CREATE TABLE IF NOT EXISTS asignaturas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    codigo VARCHAR(20),
    curso_id INT NOT NULL,
    FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE
);

-- Asignación de profesores a cursos y asignaturas
CREATE TABLE IF NOT EXISTS profesor_asignatura_curso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    profesor_id INT NOT NULL,
    curso_id INT NOT NULL,
    asignatura_id INT NOT NULL,
    FOREIGN KEY (profesor_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE,
    FOREIGN KEY (asignatura_id) REFERENCES asignaturas(id) ON DELETE CASCADE,
    UNIQUE KEY unique_asignacion (profesor_id, curso_id, asignatura_id)
);

-- Tabla de notas
CREATE TABLE IF NOT EXISTS notas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    estudiante_id INT NOT NULL,
    asignatura_id INT NOT NULL,
    periodo VARCHAR(20) NOT NULL,
    nota DECIMAL(4,2) NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    registrado_por INT NOT NULL,
    FOREIGN KEY (estudiante_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (asignatura_id) REFERENCES asignaturas(id) ON DELETE CASCADE,
    FOREIGN KEY (registrado_por) REFERENCES usuarios(id)
);

-- Tabla de asistencia
CREATE TABLE IF NOT EXISTS asistencia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    estudiante_id INT NOT NULL,
    fecha DATE NOT NULL,
    estado ENUM('presente', 'ausente', 'tarde') NOT NULL,
    registrado_por INT NOT NULL,
    FOREIGN KEY (estudiante_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (registrado_por) REFERENCES usuarios(id),
    UNIQUE KEY unique_asistencia_dia (estudiante_id, fecha)
);

-- Tabla de noticias
CREATE TABLE IF NOT EXISTS noticias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    contenido TEXT NOT NULL,
    fecha_publicacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    creado_por INT NOT NULL,
    rol_destino ENUM('todos', 'director', 'profesor', 'auxiliar', 'estudiante', 'apoderado') DEFAULT 'todos',
    FOREIGN KEY (creado_por) REFERENCES usuarios(id)
);

-- Tabla de recordatorios
CREATE TABLE IF NOT EXISTS recordatorios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT,
    fecha_recordatorio DATE NOT NULL,
    para_rol ENUM('apoderado', 'estudiante', 'todos') DEFAULT 'apoderado',
    creado_por INT NOT NULL,
    FOREIGN KEY (creado_por) REFERENCES usuarios(id)
);

-- Insertar usuarios de ejemplo (contraseña: 123456 encriptada)
-- Hash de '123456' generado con password_hash()
INSERT INTO usuarios (nombre, email, password_hash, rol) VALUES
('Director', 'director@colegio.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'director'),
('Profesor Juan', 'profesor@colegio.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'profesor'),
('Auxiliar Pedro', 'auxiliar@colegio.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'auxiliar'),
('Estudiante Ana', 'estudiante@colegio.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'estudiante'),
('Apoderado Carlos', 'apoderado@colegio.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'apoderado');

-- Insertar cursos
INSERT INTO cursos (nombre, descripcion, anio) VALUES
('1ro Básico', 'Primer año de educación básica', 2025),
('2do Básico', 'Segundo año de educación básica', 2025),
('3ro Básico', 'Tercer año de educación básica', 2025);

-- Insertar asignaturas para el curso 1 (id 1)
INSERT INTO asignaturas (nombre, codigo, curso_id) VALUES
('Matemáticas', 'MAT-101', 1),
('Lenguaje', 'LEN-101', 1),
('Ciencias', 'CIE-101', 1);

-- Asignar profesor (id 2) al curso 1 y a las asignaturas 1,2,3
INSERT INTO profesor_asignatura_curso (profesor_id, curso_id, asignatura_id) VALUES
(2, 1, 1),
(2, 1, 2),
(2, 1, 3);

-- Relacionar estudiante (id 4) con curso 1
INSERT INTO estudiantes (user_id, curso_id) VALUES (4, 1);

-- Relacionar apoderado (id 5) con estudiante (id 4)
INSERT INTO apoderado_estudiante (apoderado_id, estudiante_id) VALUES (5, 4);

-- Noticia de ejemplo
INSERT INTO noticias (titulo, contenido, creado_por, rol_destino) VALUES
('Bienvenida al año escolar 2025', 'Les damos la bienvenida a todos los estudiantes y apoderados a un nuevo año lleno de aprendizajes.', 1, 'todos');

-- Recordatorio de ejemplo
INSERT INTO recordatorios (titulo, descripcion, fecha_recordatorio, para_rol, creado_por) VALUES
('Reunión de apoderados', 'Reunión general de apoderados en el gimnasio.', '2025-03-20', 'apoderado', 1);