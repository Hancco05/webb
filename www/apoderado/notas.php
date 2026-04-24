<?php
require_once '../includes/auth.php';
verificarSesion('apoderado');
require_once '../includes/db.php';
$titulo_pagina = 'Notas de mi Hijo';
include '../includes/header.php';

$estudiante_id = $_SESSION['hijo_actual'] ?? 0;
if(!$estudiante_id) {
    header("Location: hijos.php");
    exit;
}
$stmt = $pdo->prepare("
    SELECT a.nombre as asignatura, n.periodo, n.nota 
    FROM notas n
    JOIN asignaturas a ON n.asignatura_id = a.id
    WHERE n.estudiante_id = ?
    ORDER BY a.nombre, FIELD(n.periodo, '1er Bimestre','2do Bimestre','3er Bimestre','4to Bimestre')
");
$stmt->execute([$estudiante_id]);
$notas = $stmt->fetchAll();
?>
<div class="card">
    <div class="card-header">Notas</div>
    <div class="card-body">
        <table class="table">
            <thead><tr><th>Asignatura</th><th>Período</th><th>Nota</th></tr></thead>
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
    </div>
</div>
<?php include '../includes/footer.php'; ?>