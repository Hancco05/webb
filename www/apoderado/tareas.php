<?php
require_once '../includes/auth.php';
verificarSesion('apoderado');
require_once '../includes/db.php';
$titulo_pagina = 'Tareas de mi Hijo';
include '../includes/header.php';

$estudiante_id = $_SESSION['hijo_actual'] ?? 0;
if (!$estudiante_id) {
    header("Location: hijos.php");
    exit;
}
$stmt = $pdo->prepare("SELECT e.curso_id FROM estudiantes e WHERE e.user_id = ?");
$stmt->execute([$estudiante_id]);
$curso_id = $stmt->fetchColumn();
$tareas = $curso_id ? obtenerTareasPorCurso($curso_id) : [];
?>
<div class="card"><div class="card-header">Tareas</div><div class="card-body">
    <?php if (empty($tareas)): ?><p>No hay tareas.</p><?php else: ?>
    <table class="table">
        <tr><th>Título</th><th>Asignatura</th><th>Fecha entrega</th><th>Descripción</th></tr>
        <?php foreach($tareas as $t): ?><tr><td><?= htmlspecialchars($t['titulo']) ?></td><td><?= htmlspecialchars($t['asignatura_nombre']) ?></td><td><?= $t['fecha_entrega'] ?></td><td><?= nl2br(htmlspecialchars($t['descripcion'])) ?></td></tr><?php endforeach; ?>
    </table><?php endif; ?>
</div></div>
<?php include '../includes/footer.php'; ?>