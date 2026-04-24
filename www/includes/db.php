<?php
$host = 'mysql';
$user = 'webb_user';
$pass = 'webb_pass';
$dbname = 'webb_db';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

function obtenerDatosUsuario($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

function obtenerCursos() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM cursos ORDER BY anio, nombre");
    return $stmt->fetchAll();
}

function obtenerAsignaturasPorCurso($curso_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM asignaturas WHERE curso_id = ?");
    $stmt->execute([$curso_id]);
    return $stmt->fetchAll();
}

function obtenerEstudiantesPorCurso($curso_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT u.* FROM usuarios u 
        JOIN estudiantes e ON u.id = e.user_id 
        WHERE e.curso_id = ? AND u.rol = 'estudiante'
    ");
    $stmt->execute([$curso_id]);
    return $stmt->fetchAll();
}

function obtenerProfesores() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE rol = 'profesor'");
    $stmt->execute();
    return $stmt->fetchAll();
}

function obtenerCursosPorProfesor($profesor_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT DISTINCT c.* FROM cursos c
        JOIN profesor_asignatura_curso pac ON c.id = pac.curso_id
        WHERE pac.profesor_id = ?
    ");
    $stmt->execute([$profesor_id]);
    return $stmt->fetchAll();
}

function obtenerAsignaturasPorProfesorCurso($profesor_id, $curso_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT a.* FROM asignaturas a
        JOIN profesor_asignatura_curso pac ON a.id = pac.asignatura_id
        WHERE pac.profesor_id = ? AND pac.curso_id = ?
    ");
    $stmt->execute([$profesor_id, $curso_id]);
    return $stmt->fetchAll();
}

function obtenerHijos($apoderado_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT u.*, e.curso_id, c.nombre as curso_nombre 
        FROM usuarios u
        JOIN apoderado_estudiante ae ON u.id = ae.estudiante_id
        JOIN estudiantes e ON u.id = e.user_id
        JOIN cursos c ON e.curso_id = c.id
        WHERE ae.apoderado_id = ?
    ");
    $stmt->execute([$apoderado_id]);
    return $stmt->fetchAll();
}
?>