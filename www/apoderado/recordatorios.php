<?php
require_once '../includes/auth.php';
verificarSesion('apoderado');
require_once '../includes/db.php';
$titulo_pagina = 'Recordatorios';
include '../includes/header.php';

$stmt = $pdo->prepare("SELECT * FROM recordatorios WHERE para_rol IN ('apoderado', 'todos') ORDER BY fecha_recordatorio DESC");
$stmt->execute();
$recordatorios = $stmt->fetchAll();
?>
<div class="card">
    <div class="card-header">Recordatorios</div>
    <div class="card-body">
        <?php foreach($recordatorios as $r): ?>
            <div class="alert alert-warning">
                <strong><?= htmlspecialchars($r['titulo']) ?></strong><br>
                <?= nl2br(htmlspecialchars($r['descripcion'])) ?><br>
                <small>Fecha: <?= $r['fecha_recordatorio'] ?></small>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php include '../includes/footer.php'; ?>