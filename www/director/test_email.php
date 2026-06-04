<?php
require_once '../includes/auth.php';
verificarSesion('director');
require_once '../includes/db.php';
$titulo_pagina = 'Prueba de Correo';
include '../includes/header.php';
$resultado = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $destino = $_POST['destino'];
    $asunto = $_POST['asunto'];
    $cuerpo = $_POST['cuerpo'];
    if (enviarCorreo($destino, $asunto, $cuerpo)) $resultado = '<div class="alert alert-success">Enviado</div>';
    else $resultado = '<div class="alert alert-danger">Error</div>';
}
?>
<div class="card"><div class="card-header">Prueba</div><div class="card-body"><?= $resultado ?><form method="POST"><div class="mb-2"><label>Destinatario</label><input type="email" name="destino" class="form-control" required></div><div class="mb-2"><label>Asunto</label><input type="text" name="asunto" class="form-control" value="Prueba"></div><div class="mb-2"><label>Contenido</label><textarea name="cuerpo" class="form-control" rows="4">Prueba</textarea></div><button type="submit" class="btn btn-primary">Enviar</button></form></div></div>
<?php include '../includes/footer.php'; ?>