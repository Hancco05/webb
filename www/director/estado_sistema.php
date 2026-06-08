<?php
require_once '../includes/auth.php';
verificarSesion('director');
require_once '../includes/db.php';
$titulo_pagina = 'Estado del Sistema';
include '../includes/header.php';
$tablas = ['usuarios', 'cursos', 'asignaturas', 'tareas', 'entregas', 'logs', 'asistencia', 'notas', 'cuestionarios', 'mensajes', 'eventos'];
?>
<div class="row">
    <?php foreach($tablas as $tabla): ?>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-secondary">
                <div class="card-body">
                    <h5><?= ucfirst($tabla) ?></h5>
                    <p class="display-6"><?= number_format($pdo->query("SELECT COUNT(*) FROM $tabla")->fetchColumn()) ?></p>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php include '../includes/footer.php'; ?>