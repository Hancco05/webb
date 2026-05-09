<?php
require_once '../includes/auth.php';
verificarSesion('estudiante');
require_once '../includes/db.php';
$titulo_pagina = 'Entregar Tarea';
include '../includes/header.php';

$tarea_id = $_GET['id'] ?? 0;
if (!$tarea_id) {
    header("Location: tareas.php");
    exit;
}

// Verificar que la tarea existe y pertenece al curso del estudiante
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT e.curso_id FROM estudiantes e WHERE e.user_id = ?");
$stmt->execute([$user_id]);
$curso_id = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT * FROM tareas WHERE id = ? AND curso_id = ?");
$stmt->execute([$tarea_id, $curso_id]);
$tarea = $stmt->fetch();

if (!$tarea) {
    echo '<div class="alert alert-danger">Tarea no válida o no pertenece a tu curso.</div>';
    include '../includes/footer.php';
    exit;
}

$entrega = obtenerEntregaPorEstudianteTarea($user_id, $tarea_id);
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comentario = $_POST['comentario'] ?? '';
    
    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
        $archivo_tmp = $_FILES['archivo']['tmp_name'];
        $archivo_nombre = time() . '_' . basename($_FILES['archivo']['name']);
        $ruta_destino = '../uploads/entregas/' . $archivo_nombre;
        
        if (move_uploaded_file($archivo_tmp, $ruta_destino)) {
            if (guardarEntrega($tarea_id, $user_id, $archivo_nombre, $ruta_destino, $comentario)) {
                $mensaje = '<div class="alert alert-success">Tarea entregada correctamente.</div>';
                $entrega = obtenerEntregaPorEstudianteTarea($user_id, $tarea_id);
            } else {
                $mensaje = '<div class="alert alert-danger">Error al guardar en la base de datos.</div>';
            }
        } else {
            $mensaje = '<div class="alert alert-danger">Error al subir el archivo.</div>';
        }
    } else {
        $mensaje = '<div class="alert alert-warning">Debes seleccionar un archivo.</div>';
    }
}
?>
<div class="card">
    <div class="card-header">Entregar tarea: <?= htmlspecialchars($tarea['titulo']) ?></div>
    <div class="card-body">
        <?= $mensaje ?>
        <p><strong>Descripción:</strong> <?= nl2br(htmlspecialchars($tarea['descripcion'])) ?></p>
        <p><strong>Fecha de entrega límite:</strong> <?= $tarea['fecha_entrega'] ?></p>
        
        <?php if ($entrega): ?>
            <div class="alert alert-info">
                <strong>Tu entrega anterior:</strong>
                <a href="<?= $entrega['archivo_ruta'] ?>" target="_blank">Ver archivo subido</a>
                <br><strong>Comentario:</strong> <?= htmlspecialchars($entrega['comentario']) ?>
                <br><strong>Fecha de entrega:</strong> <?= $entrega['fecha_entrega'] ?>
                <?php if ($entrega['calificacion'] !== null): ?>
                    <br><strong>Calificación:</strong> <?= $entrega['calificacion'] ?>
                    <br><strong>Comentario del profesor:</strong> <?= htmlspecialchars($entrega['comentario_profesor']) ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label>Archivo (PDF, Word, Imagen, etc.)</label>
                <input type="file" name="archivo" class="form-control" <?= $entrega ? '' : 'required' ?>>
            </div>
            <div class="mb-3">
                <label>Comentario adicional</label>
                <textarea name="comentario" class="form-control" rows="3"><?= htmlspecialchars($entrega['comentario'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary"><?= $entrega ? 'Actualizar entrega' : 'Entregar tarea' ?></button>
            <a href="tareas.php" class="btn btn-secondary">Volver</a>
        </form>
    </div>
</div>
<?php include '../includes/footer.php'; ?>