<?php
require_once '../includes/auth.php';
verificarSesion('estudiante');
require_once '../includes/db.php';
$titulo_pagina = 'Mi Horario';
include '../includes/header.php';
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT e.curso_id FROM estudiantes e WHERE e.user_id = ?");
$stmt->execute([$user_id]);
$curso_id = $stmt->fetchColumn();
$horarios = $curso_id ? obtenerHorariosPorCurso($curso_id) : [];
?>
<div class="card"><div class="card-header">Horario de clases</div><div class="card-body"><?php if(empty($horarios)): ?><p>No hay horarios registrados.</p><?php else: ?><table class="table"><?php foreach($horarios as $h): ?><tr><td><?= $h['dia_semana'] ?></td><td><?= $h['hora_inicio'] ?> - <?= $h['hora_fin'] ?></td><td><?= htmlspecialchars($h['asignatura_nombre']) ?></td></tr><?php endforeach; ?></table><?php endif; ?></div></div>
<?php include '../includes/footer.php'; ?>