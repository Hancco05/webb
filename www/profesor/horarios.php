<?php
require_once '../includes/auth.php';
verificarSesion('profesor');
require_once '../includes/db.php';
$titulo_pagina = 'Gestión de Horarios';
include '../includes/header.php';

$profesor_id = $_SESSION['user_id'];
$cursos = obtenerCursosPorProfesor($profesor_id);
$curso_id = $_GET['curso_id'] ?? 0;
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verificarTokenCSRF($_POST['csrf_token'])) { die('CSRF inválido'); }
    if (isset($_POST['guardar'])) {
        $dia = $_POST['dia'];
        $hora_inicio = $_POST['hora_inicio'];
        $hora_fin = $_POST['hora_fin'];
        $asignatura_id = $_POST['asignatura_id'];
        if (guardarHorario($curso_id, $dia, $hora_inicio, $hora_fin, $asignatura_id)) {
            $mensaje = 'Horario guardado';
        } else { $mensaje = 'Error'; }
    } elseif (isset($_POST['eliminar'])) {
        $id = $_POST['id'];
        if (eliminarHorario($id)) { $mensaje = 'Eliminado'; }
    }
}

$horarios = [];
$asignaturas = [];
if ($curso_id) {
    $horarios = obtenerHorariosPorCurso($curso_id);
    $asignaturas = obtenerAsignaturasPorProfesorCurso($profesor_id, $curso_id);
}
?>
<div class="row">
    <div class="col-md-3">
        <div class="card"><div class="card-header">Mis Cursos</div><ul class="list-group"><?php foreach($cursos as $c): ?><li class="list-group-item"><a href="?curso_id=<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></a></li><?php endforeach; ?></ul></div>
    </div>
    <div class="col-md-9">
        <?php if($curso_id): ?>
            <div class="card"><div class="card-header">Horarios - Curso <?= htmlspecialchars($cursos[array_search($curso_id, array_column($cursos,'id'))]['nombre'] ?? '') ?></div>
            <div class="card-body">
                <?php if($mensaje): ?><div class="alert alert-info"><?= $mensaje ?></div><?php endif; ?>
                <form method="POST" class="row g-3 mb-4">
                    <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
                    <div class="col-md-3"><select name="dia" class="form-select" required><option>Lunes</option><option>Martes</option><option>Miércoles</option><option>Jueves</option><option>Viernes</option></select></div>
                    <div class="col-md-2"><input type="time" name="hora_inicio" class="form-control" required></div>
                    <div class="col-md-2"><input type="time" name="hora_fin" class="form-control" required></div>
                    <div class="col-md-3"><select name="asignatura_id" class="form-select" required><?php foreach($asignaturas as $a): ?><option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['nombre']) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-2"><button type="submit" name="guardar" class="btn btn-primary">Agregar</button></div>
                </form>
                <table class="table table-bordered">
                    <thead><tr><th>Día</th><th>Hora inicio</th><th>Hora fin</th><th>Asignatura</th><th>Acción</th></tr></thead>
                    <tbody><?php foreach($horarios as $h): ?><tr><td><?= $h['dia_semana'] ?></td><td><?= $h['hora_inicio'] ?></td><td><?= $h['hora_fin'] ?></td><td><?= htmlspecialchars($h['asignatura_nombre']) ?></td><td><form method="POST"><input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>"><input type="hidden" name="id" value="<?= $h['id'] ?>"><button type="submit" name="eliminar" class="btn btn-danger btn-sm">Eliminar</button></form></td></tr><?php endforeach; ?></tbody>
                </table>
            </div></div>
        <?php endif; ?>
    </div>
</div>
<?php include '../includes/footer.php'; ?>