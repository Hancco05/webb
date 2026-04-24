<?php
require_once '../includes/auth.php';
verificarSesion('estudiante');
require_once '../includes/db.php';
$titulo_pagina = 'Dashboard Estudiante';
include '../includes/header.php';
$user_id = $_SESSION['user_id'];
// Obtener curso del estudiante
$stmt = $pdo->prepare("SELECT c.nombre as curso FROM estudiantes e JOIN cursos c ON e.curso_id = c.id WHERE e.user_id = ?");
$stmt->execute([$user_id]);
$curso = $stmt->fetchColumn();
?>
<div class="alert alert-info">Bienvenido, <?= $_SESSION['nombre'] ?> - Curso: <?= htmlspecialchars($curso) ?></div>
<div class="row">
    <div class="col-md-4">
        <div class="card text-white bg-primary mb-3">
            <div class="card-body">
                <h5>Mis Notas</h5>
                <a href="mis_notas.php" class="btn btn-light">Ver</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-success mb-3">
            <div class="card-body">
                <h5>Mi Asistencia</h5>
                <a href="asistencia.php" class="btn btn-light">Ver</a>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>