<?php
require_once '../includes/auth.php';
verificarSesion('apoderado');
require_once '../includes/db.php';
$titulo_pagina = 'Seleccionar Hijo';
include '../includes/header.php';

$estudiante_id = $_GET['estudiante_id'] ?? 0;
if($estudiante_id) {
    $_SESSION['hijo_actual'] = $estudiante_id;
    header("Location: notas.php");
    exit;
}
$hijos = obtenerHijos($_SESSION['user_id']);
?>
<div class="card">
    <div class="card-header">Seleccione un hijo para ver su información</div>
    <div class="card-body">
        <div class="list-group">
            <?php foreach($hijos as $h): ?>
                <a href="?estudiante_id=<?= $h['id'] ?>" class="list-group-item list-group-item-action">
                    <?= htmlspecialchars($h['nombre']) ?> - <?= htmlspecialchars($h['curso_nombre']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>