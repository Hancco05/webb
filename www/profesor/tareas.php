<?php
require_once '../includes/auth.php';
verificarSesion('profesor');
require_once '../includes/db.php';
$titulo_pagina = 'Tareas';
include '../includes/header.php';

$profesor_id = $_SESSION['user_id'];
$cursos = obtenerCursosPorProfesor($profesor_id);
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verificarTokenCSRF($_POST['csrf_token'] ?? '')) die("CSRF");
    $action = $_POST['action'];
    if ($action === 'crear') {
        crearTarea($_POST['titulo'], $_POST['descripcion'], $_POST['curso_id'], $_POST['asignatura_id'], $_POST['fecha_entrega'], $profesor_id);
        $_SESSION['mensaje'] = "Tarea creada";
        header("Location: tareas.php");
        exit;
    } elseif ($action === 'editar') {
        actualizarTarea($_POST['id'], $_POST['titulo'], $_POST['descripcion'], $_POST['fecha_entrega']);
        $_SESSION['mensaje'] = "Actualizada";
        header("Location: tareas.php");
        exit;
    } elseif ($action === 'eliminar') {
        eliminarTarea($_POST['id']);
        $_SESSION['mensaje'] = "Eliminada";
        header("Location: tareas.php");
        exit;
    }
}
$tareas = obtenerTareasPorProfesor($profesor_id);
?>
<div class="row">
    <div class="col-md-4"><div class="card"><div class="card-header">Crear tarea</div><div class="card-body"><form method="POST"><input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>"><input type="hidden" name="action" value="crear"><div class="mb-2"><label>Título</label><input type="text" name="titulo" class="form-control" required></div><div class="mb-2"><label>Descripción</label><textarea name="descripcion" class="form-control"></textarea></div><div class="mb-2"><label>Curso</label><select name="curso_id" id="curso_id" class="form-select" required><option value="">Seleccione</option><?php foreach($cursos as $c): ?><option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option><?php endforeach; ?></select></div><div class="mb-2"><label>Asignatura</label><select name="asignatura_id" id="asignatura_id" class="form-select" required></select></div><div class="mb-2"><label>Fecha entrega</label><input type="date" name="fecha_entrega" class="form-control" required></div><button type="submit" class="btn btn-primary">Crear</button></form></div></div></div>
    <div class="col-md-8"><div class="card"><div class="card-header">Mis tareas</div><div class="card-body"><table class="table"><?php foreach($tareas as $t): ?><tr><td><strong><?= htmlspecialchars($t['titulo']) ?></strong><br><small><?= htmlspecialchars($t['curso_nombre']) ?> - <?= htmlspecialchars($t['asignatura_nombre']) ?> - Entrega: <?= $t['fecha_entrega'] ?></small></td><td><button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalEditar" onclick="editarTarea(<?= htmlspecialchars(json_encode($t)) ?>)">Editar</button><form method="POST" style="display:inline;"><input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>"><input type="hidden" name="action" value="eliminar"><input type="hidden" name="id" value="<?= $t['id'] ?>"><button class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar?')">Eliminar</button></form></td></tr><?php endforeach; ?></table></div></div></div>
</div>
<div class="modal fade" id="modalEditar" tabindex="-1"><div class="modal-dialog"><form method="POST" class="modal-content"><div class="modal-header"><h5>Editar tarea</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>"><input type="hidden" name="action" value="editar"><input type="hidden" name="id" id="edit_id"><div class="mb-2"><label>Título</label><input type="text" name="titulo" id="edit_titulo" class="form-control" required></div><div class="mb-2"><label>Descripción</label><textarea name="descripcion" id="edit_descripcion" class="form-control"></textarea></div><div class="mb-2"><label>Fecha entrega</label><input type="date" name="fecha_entrega" id="edit_fecha" class="form-control" required></div></div><div class="modal-footer"><button type="submit" class="btn btn-primary">Guardar</button></div></form></div></div>
<script>
document.getElementById('curso_id').addEventListener('change', function() {
    let curso_id = this.value;
    fetch('/ajax/asignaturas_por_curso.php?curso_id=' + curso_id)
        .then(res => res.json())
        .then(data => { let select = document.getElementById('asignatura_id'); select.innerHTML = '<option value="">Seleccione</option>'; data.forEach(a => select.innerHTML += `<option value="${a.id}">${a.nombre}</option>`); });
});
function editarTarea(t) { document.getElementById('edit_id').value = t.id; document.getElementById('edit_titulo').value = t.titulo; document.getElementById('edit_descripcion').value = t.descripcion || ''; document.getElementById('edit_fecha').value = t.fecha_entrega; }
</script>
<?php include '../includes/footer.php'; ?>