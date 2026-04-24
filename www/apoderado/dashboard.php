<?php
require_once '../includes/auth.php';
verificarSesion('apoderado');
require_once '../includes/db.php';
$titulo_pagina = 'Dashboard Apoderado';
include '../includes/header.php';
$hijos = obtenerHijos($_SESSION['user_id']);
?>
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Mis Hijos</div>
            <ul class="list-group list-group-flush">
                <?php foreach($hijos as $h): ?>
                    <li class="list-group-item">
                        <?= htmlspecialchars($h['nombre']) ?> - Curso: <?= htmlspecialchars($h['curso_nombre']) ?>
                        <a href="hijos.php?estudiante_id=<?= $h['id'] ?>" class="btn btn-sm btn-primary float-end">Ver detalles</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>