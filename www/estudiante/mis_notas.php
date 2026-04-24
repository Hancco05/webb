<?php
require_once '../includes/auth.php';
verificarSesion('estudiante');
require_once '../includes/db.php';
$titulo_pagina = 'Mis Notas';
include '../includes/header.php';
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT a.nombre as asignatura, n.periodo, n.nota 
    FROM notas n
    JOIN asignaturas a ON n.asignatura_id = a.id
    WHERE n.estudiante_id = ?
    ORDER BY a.nombre, FIELD(n.periodo, '1er Bimestre','2do Bimestre','3er Bimestre','4to Bimestre')
");
$stmt->execute([$user_id]);
$notas = $stmt->fetchAll();
?>
<div class="card">
    <div class="card-header">Mis Notas</div>
    <div class="card-body">
        <?php if(count($notas) == 0): ?>
            <p>No hay notas registradas aún.</p>
        <?php else: ?>
            <table class="table table-bordered">
                <thead>
                    <tr><th>Asignatura</th><th>Período</th><th>Nota</th></tr>
                </thead>
                <tbody>
                    <?php foreach($notas as $n): ?>
                    <tr>
                        <td><?= htmlspecialchars($n['asignatura']) ?></td>
                        <td><?= $n['periodo'] ?></td>
                        <td><?= $n['nota'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
<?php include '../includes/footer.php'; ?>