<?php
require_once '../includes/auth.php';
verificarSesion('estudiante');
require_once '../includes/db.php';
$titulo_pagina = 'Subir entrega';
include '../includes/header.php';

$tarea_id = $_GET['tarea_id'] ?? 0;
if (!$tarea_id) {
    header("Location: tareas.php");
    exit;
}

// Verificar que la tarea existe y pertenece al curso del estudiante
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT t.* FROM tareas t JOIN estudiantes e ON t.curso_id = e.curso_id WHERE t.id = ? AND e.user_id = ?");
$stmt->execute([$tarea_id, $user_id]);
$tarea = $stmt->fetch();
if (!$tarea) {
    echo "<div class='alert alert-danger'>Tarea no válida.</div>";
    include '../includes/footer.php';
    exit;
}

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comentario = $_POST['comentario'] ?? '';
    
    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
        $archivo = $_FILES['archivo'];
        $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        $nombre_seguro = uniqid() . '_' . time() . '.' . $extension;
        $ruta_destino = '/var/www/html/uploads/entregas/' . $nombre_seguro;
        
        if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
            $ruta_relativa = $nombre_seguro;
            if (registrarEntrega($tarea_id, $user_id, $archivo['name'], $ruta_relativa, $comentario)) {
                $mensaje = '<div class="alert alert-success">Entrega subida correctamente.</div>';
            } else {
                $mensaje = '<div class="alert alert-danger">Error al guardar en base de datos.</div>';
            }
        } else {
            $mensaje = '<div class="alert alert-danger">Error al mover el archivo.</div>';
        }
    } else {
        $mensaje = '<div class="alert alert-danger">Debes seleccionar un archivo.</div>';
    }
}
?>
<div class="card">
    <div class="card-header">Subir entrega - <?= htmlspecialchars($tarea['titulo']) ?></div>
    <div class="card-body">
        <?= $mensaje ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3"><label>Archivo (PDF, Word, ZIP, imagen)</label><input type="file" name="archivo" class="form-control" required></div>
            <div class="mb-3"><label>Comentario (opcional)</label><textarea name="comentario" class="form-control" rows="3"></textarea></div>
            <button type="submit" class="btn btn-primary">Subir entrega</button>
            <a href="tareas.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
<?php include '../includes/footer.php'; ?>