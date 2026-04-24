<?php
require_once '../includes/auth.php';
verificarSesion('apoderado');
require_once '../includes/db.php';
$titulo_pagina = 'Asistencia de mi Hijo';
include '../includes/header.php';

$estudiante_id = $_SESSION['hijo_actual'] ?? 0;
if(!$estudiante_id) {
    header("Location: hijos.php");
    exit;
}
$stmt = $pdo->prepare("SELECT fecha, estado FROM asistencia WHERE estudiante_id = ? ORDER BY fecha DESC");
$stmt->execute([$estudiante_id]);
$asistencias = $stmt->fetchAll();
?>
<div class="card">
    <div class="card-header">Asistencia</div>
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