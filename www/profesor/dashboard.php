<?php
require_once '../includes/auth.php';
verificarSesion('profesor');
require_once '../includes/db.php';
$titulo_pagina = 'Dashboard Profesor';
include '../includes/header.php';
$cursos = obtenerCursosPorProfesor($_SESSION['user_id']);
?>
<div class="row">
    <?php foreach($cursos as $curso): ?>
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($curso['nombre']) ?></h5>
                    <p class="card-text"><?= htmlspecialchars($curso['descripcion']) ?></p>
                    <a href="mis_cursos.php?curso_id=<?= $curso['id'] ?>" class="btn btn-primary">Ver detalles</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php include '../includes/footer.php'; ?>