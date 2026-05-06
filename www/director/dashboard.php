<?php
require_once '../includes/auth.php';
verificarSesion('director');
require_once '../includes/db.php';
$titulo_pagina = 'Dashboard Director';
include '../includes/header.php';

// Estadísticas principales
$totalUsers = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
$totalCursos = $pdo->query("SELECT COUNT(*) FROM cursos")->fetchColumn();
$totalNoticias = $pdo->query("SELECT COUNT(*) FROM noticias")->fetchColumn();

// Datos para gráfico de usuarios por rol
$rolesData = $pdo->query("SELECT rol, COUNT(*) as total FROM usuarios GROUP BY rol")->fetchAll();
$labelsRoles = [];
$dataRoles = [];
foreach($rolesData as $r) {
    $labelsRoles[] = $r['rol'];
    $dataRoles[] = $r['total'];
}

// Gráfico de asistencia mensual (últimos 6 meses)
$asistenciaMensual = $pdo->query("
    SELECT DATE_FORMAT(fecha, '%Y-%m') as mes,
           SUM(estado = 'presente') as presentes,
           SUM(estado = 'ausente') as ausentes,
           SUM(estado = 'tarde') as tardes
    FROM asistencia
    GROUP BY mes
    ORDER BY mes DESC
    LIMIT 6
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
?>

<!-- Botones de exportación a Excel (avance anterior) -->
<div class="row mb-3">
    <div class="col-md-12">
        <div class="btn-group" role="group">
            <a href="exportar_excel.php?tipo=usuarios" class="btn btn-success"><i class="bi bi-file-excel"></i> Exportar usuarios a Excel</a>
            <a href="exportar_excel.php?tipo=cursos" class="btn btn-success"><i class="bi bi-file-excel"></i> Exportar cursos a Excel</a>
        </div>
    </div>
</div>

<!-- Tarjetas de resumen -->
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

<!-- Gráfico de usuarios por rol y últimos usuarios -->
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">Usuarios por rol</div>
            <div class="card-body">
                <canvas id="graficoRoles" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">Últimos usuarios registrados</div>
            <div class="card-body">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr><th>Nombre</th><th>Email</th><th>Rol</th><th>Fecha</th></tr>
                    </thead>
                    <tbody>
                        <?php
                        $ultimos = $pdo->query("SELECT nombre, email, rol, created_at FROM usuarios ORDER BY created_at DESC LIMIT 5")->fetchAll();
                        foreach($ultimos as $u): ?>
                        <tr>
                            <td><?= htmlspecialchars($u['nombre']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= $u['rol'] ?></td>
                            <td><?= $u['created_at'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Gráficos de asistencia y promedios -->
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">Evolución de la asistencia (últimos 6 meses)</div>
            <div class="card-body">
                <canvas id="asistenciaChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">Promedio de notas por curso</div>
            <div class="card-body">
                <canvas id="notasChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Gráfico de usuarios por rol
const ctxRoles = document.getElementById('graficoRoles').getContext('2d');
new Chart(ctxRoles, {
    type: 'bar',
    data: {
        labels: <?= json_encode($labelsRoles) ?>,
        datasets: [{
            label: 'Cantidad',
            data: <?= json_encode($dataRoles) ?>,
            backgroundColor: 'rgba(54,162,235,0.5)'
        }]
    }
});

// Gráfico de asistencia mensual
<?php if(!empty($meses)): ?>
const ctxAsistencia = document.getElementById('asistenciaChart').getContext('2d');
new Chart(ctxAsistencia, {
    type: 'line',
    data: {
        labels: <?= json_encode($meses) ?>,
        datasets: [
            { label: 'Presentes', data: <?= json_encode($presentes) ?>, borderColor: 'green', backgroundColor: 'rgba(0,255,0,0.1)', fill: true },
            { label: 'Ausentes', data: <?= json_encode($ausentes) ?>, borderColor: 'red', backgroundColor: 'rgba(255,0,0,0.1)', fill: true },
            { label: 'Tardes', data: <?= json_encode($tardes) ?>, borderColor: 'orange', backgroundColor: 'rgba(255,165,0,0.1)', fill: true }
        ]
    },
    options: { responsive: true }
});
<?php else: ?>
document.getElementById('asistenciaChart').parentNode.innerHTML = '<div class="alert alert-info">No hay datos de asistencia aún.</div>';
<?php endif; ?>

// Gráfico de promedios por curso
<?php if(!empty($cursosPromedio)): ?>
const ctxNotas = document.getElementById('notasChart').getContext('2d');
new Chart(ctxNotas, {
    type: 'bar',
    data: {
        labels: <?= json_encode($cursosPromedio) ?>,
        datasets: [{
            label: 'Promedio general',
            data: <?= json_encode($promedios) ?>,
            backgroundColor: 'rgba(153,102,255,0.5)'
        }]
    },
    options: {
        scales: { y: { beginAtZero: true, max: 7 } }
    }
});
<?php else: ?>
document.getElementById('notasChart').parentNode.innerHTML = '<div class="alert alert-info">No hay notas registradas aún.</div>';
<?php endif; ?>

// Mostrar mensaje de bienvenida con toast (si existe sesión de mensaje)
<?php if(isset($_SESSION['mensaje'])): ?>
    window.addEventListener('DOMContentLoaded', function() {
        mostrarMensaje('<?= addslashes($_SESSION['mensaje']); ?>');
    });
    <?php unset($_SESSION['mensaje']); ?>
<?php endif; ?>
</script>

<?php include '../includes/footer.php'; ?>