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
$tareas = $curso_id ? obtenerTareasPorCurso($curso_id) : [];
?>
<div class="card"><div class="card-header">Tareas</div><div class="card-body"><table class="table"><?php foreach($tareas as $t): $entrega = obtenerEntregaEstudiante($t['id'], $user_id); ?><tr><td><strong><?= htmlspecialchars($t['titulo']) ?></strong><br><?= htmlspecialchars($t['asignatura_nombre']) ?><br>Entrega: <?= $t['fecha_entrega'] ?><br><?= nl2br(htmlspecialchars($t['descripcion'])) ?></td><td><?php if($entrega): ?>Entregada: <a href="/uploads/entregas/<?= $entrega['archivo_ruta'] ?>" target="_blank">Ver</a><?php else: ?><a href="subir_entrega.php?tarea_id=<?= $t['id'] ?>" class="btn btn-primary btn-sm">Subir entrega</a><?php endif; ?></td></tr><?php endforeach; ?></table></div></div>
<?php include '../includes/footer.php'; ?>