<?php
require_once '../includes/auth.php';
verificarSesion('director');
require_once '../includes/db.php';
$titulo_pagina = 'Estado del sistema';
include '../includes/header.php';

// Estadísticas de tablas
$tablas = ['usuarios', 'cursos', 'asignaturas', 'tareas', 'entregas', 'logs', 'asistencia', 'notas'];
?>
<div class="row">
    <?php foreach($tablas as $tabla): ?>
        <?php $count = $pdo->query("SELECT COUNT(*) FROM $tabla")->fetchColumn(); ?>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-secondary">
                <div class="card-body">
                    <h5><?= ucfirst($tabla) ?></h5>
                    <p class="display-6"><?= number_format($count) ?></p>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<div class="card">
    <div class="card-header">Variables de configuración</div>
    <div class="card-body">
        <?php
        $vars = $pdo->query("SHOW VARIABLES LIKE 'innodb_buffer_pool_size'")->fetch();
        echo "<p>innodb_buffer_pool_size: " . round($vars['Value']/1024/1024) . " MB</p>";
        ?>
    </div>
</div>
<?php include '../includes/footer.php'; ?>