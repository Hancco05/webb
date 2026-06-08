<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
$titulo_pagina = 'Ver mensaje';
include '../includes/header.php';
$id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];
$mensaje = obtenerMensaje($id);
if (!$mensaje || ($mensaje['to_user_id'] != $user_id && $mensaje['from_user_id'] != $user_id)) { die("No autorizado"); }
if ($mensaje['to_user_id'] == $user_id && !$mensaje['is_read']) marcarMensajeComoLeido($id, $user_id);
$respuestas = obtenerRespuestas($id);
?>
<div class="card"><div class="card-header"><strong><?= htmlspecialchars($mensaje['asunto']) ?></strong> <span class="float-end"><?= $mensaje['created_at'] ?></span></div><div class="card-body">
    <p><strong>De:</strong> <?= htmlspecialchars($mensaje['remitente_nombre']) ?> (<?= $mensaje['remitente_rol'] ?>)</p>
    <p><strong>Para:</strong> <?= htmlspecialchars($mensaje['destinatario_nombre']) ?> (<?= $mensaje['destinatario_rol'] ?>)</p>
    <hr><p><?= nl2br(htmlspecialchars($mensaje['mensaje'])) ?></p>
</div><div class="card-footer"><a href="enviar_mensaje.php?responder=<?= $id ?>" class="btn btn-primary">Responder</a> <a href="mensajes.php" class="btn btn-secondary">Volver</a></div></div>
<?php if (!empty($respuestas)): ?><div class="card mt-3"><div class="card-header">Respuestas</div><div class="card-body"><?php foreach($respuestas as $r): ?><div class="border-bottom mb-2 pb-2"><strong><?= htmlspecialchars($r['remitente_nombre']) ?></strong> - <?= $r['created_at'] ?><p><?= nl2br(htmlspecialchars($r['mensaje'])) ?></p></div><?php endforeach; ?></div></div><?php endif; ?>
<?php include '../includes/footer.php'; ?>