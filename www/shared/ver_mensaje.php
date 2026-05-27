<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
$user_id = $_SESSION['user_id'];
$id = $_GET['id'] ?? 0;
$vista = $_GET['vista'] ?? 'recibidos';

$mensaje = obtenerMensaje($id, $user_id);
if (!$mensaje) {
    header("Location: mensajes.php");
    exit;
}

// Marcar como leído si es destinatario
if ($mensaje['destinatario_id'] == $user_id && !$mensaje['leido']) {
    marcarMensajeLeido($id, $user_id);
    $mensaje['leido'] = 1;
}

$titulo_pagina = 'Mensaje: ' . htmlspecialchars($mensaje['asunto']);
include '../includes/header.php';
?>
<div class="card">
    <div class="card-header">
        <strong>De:</strong> <?= htmlspecialchars($mensaje['remitente_nombre']) ?> &nbsp;|&nbsp;
        <strong>Para:</strong> <?= htmlspecialchars(obtenerNombreUsuario($mensaje['destinatario_id'])) ?> &nbsp;|&nbsp;
        <strong>Fecha:</strong> <?= $mensaje['fecha_envio'] ?>
    </div>
    <div class="card-body">
        <h5>Asunto: <?= htmlspecialchars($mensaje['asunto']) ?></h5>
        <hr>
        <p><?= nl2br(htmlspecialchars($mensaje['mensaje'])) ?></p>
    </div>
    <div class="card-footer">
        <a href="mensajes.php?vista=<?= $vista ?>" class="btn btn-secondary">Volver</a>
        <a href="nuevo_mensaje.php?responder=<?= $mensaje['remitente_id'] ?>" class="btn btn-primary">Responder</a>
    </div>
</div>
<?php include '../includes/footer.php'; ?>