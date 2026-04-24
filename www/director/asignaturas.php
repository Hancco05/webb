<?php
require_once '../includes/auth.php';
verificarSesion('director');
require_once '../includes/db.php';
$titulo_pagina = 'Gestión de Asignaturas';
include '../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'crear') {
        $nombre = $_POST['nombre'];
        $codigo = $_POST['codigo'];
        $curso_id = $_POST['curso_id'];
        $stmt = $pdo->prepare("INSERT INTO asignaturas (nombre, codigo, curso_id) VALUES (?,?,?)");
        $stmt->execute([$nombre, $codigo, $curso_id]);
        $_SESSION['mensaje'] = "Asignatura creada";
    } elseif ($action === 'editar') {
        $id = $_POST['id'];
        $nombre = $_POST['nombre'];
        $codigo = $_POST['codigo'];
        $curso_id = $_POST['curso_id'];
        $stmt = $pdo->prepare("UPDATE asignaturas SET nombre=?, codigo=?, curso_id=? WHERE id=?");
        $stmt->execute([$nombre, $codigo, $curso_id, $id]);
        $_SESSION['mensaje'] = "Asignatura actualizada";
    } elseif ($action === 'eliminar') {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM asignaturas WHERE id=?");
        $stmt->execute([$id]);
        $_SESSION['mensaje'] = "Asignatura eliminada";
    }
    header("Location: asignaturas.php");
    exit;
}

$asignaturas = $pdo->query("
    SELECT a.*, c.nombre as curso_nombre 
    FROM asignaturas a 
    JOIN cursos c ON a.curso_id = c.id 
    ORDER BY c.nombre, a.nombre
")->fetchAll();
$cursos = obtenerCursos();
?>
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <span>Lista de Asignaturas</span>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAsignatura" onclick="resetForm()">+ Nueva Asignatura</button>
    </div>
    <div class="card-body">
        <?php if(isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-success"><?= $_SESSION['mensaje']; unset($_SESSION['mensaje']); ?></div>
        <?php endif; ?>
        <table class="table table-bordered">
            <thead>
                <tr><th>ID</th><th>Nombre</th><th>Código</th><th>Curso</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                <?php foreach($asignaturas as $a): ?>
                <tr>
                    <td><?= $a['id'] ?></td>
                    <td><?= htmlspecialchars($a['nombre']) ?></td>
                    <td><?= htmlspecialchars($a['codigo']) ?></td>
                    <td><?= htmlspecialchars($a['curso_nombre']) ?></td>
                    <td>
                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalAsignatura" 
                                onclick="editarAsignatura(<?= htmlspecialchars(json_encode($a)) ?>)">Editar</button>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar?')">
                            <input type="hidden" name="action" value="eliminar">
                            <input type="hidden" name="id" value="<?= $a['id'] ?>">
                            <button class="btn btn-danger btn-sm">Eliminar</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalAsignatura" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Asignatura</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" id="action" value="crear">
                <input type="hidden" name="id" id="asignaturaId">
                <div class="mb-2">
                    <label>Nombre</label>
                    <input type="text" name="nombre" id="nombre" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label>Código</label>
                    <input type="text" name="codigo" id="codigo" class="form-control">
                </div>
                <div class="mb-2">
                    <label>Curso</label>
                    <select name="curso_id" id="curso_id" class="form-select" required>
                        <option value="">Seleccione</option>
                        <?php foreach($cursos as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?> (<?= $c['anio'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
function resetForm() {
    document.getElementById('action').value = 'crear';
    document.getElementById('asignaturaId').value = '';
    document.getElementById('nombre').value = '';
    document.getElementById('codigo').value = '';
    document.getElementById('curso_id').value = '';
}
function editarAsignatura(asig) {
    document.getElementById('action').value = 'editar';
    document.getElementById('asignaturaId').value = asig.id;
    document.getElementById('nombre').value = asig.nombre;
    document.getElementById('codigo').value = asig.codigo;
    document.getElementById('curso_id').value = asig.curso_id;
}
</script>
<?php include '../includes/footer.php'; ?>