<?php
require_once '../includes/auth.php';
verificarSesion('profesor');
require_once '../includes/db.php';
$titulo_pagina = 'Mis Cuestionarios';
include '../includes/header.php';

$profesor_id = $_SESSION['user_id'];
$cuestionarios = obtenerCuestionariosPorProfesor($profesor_id);
?>
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <span>Cuestionarios creados</span>
        <a href="crear_cuestionario.php" class="btn btn-primary btn-sm">+ Nuevo cuestionario</a>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr><th>Título</th><th>Asignatura</th><th>Curso</th><th>Fechas</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                <?php foreach($cuestionarios as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['titulo']) ?></td>
                    <td><?= htmlspecialchars($c['asignatura_nombre']) ?></td>
                    <td><?= htmlspecialchars($c['curso_nombre']) ?></td>
                    <td><?= date('d/m/y H:i', strtotime($c['fecha_inicio'])) ?> - <?= date('d/m/y H:i', strtotime($c['fecha_fin'])) ?></td>
                    <td>
                        <a href="editar_preguntas.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-info">Preguntas</a>
                        <a href="ver_calificaciones.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-secondary">Calificaciones</a>
                        <a href="eliminar_cuestionario.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar?')">Eliminar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include '../includes/footer.php'; ?>