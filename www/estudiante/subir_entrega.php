<?php
require_once '../includes/auth.php';
verificarSesion('estudiante');
require_once '../includes/db.php';
$titulo_pagina = 'Subir Entrega';
include '../includes/header.php';
$tarea_id = $_GET['tarea_id'] ?? 0;
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT t.* FROM tareas t JOIN estudiantes e ON t.curso_id = e.curso_id WHERE t.id = ? AND e.user_id = ?");
$stmt->execute([$tarea_id, $user_id]);
$tarea = $stmt->fetch();
if(!$tarea) { echo "<div class='alert alert-danger'>Tarea no válida</div>"; include '../includes/footer.php'; exit; }
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $comentario = $_POST['comentario'] ?? '';
    if(isset($_FILES['archivo']) && $_FILES['archivo']['error']===UPLOAD_ERR_OK){
        $archivo = $_FILES['archivo'];
        $ext = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        $nombre_seguro = uniqid() . '_' . time() . '.' . $ext;
        $ruta = '/var/www/html/uploads/entregas/' . $nombre_seguro;
        if(move_uploaded_file($archivo['tmp_name'], $ruta)){
            registrarEntrega($tarea_id, $user_id, $archivo['name'], $nombre_seguro, $comentario);
            $_SESSION['mensaje'] = "Entrega subida";
            header("Location: tareas.php");
            exit;
        }
    }
}
?>
<div class="card"><div class="card-header">Subir entrega - <?= htmlspecialchars($tarea['titulo']) ?></div><div class="card-body"><form method="POST" enctype="multipart/form-data"><div class="mb-3"><label>Archivo</label><input type="file" name="archivo" class="form-control" required></div><div class="mb-3"><label>Comentario</label><textarea name="comentario" class="form-control"></textarea></div><button type="submit" class="btn btn-primary">Subir</button></form></div></div>
<?php include '../includes/footer.php'; ?>