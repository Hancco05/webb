<?php
require_once '../includes/auth.php';
verificarSesion('director');
require_once '../includes/db.php';
$titulo_pagina = 'Dashboard Director';
include '../includes/header.php';

// Estadísticas rápidas
$totalUsers = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
$totalCursos = $pdo->query("SELECT COUNT(*) FROM cursos")->fetchColumn();
$totalNoticias = $pdo->query("SELECT COUNT(*) FROM noticias")->fetchColumn();
?>
<div class="row">
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5 class="card-title">Usuarios</h5>
                <p class="card-text display-4"><?php echo $totalUsers; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title">Cursos</h5>
                <p class="card-text display-4"><?php echo $totalCursos; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h5 class="card-title">Noticias</h5>
                <p class="card-text display-4"><?php echo $totalNoticias; ?></p>
            </div>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-header">Últimos usuarios registrados</div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr><th>Nombre</th><th>Email</th><th>Rol</th><th>Fecha</th></tr>
            </thead>
            <tbody>
                <?php
                $stmt = $pdo->query("SELECT * FROM usuarios ORDER BY created_at DESC LIMIT 5");
                while($row = $stmt->fetch()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo $row['rol']; ?></td>
                    <td><?php echo $row['created_at']; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include '../includes/footer.php'; ?>