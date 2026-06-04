<?php
require_once '../includes/auth.php';
verificarSesion('director');
require_once '../includes/db.php';
$titulo_pagina = 'Enviar Recordatorio';
include '../includes/header.php';
$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'];
    $contenido = $_POST['contenido'];
    $apoderados = $pdo->query("SELECT email, nombre FROM usuarios WHERE rol='apoderado'")->fetchAll();
    $enviados = 0;
    foreach ($apoderados as $apo) {
        $cuerpo = "<h2>$titulo</h2><p>$contenido</p><p>Dirección</p>";
        try {
            enviarCorreo($apo['email'], $titulo, $cuerpo);
            $enviados++;
        } catch (Exception $e) { error_log("Correo fallido: ".$e->getMessage()); }
    }
    $mensaje = "Enviado a $enviados apoderados.";
}
?>
<div class="card"><div class="card-header">Enviar recordatorio masivo</div><div class="card-body"><?= $mensaje ?><form method="POST"><div class="mb-3"><label>Título</label><input type="text" name="titulo" class="form-control" required></div><div class="mb-3"><label>Mensaje</label><textarea name="contenido" class="form-control" rows="5" required></textarea></div><button type="submit" class="btn btn-primary">Enviar</button></form></div></div>
<?php include '../includes/footer.php'; ?>