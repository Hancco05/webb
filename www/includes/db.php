// ... (código anterior)

// Obtener cursos de un profesor
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

// Obtener asignaturas de un profesor para un curso
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

// Obtener hijos de un apoderado
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