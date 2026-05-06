<?php
require_once '../includes/auth.php';
verificarSesion('director');
require_once '../includes/db.php';
$titulo_pagina = 'Dashboard Director';
include '../includes/header.php';

$totalUsers = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
$totalCursos = $pdo->query("SELECT COUNT(*) FROM cursos")->fetchColumn();
$totalNoticias = $pdo->query("SELECT COUNT(*) FROM noticias")->fetchColumn();

// Datos para gráfico de usuarios por rol
$rolesData = $pdo->query("SELECT rol, COUNT(*) as total FROM usuarios GROUP BY rol")->fetchAll();
$labels = [];
$data = [];
foreach($rolesData as $r) {
    $labels[] = $r['rol'];
    $data[] = $r['total'];
}
?>
<div class="row">
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-primary"><div class="card-body"><h5>Usuarios</h5><p class="display-4"><?= $totalUsers ?></p></div></div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-success"><div class="card-body"><h5>Cursos</h5><p class="display-4"><?= $totalCursos ?></p></div></div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-warning"><div class="card-body"><h5>Noticias</h5><p class="display-4"><?= $totalNoticias ?></p></div></div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="card"><div class="card-header">Usuarios por rol</div><div class="card-body"><canvas id="graficoRoles" width="400" height="200"></canvas></div></div>
    </div>
    <div class="col-md-6">
        <div class="card"><div class="card-header">Últimos usuarios</div><div class="card-body"><table class="table table-sm">...</table></div></div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('graficoRoles').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: { labels: <?= json_encode($labels) ?>, datasets: [{ label: 'Cantidad', data: <?= json_encode($data) ?>, backgroundColor: 'rgba(54,162,235,0.5)' }] }
});
</script>
<?php include '../includes/footer.php'; ?>