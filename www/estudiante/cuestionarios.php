<?php
require_once '../includes/auth.php';
verificarSesion('estudiante');
require_once '../includes/db.php';
$titulo_pagina = 'Evaluaciones disponibles';
include '../includes/header.php';

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT curso_id FROM estudiantes WHERE user_id = ?");
$stmt->execute([$user_id]);
$curso_id = $stmt->fetchColumn();
$cuestionarios = $curso_id ? obtenerCuestionariosPorCurso($curso_id) : [];
?>
<div class="card">
    <div class="card-header">Cuestionarios disponibles</div>
    <div class="card-body">
        <table class="table">
            <thead><tr><th>Título</th><th>Asignatura</th><th>Fechas</th><th>Intentos</th><th>Acción</th></tr></thead>
            <tbody>
                <?php foreach($cuestionarios as $c):
                    $calif = obtenerCalificacionEstudiante($c['id'], $user_id);
                ?>
                <tr>
                    <td><?= htmlspecialchars($c['titulo']) ?></td>
                    <td><?= htmlspecialchars($c['asignatura_nombre']) ?></td>
                    <td><?= date('d/m H:i', strtotime($c['fecha_inicio'])) ?> - <?= date('d/m H:i', strtotime($c['fecha_fin'])) ?></td>
                    <td><?= $calif ? "Intentos: {$calif['intentos']}/{$c['intentos_permitidos']}" : "0/{$c['intentos_permitidos']}" ?></td>
                    <td>
                        <?php if ($calif && $calif['intentos'] >= $c['intentos_permitidos']): ?>
                            <span class="badge bg-secondary">Completado</span>
                        <?php elseif (strtotime($c['fecha_inicio']) > time()): ?>
                            <span class="badge bg-warning">Próximamente</span>
                        <?php elseif (strtotime($c['fecha_fin']) < time()): ?>
                            <span class="badge bg-danger">Vencido</span>
                        <?php else: ?>
                            <a href="responder_cuestionario.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-primary">Responder</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include '../includes/footer.php'; ?>