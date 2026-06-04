<?php
require_once '../includes/auth.php';
verificarSesion('director');
require_once '../includes/db.php';

$tipo = $_GET['tipo'] ?? 'usuarios';
$filename = "export_{$tipo}_" . date('Y-m-d') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// Encabezados
if ($tipo == 'usuarios') {
    fputcsv($output, ['ID', 'Nombre', 'Email', 'Rol', 'Fecha registro']);
    $data = $pdo->query("SELECT id, nombre, email, rol, created_at FROM usuarios ORDER BY rol, nombre")->fetchAll();
    foreach ($data as $row) {
        fputcsv($output, [$row['id'], $row['nombre'], $row['email'], $row['rol'], $row['created_at']]);
    }
} elseif ($tipo == 'cursos') {
    fputcsv($output, ['ID', 'Nombre', 'Descripción', 'Año', 'Estudiantes']);
    $data = $pdo->query("SELECT c.*, COUNT(e.user_id) as estudiantes FROM cursos c LEFT JOIN estudiantes e ON c.id = e.curso_id GROUP BY c.id")->fetchAll();
    foreach ($data as $row) {
        fputcsv($output, [$row['id'], $row['nombre'], $row['descripcion'], $row['anio'], $row['estudiantes']]);
    }
} elseif ($tipo == 'tareas') {
    fputcsv($output, ['Título', 'Curso', 'Asignatura', 'Fecha entrega', 'Profesor']);
    $data = $pdo->query("SELECT t.titulo, c.nombre as curso, a.nombre as asignatura, t.fecha_entrega, u.nombre as profesor FROM tareas t JOIN cursos c ON t.curso_id = c.id JOIN asignaturas a ON t.asignatura_id = a.id JOIN usuarios u ON t.creado_por = u.id ORDER BY t.fecha_entrega")->fetchAll();
    foreach ($data as $row) {
        fputcsv($output, [$row['titulo'], $row['curso'], $row['asignatura'], $row['fecha_entrega'], $row['profesor']]);
    }
}
fclose($output);
exit;