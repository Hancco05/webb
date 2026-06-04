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

// Datos para gráfico de usuarios por rol
$rolesData = $pdo->query("SELECT rol, COUNT(*) as total FROM usuarios GROUP BY rol")->fetchAll();
$labelsRoles = [];
$dataRoles = [];
foreach ($rolesData as $r) {
    $labelsRoles[] = $r['rol'];
    $dataRoles[] = $r['total'];
}

// Gráfico de asistencia mensual
$asistenciaMensual = $pdo->query("
    SELECT DATE_FORMAT(fecha, '%Y-%m') as mes,
           SUM(estado='presente') as presentes,
           SUM(estado='ausente') as ausentes,
           SUM(estado='tarde') as tardes
    FROM asistencia
    GROUP BY mes
    ORDER BY mes DESC LIMIT 6
")->fetchAll();
$meses = array_reverse(array_column($asistenciaMensual, 'mes'));
$presentes = array_reverse(array_column($asistenciaMensual, 'presentes'));
$ausentes = array_reverse(array_column($asistenciaMensual, 'ausentes'));
$tardes = array_reverse(array_column($asistenciaMensual, 'tardes'));

// Promedio de notas por curso
$promediosPorCurso = $pdo->query("
    SELECT c.nombre as curso, AVG(n.nota) as promedio
    FROM notas n
    JOIN asignaturas a ON n.asignatura_id = a.id
    JOIN cursos c ON a.curso_id = c.id
    GROUP BY c.id
    ORDER BY promedio DESC
")->fetchAll();
$cursosPromedio = array_column($promediosPorCurso, 'curso');
$promedios = array_column($promediosPorCurso, 'promedio');

// Tareas próximas y últimas entregas
$proximas = $pdo->query("
    SELECT t.titulo, c.nombre as curso, t.fecha_entrega 
    FROM tareas t
    JOIN cursos c ON t.curso_id = c.id
    WHERE t.fecha_entrega BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    ORDER BY t.fecha_entrega LIMIT 5
")->fetchAll();

$ultimasEntregas = $pdo->query("
    SELECT e.fecha_entrega, u.nombre as estudiante, t.titulo as tarea
    FROM entregas e
    JOIN usuarios u ON e.estudiante_id = u.id
    JOIN tareas t ON e.tarea_id = t.id
    ORDER BY e.fecha_entrega DESC LIMIT 5
")->fetchAll();
?>
<div class="row">
    <div class="col-md-4 mb-3"><div class="card text-white bg-primary"><div class="card-body"><h5>Usuarios</h5><p class="display-4"><?= $totalUsers ?></p></div></div></div>
    <div class="col-md-4 mb-3"><div class="card text-white bg-success"><div class="card-body"><h5>Cursos</h5><p class="display-4"><?= $totalCursos ?></p></div></div></div>
    <div class="col-md-4 mb-3"><div class="card text-white bg-warning"><div class="card-body"><h5>Noticias</h5><p class="display-4"><?= $totalNoticias ?></p></div></div></div>
</div>
<div class="row">
    <div class="col-md-6 mb-4"><div class="card"><div class="card-header">Usuarios por rol</div><div class="card-body"><canvas id="graficoRoles" width="400" height="200"></canvas></div></div></div>
    <div class="col-md-6 mb-4"><div class="card"><div class="card-header">Últimos usuarios</div><div class="card-body">
        <table class="table table-sm"><?php $ultimos = $pdo->query("SELECT nombre, email, rol, created_at FROM usuarios ORDER BY created_at DESC LIMIT 5")->fetchAll(); ?>
            <thead><tr><th>Nombre</th><th>Email</th><th>Rol</th><th>Fecha</th></tr></thead>
            <tbody><?php foreach($ultimos as $u): ?><tr><td><?= htmlspecialchars($u['nombre']) ?></td><td><?= htmlspecialchars($u['email']) ?></td><td><?= $u['rol'] ?></td><td><?= $u['created_at'] ?></td></tr><?php endforeach; ?></tbody>
        </table>
    </div></div></div>
</div>
<div class="row">
    <div class="col-md-6 mb-4"><div class="card"><div class="card-header">Evolución asistencia (6 meses)</div><div class="card-body"><canvas id="asistenciaChart" width="400" height="200"></canvas></div></div></div>
    <div class="col-md-6 mb-4"><div class="card"><div class="card-header">Promedio notas por curso</div><div class="card-body"><canvas id="notasChart" width="400" height="200"></canvas></div></div></div>
</div>
<div class="row">
    <div class="col-md-6 mb-4"><div class="card"><div class="card-header">Próximas tareas (7 días)</div><div class="card-body">
        <?php if(empty($proximas)) echo "Sin tareas próximas."; else echo "<ul class='list-group'>"; foreach($proximas as $t): echo "<li class='list-group-item'><strong>{$t['titulo']}</strong> - {$t['curso']} - Entrega: {$t['fecha_entrega']}</li>"; endforeach; echo "</ul>"; ?>
    </div></div></div>
    <div class="col-md-6 mb-4"><div class="card"><div class="card-header">Últimas entregas</div><div class="card-body">
        <?php if(empty($ultimasEntregas)) echo "Sin entregas."; else echo "<ul class='list-group'>"; foreach($ultimasEntregas as $e): echo "<li class='list-group-item'>{$e['estudiante']} entregó '{$e['tarea']}' el {$e['fecha_entrega']}</li>"; endforeach; echo "</ul>"; ?>
    </div></div></div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('graficoRoles'), { type: 'bar', data: { labels: <?= json_encode($labelsRoles) ?>, datasets: [{ label: 'Cantidad', data: <?= json_encode($dataRoles) ?>, backgroundColor: 'rgba(54,162,235,0.5)' }] } });
<?php if(!empty($meses)): ?>
new Chart(document.getElementById('asistenciaChart'), { type: 'line', data: { labels: <?= json_encode($meses) ?>, datasets: [{ label: 'Presentes', data: <?= json_encode($presentes) ?>, borderColor: 'green', fill: false },{ label: 'Ausentes', data: <?= json_encode($ausentes) ?>, borderColor: 'red', fill: false },{ label: 'Tardes', data: <?= json_encode($tardes) ?>, borderColor: 'orange', fill: false }] } });
<?php endif; ?>
<?php if(!empty($cursosPromedio)): ?>
new Chart(document.getElementById('notasChart'), { type: 'bar', data: { labels: <?= json_encode($cursosPromedio) ?>, datasets: [{ label: 'Promedio', data: <?= json_encode($promedios) ?>, backgroundColor: 'rgba(153,102,255,0.5)' }] }, options: { scales: { y: { beginAtZero: true, max: 7 } } } });
<?php endif; ?>
</script>
<?php include '../includes/footer.php'; ?>