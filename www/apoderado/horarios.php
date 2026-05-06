<?php
require_once '../includes/auth.php';
verificarSesion('apoderado');
require_once '../includes/db.php';
$titulo_pagina = 'Horario de mi hijo';
include '../includes/header.php';

$estudiante_id = $_SESSION['hijo_actual'] ?? 0;
if(!$estudiante_id) { header("Location: hijos.php"); exit; }

$stmt = $pdo->prepare("SELECT e.curso_id FROM estudiantes e WHERE e.user_id = ?");
$stmt->execute([$estudiante_id]);
$curso_id = $stmt->fetchColumn();

$horarios = obtenerHorariosPorCurso($curso_id);
?>
<div class="card"><div class="card-header">Horario</div><div class="card-body">
    <?php if(empty($horarios)): ?><p>No hay horarios registrados.</p><?php else: ?>
    <table class="table">...</table><?php endif; ?>
</div></div>
<?php include '../includes/footer.php'; ?>