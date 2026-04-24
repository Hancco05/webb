<?php
require_once '../includes/auth.php';
verificarSesion('profesor');
require_once '../includes/db.php';
$titulo_pagina = 'Mis Cursos';
include '../includes/header.php';

$curso_id = $_GET['curso_id'] ?? 0;
$cursos = obtenerCursosPorProfesor($_SESSION['user_id']);
$asignaturas = [];
if ($curso_id) {
    $asignaturas = obtenerAsignaturasPorProfesorCurso($_SESSION['user_id'], $curso_id);
}
?>
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">Mis Cursos</div>
            <ul class="list-group list-group-flush">
                <?php foreach($cursos as $c): ?>
                    <li class="list-group-item">
                        <a href="?curso_id=<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="col-md-8">
        <?php if($curso_id): ?>
            <div class="card">
                <div class="card-header">Asignaturas del curso</div>
                <ul class="list-group">
                    <?php foreach($asignaturas as $a): ?>
                        <li class="list-group-item">
                            <?= htmlspecialchars($a['nombre']) ?> (<?= $a['codigo'] ?>)
                            <a href="notas.php?asignatura_id=<?= $a['id'] ?>&curso_id=<?= $curso_id ?>" class="btn btn-sm btn-primary float-end">Gestionar Notas</a>
                            <a href="asistencia.php?curso_id=<?= $curso_id ?>" class="btn btn-sm btn-secondary float-end me-2">Asistencia</a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php include '../includes/footer.php'; ?>