<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
$titulo_pagina = 'Calendario escolar';
include '../includes/header.php';

$user_id = $_SESSION['user_id'];
$rol = $_SESSION['rol'];
$eventos_json = [];

// Obtener eventos según el rol
if ($rol == 'director') {
    $eventos = obtenerEventos();
} elseif ($rol == 'profesor') {
    $cursos = obtenerCursosPorProfesor($user_id);
    $curso_ids = array_column($cursos, 'id');
    if (!empty($curso_ids)) {
        $placeholders = implode(',', array_fill(0, count($curso_ids), '?'));
        $stmt = $pdo->prepare("SELECT e.* FROM eventos e WHERE e.curso_id IN ($placeholders) OR e.curso_id IS NULL");
        $stmt->execute($curso_ids);
        $eventos = $stmt->fetchAll();
    } else {
        $eventos = [];
    }
} elseif ($rol == 'estudiante') {
    $stmt = $pdo->prepare("SELECT e.curso_id FROM estudiantes e WHERE e.user_id = ?");
    $stmt->execute([$user_id]);
    $curso_id = $stmt->fetchColumn();
    $eventos = $curso_id ? obtenerEventos(null, null, $curso_id) : [];
} elseif ($rol == 'apoderado') {
    $estudiante_id = $_SESSION['hijo_actual'] ?? 0;
    if ($estudiante_id) {
        $stmt = $pdo->prepare("SELECT e.curso_id FROM estudiantes e WHERE e.user_id = ?");
        $stmt->execute([$estudiante_id]);
        $curso_id = $stmt->fetchColumn();
        $eventos = $curso_id ? obtenerEventos(null, null, $curso_id) : [];
    } else {
        $eventos = [];
    }
} else {
    $eventos = [];
}

foreach ($eventos as $e) {
    $color = '#3788d8';
    switch ($e['tipo']) {
        case 'feriado': $color = '#dc3545'; break;
        case 'reunion': $color = '#28a745'; break;
        case 'evaluacion': $color = '#ffc107'; break;
        case 'actividad': $color = '#17a2b8'; break;
        default: $color = '#6c757d';
    }
    $eventos_json[] = [
        'title' => htmlspecialchars($e['titulo']),
        'start' => $e['fecha_inicio'],
        'end' => date('Y-m-d', strtotime($e['fecha_fin'] . ' +1 day')),
        'color' => $color,
        'description' => htmlspecialchars($e['descripcion'])
    ];
}
?>
<div class="card">
    <div class="card-header">
        Calendario escolar
        <?php if ($rol == 'director'): ?>
            <button class="btn btn-primary btn-sm float-end" data-bs-toggle="modal" data-bs-target="#modalEvento">+ Nuevo evento</button>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div id="calendar" style="height: 600px;"></div>
    </div>
</div>

<?php if ($rol == 'director'): ?>
<!-- Modal para crear evento (igual que antes) -->
<div class="modal fade" id="modalEvento" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="guardar_evento.php">
            <div class="modal-content">
                <div class="modal-header"><h5>Nuevo evento</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="crear">
                    <div class="mb-2"><label>Título</label><input type="text" name="titulo" class="form-control" required></div>
                    <div class="mb-2"><label>Descripción</label><textarea name="descripcion" class="form-control"></textarea></div>
                    <div class="mb-2"><label>Fecha inicio</label><input type="date" name="fecha_inicio" class="form-control" required></div>
                    <div class="mb-2"><label>Fecha fin</label><input type="date" name="fecha_fin" class="form-control" required></div>
                    <div class="row">
                        <div class="col-md-6"><label>Hora inicio</label><input type="time" name="hora_inicio" class="form-control"></div>
                        <div class="col-md-6"><label>Hora fin</label><input type="time" name="hora_fin" class="form-control"></div>
                    </div>
                    <div class="mb-2"><label>Tipo</label><select name="tipo" class="form-select"><option value="actividad">Actividad</option><option value="reunion">Reunión</option><option value="evaluacion">Evaluación</option><option value="feriado">Feriado</option></select></div>
                    <div class="mb-2"><label>Curso (opcional)</label><select name="curso_id" class="form-select"><option value="">Todos los cursos</option><?php $cursos = obtenerCursos(); foreach($cursos as $c): ?><option value="<?= $c['id'] ?>"><?= $c['nombre'] ?></option><?php endforeach; ?></select></div>
                    <div class="mb-2"><label>Asignatura (opcional)</label><select name="asignatura_id" class="form-select"><option value="">Ninguna</option></select></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Guardar</button></div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/es.js'></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'es',
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listMonth'
        },
        events: <?php echo json_encode($eventos_json); ?>,
        eventClick: function(info) {
            alert(info.event.title + '\n' + (info.event.extendedProps.description || 'Sin descripción'));
        }
    });
    calendar.render();
});
</script>
<?php include '../includes/footer.php'; ?>