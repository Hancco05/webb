<?php
require_once '../includes/auth.php';
verificarSesion('estudiante');
require_once '../includes/db.php';
$titulo_pagina = 'Mis Tareas';
include '../includes/header.php';

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT e.curso_id FROM estudiantes e WHERE e.user_id = ?");
$stmt->execute([$user_id]);
$curso_id = $stmt->fetchColumn();

if (!$curso_id) {
    echo '<div class="alert alert-warning">No tienes curso asignado.</div>';
    include '../includes/footer.php';
    exit;
}

$tareas = obtenerTareasPorCurso($curso_id);
?>
<div class="card">
    <div class="card-header">Tareas pendientes y próximas</div>
    <div class="card-body">
        <?php if (empty($tareas)): ?>
            <p>No hay tareas para tu curso.</p>
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