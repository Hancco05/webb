<?php
require_once '../includes/auth.php';
verificarSesion('profesor');
require_once '../includes/db.php';
$titulo_pagina = 'Cuestionarios';
include '../includes/header.php';

$profesor_id = $_SESSION['user_id'];
$cuestionarios = obtenerCuestionariosPorProfesor($profesor_id);
?>
<div class="card"><div class="card-header d-flex justify-content-between"><span>Cuestionarios</span><a href="crear_cuestionario.php" class="btn btn-primary btn-sm">+ Nuevo</a></div><div class="card-body">
    <table class="table"><?php foreach($cuestionarios as $c): ?><tr><td><strong><?= htmlspecialchars($c['titulo']) ?></strong><br><?= htmlspecialchars($c['asignatura_nombre']) ?> - <?= htmlspecialchars($c['curso_nombre']) ?></td><td><a href="editar_preguntas.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-info">Preguntas</a> <a href="ver_calificaciones.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-secondary">Calificaciones</a> <a href="eliminar_cuestionario.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar?')">Eliminar</a></td></tr><?php endforeach; ?></table>
</div></div>
<?php include '../includes/footer.php'; ?>