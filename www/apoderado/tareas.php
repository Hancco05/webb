<?php
require_once '../includes/auth.php';
verificarSesion('apoderado');
require_once '../includes/db.php';
$titulo_pagina = 'Tareas de mi hijo';
include '../includes/header.php';

$estudiante_id = $_SESSION['hijo_actual'] ?? 0;
if (!$estudiante_id) {
    header("Location: hijos.php");
    exit;
}

$stmt = $pdo->prepare("SELECT e.curso_id FROM estudiantes e WHERE e.user_id = ?");
$stmt->execute([$estudiante_id]);
$curso_id = $stmt->fetchColumn();

if (!$curso_id) {
    echo '<div class="alert alert-warning">El estudiante no tiene curso asignado.</div>';
    include '../includes/footer.php';
    exit;
}

$tareas = obtenerTareasPorCurso($curso_id);
?>
<div class="card">
    <div class="card-header">Tareas de mi hijo</div>
    <div class="card-body">
        <?php if (empty($tareas)): ?>
            <p>No hay tareas para este curso.</p>
        <?php else: ?>
            <table class="table table-bordered">
                <thead>
                    <tr><th>Título</th><th>Asignatura</th><th>Fecha entrega</th><th>Descripción</th></tr>
                </thead>
                <tbody>
                    <?php foreach($tareas as $t): ?>
                    <tr>
                        <td><?= htmlspecialchars($t['titulo']) ?></td>
                        <td><?= htmlspecialchars($t['asignatura_nombre']) ?></td>
                        <td><?= $t['fecha_entrega'] ?></td>
                        <td><?= nl2br(htmlspecialchars($t['descripcion'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
<?php include '../includes/footer.php'; ?>