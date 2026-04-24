<?php
require_once '../includes/auth.php';
verificarSesion('estudiante');
require_once '../includes/db.php';
$titulo_pagina = 'Mi Asistencia';
include '../includes/header.php';
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT fecha, estado FROM asistencia WHERE estudiante_id = ? ORDER BY fecha DESC");
$stmt->execute([$user_id]);
$asistencias = $stmt->fetchAll();
?>
<div class="card">
    <div class="card-header">Mi Asistencia</div>
    <div class="card-body">
        <table class="table">
            <thead><tr><th>Fecha</th><th>Estado</th></tr></thead>
            <tbody>
                <?php foreach($asistencias as $a): ?>
                <tr>
                    <td><?= $a['fecha'] ?></td>
                    <td><?= ucfirst($a['estado']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include '../includes/footer.php'; ?>