<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
$titulo_pagina = 'Nuevo mensaje';
include '../includes/header.php';

$user_id = $_SESSION['user_id'];
$roles_permitidos = ['director', 'profesor', 'auxiliar', 'estudiante', 'apoderado'];
$destinatarios = $pdo->query("SELECT id, nombre, rol FROM usuarios WHERE id != $user_id ORDER BY rol, nombre")->fetchAll();

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $destinatario_id = $_POST['destinatario_id'];
    $asunto = $_POST['asunto'];
    $cuerpo = $_POST['mensaje'];
    if (enviarMensaje($user_id, $destinatario_id, $asunto, $cuerpo)) {
        $mensaje = '<div class="alert alert-success">Mensaje enviado correctamente.</div>';
    } else {
        $mensaje = '<div class="alert alert-danger">Error al enviar mensaje.</div>';
    }
}
?>
<div class="card">
    <div class="card-header">Nuevo mensaje</div>
    <div class="card-body">
        <?= $mensaje ?>
        <form method="POST">
            <div class="mb-3"><label>Para:</label><select name="destinatario_id" class="form-select" required><option value="">Seleccione</option><?php foreach($destinatarios as $d): ?><option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['nombre']) ?> (<?= $d['rol'] ?>)</option><?php endforeach; ?></select></div>
            <div class="mb-3"><label>Asunto:</label><input type="text" name="asunto" class="form-control" required></div>
            <div class="mb-3"><label>Mensaje:</label><textarea name="mensaje" class="form-control" rows="5" required></textarea></div>
            <button type="submit" class="btn btn-primary">Enviar</button>
            <a href="mensajes.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
<?php include '../includes/footer.php'; ?>