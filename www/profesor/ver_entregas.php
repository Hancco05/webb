<?php
require_once '../includes/auth.php';
verificarSesion('profesor');
require_once '../includes/db.php';
$titulo_pagina = 'Calificaciones del Cuestionario';
include '../includes/header.php';

$cuestionario_id = $_GET['id'] ?? 0;
$cuestionario = obtenerCuestionario($cuestionario_id);
if (!$cuestionario || $cuestionario['profesor_id'] != $_SESSION['user_id']) die("No autorizado");

$estudiantes = obtenerEstudiantesPorCurso($cuestionario['curso_id']);
$calificaciones = [];
foreach ($estudiantes as $e) {
    $calif = obtenerCalificacionEstudiante($cuestionario_id, $e['id']);
    $calificaciones[$e['id']] = $calif ? $calif['puntaje_obtenido'] . '/' . $calif['puntaje_total'] : 'No respondido';
}
?>
<div class="card">
    <div class="card-header">Calificaciones - <?= htmlspecialchars($cuestionario['titulo']) ?></div>
    <div class="card-body">
        <table class="table">
            <thead><tr><th>Estudiante</th><th>Calificación</th></tr></thead>
            <tbody>
                <?php foreach ($estudiantes as $e): ?>
                <tr>
                    <td><?= htmlspecialchars($e['nombre']) ?></td>
                    <td><?= $calificaciones[$e['id']] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include '../includes/footer.php'; ?>