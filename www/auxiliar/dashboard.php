<?php
require_once '../includes/auth.php';
verificarSesion('auxiliar');
require_once '../includes/db.php';
$titulo_pagina = 'Dashboard Auxiliar';
include '../includes/header.php';
?>
<div class="alert alert-info">Bienvenido, <?= $_SESSION['nombre'] ?>. Puede registrar asistencia.</div>
<div class="row"><div class="col-md-6"><div class="card"><div class="card-body"><h5>Registro de Asistencia</h5><a href="asistencia.php" class="btn btn-primary">Ir a Asistencia</a></div></div></div></div>
<?php include '../includes/footer.php'; ?>