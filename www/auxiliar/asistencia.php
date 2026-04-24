<?php
require_once '../includes/auth.php';
verificarSesion('auxiliar');
require_once '../includes/db.php';
$titulo_pagina = 'Registro de Asistencia (Auxiliar)';
include '../includes/header.php';

$curso_id = $_GET['curso_id'] ?? 0;
$fecha = $_GET['fecha'] ?? date('Y-m-d');
$cursos = $pdo->query("SELECT * FROM cursos ORDER BY anio, nombre")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['asistencia'])) {
    foreach($_POST['asistencia'] as $estudiante_id => $estado) {
        $stmt = $pdo->prepare("INSERT INTO asistencia (estudiante_id, fecha, estado, registrado_por) VALUES (?,?,?,?) 
                                ON DUPLICATE KEY UPDATE estado=?, registrado_por=?");
        $stmt->execute([$estudiante_id, $fecha, $estado, $_SESSION['user_id'], $estado, $_SESSION['user_id']]);
    }
    $_SESSION['mensaje'] = "Asistencia registrada";
    header("Location: asistencia.php?curso_id=$curso_id&fecha=$fecha");
    exit;
}

$estudiantes = [];
if($curso_id) {
    $estudiantes = obtenerEstudiantesPorCurso($curso_id);
}
$asistencias = [];
if($curso_id) {
    $stmt = $pdo->prepare("SELECT estudiante_id, estado FROM asistencia WHERE fecha=?");
    $stmt->execute([$fecha]);
    while($row = $stmt->fetch()) {
        $asistencias[$row['estudiante_id']] = $row['estado'];
    }
}
?>
<div class="row">
    <div class="col-md-3">
        <div class="card">
            <div class="card-header">Cursos</div>
            <ul class="list-group list-group-flush">
                <?php foreach($cursos as $c): ?>
                    <li class="list-group-item">
                        <a href="?curso_id=<?= $c['id'] ?>&fecha=<?= $fecha ?>"><?= htmlspecialchars($c['nombre']) ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="col-md-9">
        <?php if($curso_id): ?>
        <div class="card">
            <div class="card-header">
                Asistencia - Curso: <?= htmlspecialchars($cursos[array_search($curso_id, array_column($cursos, 'id'))]['nombre'] ?? '') ?> - Fecha: <input type="date" id="fecha" value="<?= $fecha ?>" onchange="cambiarFecha()">
            </div>
            <div class="card-body">
                <?php if(isset($_SESSION['mensaje'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['mensaje']; unset($_SESSION['mensaje']); ?></div>
                <?php endif; ?>
                <form method="POST">
                    <table class="table">
                        <thead><tr><th>Estudiante</th><th>Estado</th></tr></thead>
                        <tbody>
                            <?php foreach($estudiantes as $e): ?>
                            <tr>
                                <td><?= htmlspecialchars($e['nombre']) ?></td>
                                <td>
                                    <select name="asistencia[<?= $e['id'] ?>]" class="form-select">
                                        <option value="presente" <?= ($asistencias[$e['id']]??'')=='presente'?'selected':'' ?>>Presente</option>
                                        <option value="ausente" <?= ($asistencias[$e['id']]??'')=='ausente'?'selected':'' ?>>Ausente</option>
                                        <option value="tarde" <?= ($asistencias[$e['id']]??'')=='tarde'?'selected':'' ?>>Tarde</option>
                                    </select>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <button type="submit" class="btn btn-primary">Guardar Asistencia</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<script>
function cambiarFecha() {
    var fecha = document.getElementById('fecha').value;
    window.location.href = '?curso_id=<?= $curso_id ?>&fecha=' + fecha;
}
</script>
<?php include '../includes/footer.php'; ?>