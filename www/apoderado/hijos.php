<?php
require_once '../includes/auth.php';
verificarSesion('apoderado');
require_once '../includes/db.php';
$titulo_pagina = 'Mis Hijos';
include '../includes/header.php';
$hijos = obtenerHijos($_SESSION['user_id']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['hijo_actual'] = $_POST['estudiante_id'];
    header("Location: notas.php");
    exit;
}
?>
<div class="card"><div class="card-header">Seleccione un hijo</div><div class="card-body"><form method="POST"><?php foreach($hijos as $h): ?><div class="mb-2"><input type="radio" name="estudiante_id" value="<?= $h['id'] ?>" required> <?= htmlspecialchars($h['nombre']) ?> - <?= htmlspecialchars($h['curso_nombre']) ?></div><?php endforeach; ?><button type="submit" class="btn btn-primary">Ver información</button></form></div></div>
<?php include '../includes/footer.php'; ?>