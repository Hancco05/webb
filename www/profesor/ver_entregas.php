<?php
require_once '../includes/auth.php';
verificarSesion('profesor');
require_once '../includes/db.php';
$titulo_pagina = 'Ver entregas de tarea';
include '../includes/header.php';

$tarea_id = $_GET['id'] ?? 0;
if (!$tarea_id) {
    header("Location: tareas.php");
    exit;
}

// Verificar que la tarea pertenezca al profesor
$profesor_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM tareas WHERE id = ? AND creado_por = ?");
$stmt->execute([$tarea_id, $profesor_id]);
$tarea = $stmt->fetch();

if (!$tarea) {
    echo '<div class="alert alert-danger">No tienes permiso para ver esta tarea.</div>';
    include '../includes/footer.php';
    exit;
}

$entregas = obtenerEntregasPorTarea($tarea_id);
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entrega_id = $_POST['entrega_id'];
    $calificacion = $_POST['calificacion'];
    $comentario = $_POST['comentario_profesor'];
    if (calificarEntrega($entrega_id, $calificacion, $comentario)) {
        $mensaje = '<div class="alert alert-success">Calificación guardada.</div>';
        $entregas = obtenerEntregasPorTarea($tarea_id); // refrescar
    }
}
?>
<div class="card">
    <div class="card-header">Entregas de: <?= htmlspecialchars($tarea['titulo']) ?></div>
    <div class="card-body">
        <?= $mensaje ?>
        <?php if (empty($entregas)): ?>
            <p>No hay entregas todavía.</p>
        <?php else: ?>
            <table class="table table-bordered">
                <thead>
                    <tr><th>Estudiante</th><th>Archivo</th><th>Comentario</th><th>Fecha entrega</th><th>Calificación</th><th>Comentario prof.</th><th>Acción</th></tr>
                </thead>
                <tbody>
                    <?php foreach($entregas as $e): ?>
                    <tr>
                        <td><?= htmlspecialchars($e['estudiante_nombre']) ?></td>
                        <td><a href="<?= $e['archivo_ruta'] ?>" target="_blank">Ver archivo</a></td>
                        <td><?= htmlspecialchars($e['comentario']) ?></td>
                        <td><?= $e['fecha_entrega'] ?></td>
                        <form method="POST">
                            <input type="hidden" name="entrega_id" value="<?= $e['id'] ?>">
                            <td><input type="number" step="0.01" name="calificacion" class="form-control" value="<?= $e['calificacion'] ?>" style="width:80px"></td>
                            <td><input type="text" name="comentario_profesor" class="form-control" value="<?= htmlspecialchars($e['comentario_profesor']) ?>"></td>
                            <td><button type="submit" class="btn btn-sm btn-primary">Guardar</button></td>
                        </form>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <a href="tareas.php" class="btn btn-secondary">Volver a mis tareas</a>
    </div>
</div>
<?php include '../includes/footer.php'; ?>