CREATE DATABASE IF NOT EXISTS colegio_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE colegio_db;

-- ============================================================
-- TABLA USUARIOS (roles: director, profesor, auxiliar, estudiante, apoderado)
-- ============================================================
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('director','profesor','auxiliar','estudiante','apoderado') NOT NULL,
    telefono VARCHAR(20) DEFAULT '',
    descripcion VARCHAR(255) DEFAULT '',
    avatar VARCHAR(10) DEFAULT '',
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- CURSOS (Pre-kinder a 4° Medio)
-- ============================================================
CREATE TABLE cursos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    nivel VARCHAR(30) NOT NULL,
    letra CHAR(1) NOT NULL,
    año INT NOT NULL DEFAULT 2025,
    profesor_jefe_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (profesor_jefe_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- ============================================================
-- ASIGNATURAS
-- ============================================================
CREATE TABLE asignaturas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(80) NOT NULL,
    codigo VARCHAR(20) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- RELACIÓN CURSO-ASIGNATURA-PROFESOR
-- ============================================================
CREATE TABLE curso_asignatura (
    id INT AUTO_INCREMENT PRIMARY KEY,
    curso_id INT NOT NULL,
    asignatura_id INT NOT NULL,
    profesor_id INT NOT NULL,
    FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE,
    FOREIGN KEY (asignatura_id) REFERENCES asignaturas(id) ON DELETE CASCADE,
    FOREIGN KEY (profesor_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- ============================================================
-- MATRÍCULA: estudiante → curso
-- ============================================================
CREATE TABLE matriculas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    estudiante_id INT NOT NULL,
    curso_id INT NOT NULL,
    año INT NOT NULL DEFAULT 2025,
    fecha_matricula DATE DEFAULT (CURRENT_DATE),
    FOREIGN KEY (estudiante_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE
);

-- ============================================================
-- RELACIÓN APODERADO → ESTUDIANTE
-- ============================================================
CREATE TABLE apoderado_estudiante (
    id INT AUTO_INCREMENT PRIMARY KEY,
    apoderado_id INT NOT NULL,
    estudiante_id INT NOT NULL,
    parentesco VARCHAR(30) DEFAULT 'Apoderado',
    FOREIGN KEY (apoderado_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (estudiante_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- ============================================================
-- NOTAS
-- ============================================================
CREATE TABLE notas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    estudiante_id INT NOT NULL,
    curso_asignatura_id INT NOT NULL,
    evaluacion VARCHAR(80) NOT NULL,
    nota DECIMAL(3,1) NOT NULL,
    fecha DATE DEFAULT (CURRENT_DATE),
    observacion TEXT DEFAULT '',
    FOREIGN KEY (estudiante_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (curso_asignatura_id) REFERENCES curso_asignatura(id) ON DELETE CASCADE
);

-- ============================================================
-- ASISTENCIA
-- ============================================================
CREATE TABLE asistencia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    estudiante_id INT NOT NULL,
    curso_id INT NOT NULL,
    fecha DATE NOT NULL,
    estado ENUM('presente','ausente','justificado','tardanza') NOT NULL DEFAULT 'presente',
    observacion VARCHAR(255) DEFAULT '',
    registrado_por INT DEFAULT NULL,
    FOREIGN KEY (estudiante_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE,
    FOREIGN KEY (registrado_por) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- ============================================================
-- NOTICIAS / COMUNICADOS
-- ============================================================
CREATE TABLE noticias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    contenido TEXT NOT NULL,
    autor_id INT NOT NULL,
    tipo ENUM('general','urgente','evento','comunicado') DEFAULT 'general',
    visible_para SET('director','profesor','auxiliar','estudiante','apoderado') DEFAULT 'director,profesor,auxiliar,estudiante,apoderado',
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (autor_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- ============================================================
-- RECORDATORIOS (apoderado)
-- ============================================================
CREATE TABLE recordatorios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    titulo VARCHAR(100) NOT NULL,
    descripcion TEXT DEFAULT '',
    fecha_recordatorio DATE NOT NULL,
    completado TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- ============================================================
-- LOG DE ACTIVIDAD
-- ============================================================
CREATE TABLE actividad_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    accion VARCHAR(100) NOT NULL,
    detalle TEXT DEFAULT '',
    ip VARCHAR(45) DEFAULT '',
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- ============================================================
-- DATOS DE PRUEBA
-- ============================================================
-- Contraseña para todos: "password"
INSERT INTO usuarios (nombre, email, password, rol, telefono, descripcion, avatar) VALUES
('Director Carlos Muñoz',    'director@colegio.cl',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'director',   '+56 9 1000 0001', 'Director del establecimiento', '🎓'),
('Prof. Ana González',       'profesor@colegio.cl',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'profesor',   '+56 9 2000 0002', 'Profesora de Matemáticas',      '👩‍🏫'),
('Prof. Luis Herrera',       'profesor2@colegio.cl',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'profesor',   '+56 9 2000 0003', 'Profesor de Ciencias',          '👨‍🏫'),
('Pedro Rojas (Portero)',    'portero@colegio.cl',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'auxiliar',   '+56 9 3000 0004', 'Portero del establecimiento',   '🔑'),
('María López (Asist.Patio)','asistente@colegio.cl',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'auxiliar',   '+56 9 3000 0005', 'Asistente de patio',            '🏃'),
('Estudiante Juan Pérez',    'juan@colegio.cl',       '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'estudiante', '+56 9 4000 0006', '8° Básico A',                   '📚'),
('Estudiante Sofía Torres',  'sofia@colegio.cl',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'estudiante', '+56 9 4000 0007', '8° Básico A',                   '📚'),
('Apoderado Roberto Pérez',  'apoderado@colegio.cl',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'apoderado',  '+56 9 5000 0008', 'Padre de Juan Pérez',           '👨‍👧');

-- Cursos
INSERT INTO cursos (nombre, nivel, letra, año, profesor_jefe_id) VALUES
('Pre-Kinder A', 'Pre-Kinder', 'A', 2025, NULL),
('Kinder A',     'Kinder',     'A', 2025, NULL),
('1° Básico A',  '1° Básico',  'A', 2025, NULL),
('2° Básico A',  '2° Básico',  'A', 2025, NULL),
('3° Básico A',  '3° Básico',  'A', 2025, NULL),
('4° Básico A',  '4° Básico',  'A', 2025, NULL),
('5° Básico A',  '5° Básico',  'A', 2025, NULL),
('6° Básico A',  '6° Básico',  'A', 2025, NULL),
('7° Básico A',  '7° Básico',  'A', 2025, 2),
('8° Básico A',  '8° Básico',  'A', 2025, 2),
('1° Medio A',   '1° Medio',   'A', 2025, 3),
('2° Medio A',   '2° Medio',   'A', 2025, 3),
('3° Medio A',   '3° Medio',   'A', 2025, NULL),
('4° Medio A',   '4° Medio',   'A', 2025, NULL);

-- Asignaturas
INSERT INTO asignaturas (nombre, codigo) VALUES
('Matemáticas',          'MAT'),
('Lenguaje y Comunicación', 'LEN'),
('Ciencias Naturales',   'CN'),
('Historia y Geografía', 'HIS'),
('Inglés',               'ING'),
('Educación Física',     'EF'),
('Artes Visuales',       'ARV'),
('Música',               'MUS'),
('Tecnología',           'TEC');

-- Relación curso-asignatura-profesor (8° Básico A = id 10)
INSERT INTO curso_asignatura (curso_id, asignatura_id, profesor_id) VALUES
(10, 1, 2), (10, 2, 2), (10, 3, 3), (10, 4, 3), (10, 5, 2);

-- Matricular estudiantes en 8° Básico A
INSERT INTO matriculas (estudiante_id, curso_id, año) VALUES (6, 10, 2025), (7, 10, 2025);

-- Apoderado → estudiante
INSERT INTO apoderado_estudiante (apoderado_id, estudiante_id, parentesco) VALUES (8, 6, 'Padre');

-- Notas de Juan Pérez
INSERT INTO notas (estudiante_id, curso_asignatura_id, evaluacion, nota, fecha) VALUES
(6, 1, 'Prueba 1',     6.5, '2025-03-15'),
(6, 1, 'Prueba 2',     5.8, '2025-04-10'),
(6, 1, 'Trabajo',      6.2, '2025-05-05'),
(6, 2, 'Prueba 1',     5.5, '2025-03-20'),
(6, 2, 'Disertación',  6.0, '2025-04-18'),
(6, 3, 'Laboratorio',  6.8, '2025-03-25'),
(6, 3, 'Prueba 1',     5.2, '2025-04-22'),
(6, 4, 'Prueba 1',     4.8, '2025-03-18'),
(6, 5, 'Prueba oral',  6.3, '2025-04-08');

-- Asistencia de Juan Pérez (últimas 2 semanas)
INSERT INTO asistencia (estudiante_id, curso_id, fecha, estado, registrado_por) VALUES
(6, 10, '2025-05-05', 'presente',   4),
(6, 10, '2025-05-06', 'presente',   4),
(6, 10, '2025-05-07', 'ausente',    4),
(6, 10, '2025-05-08', 'justificado',4),
(6, 10, '2025-05-09', 'presente',   4),
(6, 10, '2025-05-12', 'presente',   4),
(6, 10, '2025-05-13', 'tardanza',   4),
(6, 10, '2025-05-14', 'presente',   4),
(6, 10, '2025-05-15', 'presente',   4),
(6, 10, '2025-05-16', 'presente',   4);

-- Noticias
INSERT INTO noticias (titulo, contenido, autor_id, tipo, visible_para) VALUES
('Bienvenida año escolar 2025', 'Estimada comunidad escolar, les damos la más cordial bienvenida al año académico 2025. Este año trabajaremos juntos para alcanzar los mejores resultados.', 1, 'general', 'director,profesor,auxiliar,estudiante,apoderado'),
('Reunión de apoderados - Mayo', 'Se cita a todos los apoderados a la reunión del mes de mayo el día viernes 23 a las 19:00 hrs en el gimnasio del establecimiento.', 1, 'comunicado', 'apoderado,estudiante'),
('Entrega de notas primer semestre', 'Se informa que la entrega de libretas del primer semestre se realizará el día 30 de junio en horario de 14:00 a 18:00 hrs.', 2, 'evento', 'director,profesor,auxiliar,estudiante,apoderado'),
('⚠️ Simulacro evacuación', 'Se realizará un simulacro de evacuación el día miércoles 21. Se solicita la colaboración de toda la comunidad escolar.', 1, 'urgente', 'director,profesor,auxiliar,estudiante,apoderado');

-- Recordatorios del apoderado
INSERT INTO recordatorios (usuario_id, titulo, descripcion, fecha_recordatorio) VALUES
(8, 'Reunión de apoderados', 'Reunión mensual en el gimnasio a las 19:00', '2025-05-23'),
(8, 'Pago mensualidad junio', 'Recordar pagar antes del día 5 del mes', '2025-06-05'),
(8, 'Revisión libreta Juan', 'Revisar notas del primer semestre', '2025-06-30');
