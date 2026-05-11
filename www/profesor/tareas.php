<?php
require_once '../includes/auth.php';
verificarSesion('profesor');
require_once '../includes/db.php';
$titulo_pagina = 'Gestión de Tareas';
include '../includes/header.php';

$profesor_id = $_SESSION['user_id'];
$cursos = obtenerCursosPorProfesor($profesor_id);
$mensaje = '';

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'crear') {
        $titulo = $_POST['titulo'];
        $descripcion = $_POST['descripcion'];
        $curso_id = $_POST['curso_id'];
        $asignatura_id = $_POST['asignatura_id'];
        $fecha_entrega = $_POST['fecha_entrega'];
        if (crearTarea($titulo, $descripcion, $curso_id, $asignatura_id, $fecha_entrega, $profesor_id)) {
            $mensaje = '<div class="alert alert-success">Tarea creada correctamente.</div>';
        } else {
            $mensaje = '<div class="alert alert-danger">Error al crear tarea.</div>';
        }
    } elseif ($action === 'editar') {
        $id = $_POST['id'];
        $titulo = $_POST['titulo'];
        $descripcion = $_POST['descripcion'];
        $fecha_entrega = $_POST['fecha_entrega'];
        if (actualizarTarea($id, $titulo, $descripcion, $fecha_entrega)) {
            $mensaje = '<div class="alert alert-success">Tarea actualizada.</div>';
        } else {
            $mensaje = '<div class="alert alert-danger">Error al actualizar.</div>';
        }
    } elseif ($action === 'eliminar') {
        $id = $_POST['id'];
        if (eliminarTarea($id)) {
            $mensaje = '<div class="alert alert-success">Tarea eliminada.</div>';
        }
    }
}

$tareas = obtenerTareasPorProfesor($profesor_id);
?>
<div class="row">
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header">Crear nueva tarea</div>
            <div class="card-body">
                <?= $mensaje ?>
                <form method="POST">
                    <input type="hidden" name="action" value="crear">
                    <div class="mb-2"><label>Título</label><input type="text" name="titulo" class="form-control" required></div>
                    <div class="mb-2"><label>Descripción</label><textarea name="descripcion" class="form-control" rows="3"></textarea></div>
                    <div class="mb-2"><label>Curso</label>
                        <select name="curso_id" id="curso_id" class="form-select" required>
                            <option value="">Seleccione</option>
                            <?php foreach($cursos as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2"><label>Asignatura</label>
                        <select name="asignatura_id" id="asignatura_id" class="form-select" required>
                            <option value="">Primero seleccione un curso</option>
                        </select>
                    </div>
                    <div class="mb-2"><label>Fecha de entrega</label><input type="date" name="fecha_entrega" class="form-control" required></div>
                    <button type="submit" class="btn btn-primary">Crear tarea</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Mis tareas creadas</div>
            <div class="card-body">
                <?php if (empty($tareas)): ?>
                    <p>No has creado tareas aún.</p>
                <?php else: ?>
                    <table class="table table-bordered">
                        <thead>
                            <tr><th>Título</th><th>Curso</th><th>Asignatura</th><th>Fecha entrega</th><th>Acciones</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($tareas as $t): ?>
                            <tr>
                                <td><?= htmlspecialchars($t['titulo']) ?></td>
                                <td><?= htmlspecialchars($t['curso_nombre']) ?></td>
                                <td><?= htmlspecialchars($t['asignatura_nombre']) ?></td>
                                <th>Acción</th>
                                ...
                                <td>
                                    <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#modalEntregas<?= $t['id'] ?>">Ver entregas</button>
                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditar" onclick="editarTarea(<?= htmlspecialchars(json_encode($t)) ?>)">Editar</button>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar?')">
                                        <input type="hidden" name="action" value="eliminar">
                                        <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                        <button class="btn btn-danger btn-sm">Eliminar</button>
                                    </form>
                                </td>
                                <td><a href="ver_entregas.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-info">Ver entregas</a></td>
                                <td><?= $t['fecha_entrega'] ?></td>
                                <td>
                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditar" onclick="editarTarea(<?= htmlspecialchars(json_encode($t)) ?>)">Editar</button>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar?')">
                                        <input type="hidden" name="action" value="eliminar">
                                        <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                        <button class="btn btn-danger btn-sm">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal entregas -->
<div class="modal fade" id="modalEntregas<?= $t['id'] ?>" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Entregas - <?= htmlspecialchars($t['titulo']) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php
                $entregas = obtenerEntregasPorTarea($t['id']);
                if (empty($entregas)): ?>
                    <p>No hay entregas aún.</p>
                <?php else: ?>
                    <table class="table table-sm">
                        <thead><tr><th>Estudiante</th><th>Archivo</th><th>Fecha</th><th>Calificación</th><th>Acción</th></tr></thead>
                        <tbody>
                        <?php foreach($entregas as $e): ?>
                            <tr>
                                <td><?= htmlspecialchars($e['estudiante_nombre']) ?></td>
                                <td><a href="../uploads/entregas/<?= $e['archivo_ruta'] ?>" target="_blank"><?= $e['archivo_nombre'] ?></a></td>
                                <td><?= $e['fecha_entrega'] ?></td>
                                <td>
                                    <?php if ($e['calificacion'] !== null): ?>
                                        <?= $e['calificacion'] ?>
                                    <?php else: ?>
                                        <form method="POST" action="calificar_entrega.php" class="d-inline">
                                            <input type="hidden" name="entrega_id" value="<?= $e['id'] ?>">
                                            <input type="number" step="0.1" name="calificacion" placeholder="Nota" style="width:70px" required>
                                            <input type="text" name="comentario" placeholder="Comentario" style="width:150px">
                                            <button type="submit" class="btn btn-sm btn-primary">Calificar</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal editar tarea -->
<div class="modal fade" id="modalEditar" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5>Editar tarea</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" value="editar">
                <input type="hidden" name="id" id="edit_id">
                <div class="mb-2"><label>Título</label><input type="text" name="titulo" id="edit_titulo" class="form-control" required></div>
                <div class="mb-2"><label>Descripción</label><textarea name="descripcion" id="edit_descripcion" class="form-control" rows="3"></textarea></div>
                <div class="mb-2"><label>Fecha de entrega</label><input type="date" name="fecha_entrega" id="edit_fecha" class="form-control" required></div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-primary">Guardar cambios</button></div>
        </form>
    </div>
</div>

<script>
    // Cargar asignaturas según curso seleccionado
    document.getElementById('curso_id').addEventListener('change', function() {
        let curso_id = this.value;
        if (curso_id) {
            fetch('../ajax/asignaturas_por_curso.php?curso_id=' + curso_id)
                .then(res => res.json())
                .then(data => {
                    let select = document.getElementById('asignatura_id');
                    select.innerHTML = '<option value="">Seleccione asignatura</option>';
                    data.forEach(a => {
                        select.innerHTML += `<option value="${a.id}">${a.nombre}</option>`;
                    });
                });
        } else {
            document.getElementById('asignatura_id').innerHTML = '<option value="">Primero seleccione un curso</option>';
        }
    });

    function editarTarea(tarea) {
        document.getElementById('edit_id').value = tarea.id;
        document.getElementById('edit_titulo').value = tarea.titulo;
        document.getElementById('edit_descripcion').value = tarea.descripcion || '';
        document.getElementById('edit_fecha').value = tarea.fecha_entrega;
    }
</script>
<?php include '../includes/footer.php'; ?>