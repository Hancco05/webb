<?php
require_once '../includes/auth.php';
verificarSesion('director');
require_once '../includes/db.php';

$tipo = $_GET['tipo'] ?? 'usuarios';
$filename = "export_{$tipo}_" . date('Y-m-d') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
$output = fopen('php://output', 'w');

if ($tipo == 'usuarios') {
    fputcsv($output, ['ID', 'Nombre', 'Email', 'Rol', 'Fecha']);
    $data = $pdo->query("SELECT id, nombre, email, rol, created_at FROM usuarios")->fetchAll();
    foreach ($data as $row) fputcsv($output, $row);
} elseif ($tipo == 'cursos') {
    fputcsv($output, ['ID', 'Nombre', 'Descripción', 'Año', 'Estudiantes']);
    $data = $pdo->query("SELECT c.id, c.nombre, c.descripcion, c.anio, COUNT(e.user_id) as estudiantes FROM cursos c LEFT JOIN estudiantes e ON c.id = e.curso_id GROUP BY c.id")->fetchAll();
    foreach ($data as $row) fputcsv($output, [$row['id'], $row['nombre'], $row['descripcion'], $row['anio'], $row['estudiantes']]);
}
fclose($output);
exit;