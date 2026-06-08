<?php
require_once '../includes/auth.php';
verificarSesion('apoderado');
require_once '../includes/db.php';
$titulo_pagina = 'Horario de mi Hijo';
include '../includes/header.php';

$estudiante_id = $_SESSION['hijo_actual'] ?? 0;
if (!$estudiante_id) {
    header("Location: hijos.php");
    exit;
}
$stmt = $pdo->prepare("SELECT e.curso_id FROM estudiantes e WHERE e.user_id = ?");
$stmt->execute([$estudiante_id]);
$curso_id = $stmt->fetchColumn();
$horarios = $curso_id ? obtenerHorariosPorCurso($curso_id) : [];
?>
<div class="card"><div class="card-header">Horario</div><div class="card-body">
    <?php if (empty($horarios)): ?><p>No hay horarios registrados.</p><?php else: ?>
    <table class="table"><tr><th>Día</th><th>Hora inicio</th><th>Hora fin</th><th>Asignatura</th></tr>
    <?php foreach($horarios as $h): ?><tr><td><?= $h['dia_semana'] ?></td><td><?= $h['hora_inicio'] ?></td><td><?= $h['hora_fin'] ?></td><td><?= htmlspecialchars($h['asignatura_nombre']) ?></td></tr><?php endforeach; ?>
    </table><?php endif; ?>
</div></div>
<?php include '../includes/footer.php'; ?>