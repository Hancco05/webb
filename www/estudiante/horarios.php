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

if (!$curso_id) { echo "<div class='alert alert-warning'>No tienes curso asignado</div>"; include '../includes/footer.php'; exit; }

$horarios = obtenerHorariosPorCurso($curso_id);
?>
<div class="card"><div class="card-header">Horario de clases</div><div class="card-body">
    <?php if(empty($horarios)): ?><p>No hay horarios registrados.</p><?php else: ?>
    <table class="table table-bordered"><thead><tr><th>Día</th><th>Hora inicio</th><th>Hora fin</th><th>Asignatura</th></tr></thead><tbody>
    <?php foreach($horarios as $h): ?><tr><td><?= $h['dia_semana'] ?></td><td><?= $h['hora_inicio'] ?></td><td><?= $h['hora_fin'] ?></td><td><?= htmlspecialchars($h['asignatura_nombre']) ?></td></tr><?php endforeach; ?>
    </tbody></table><?php endif; ?>
</div></div>
<?php include '../includes/footer.php'; ?>