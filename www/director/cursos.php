<?php
require_once '../includes/auth.php';
verificarSesion('director');
require_once '../includes/db.php';
$titulo_pagina = 'Gestión de Cursos';
include '../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'crear') {
        $nombre = $_POST['nombre'];
        $descripcion = $_POST['descripcion'];
        $anio = $_POST['anio'];
        $stmt = $pdo->prepare("INSERT INTO cursos (nombre, descripcion, anio) VALUES (?,?,?)");
        $stmt->execute([$nombre, $descripcion, $anio]);
        $_SESSION['mensaje'] = "Curso creado";
    } elseif ($action === 'editar') {
        $id = $_POST['id'];
        $nombre = $_POST['nombre'];
        $descripcion = $_POST['descripcion'];
        $anio = $_POST['anio'];
        $stmt = $pdo->prepare("UPDATE cursos SET nombre=?, descripcion=?, anio=? WHERE id=?");
        $stmt->execute([$nombre, $descripcion, $anio, $id]);
        $_SESSION['mensaje'] = "Curso actualizado";
    } elseif ($action === 'eliminar') {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM cursos WHERE id=?");
        $stmt->execute([$id]);
        $_SESSION['mensaje'] = "Curso eliminado";
    }
    header("Location: cursos.php");
    exit;
}

$cursos = $pdo->query("SELECT * FROM cursos ORDER BY anio, nombre")->fetchAll();
?>
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <span>Lista de Cursos</span>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalCurso" onclick="resetForm()">+ Nuevo Curso</button>
    </div>
    <div class="card-body">
        <?php if(isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-success"><?= $_SESSION['mensaje']; unset($_SESSION['mensaje']); ?></div>
        <?php endif; ?>
        <table class="table table-bordered">
            <thead>
                <tr><th>ID</th><th>Nombre</th><th>Descripción</th><th>Año</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                <?php foreach($cursos as $c): ?>
                <tr>
                    <td><?= $c['id'] ?></td>
                    <td><?= htmlspecialchars($c['nombre']) ?></td>
                    <td><?= htmlspecialchars($c['descripcion']) ?></td>
                    <td><?= $c['anio'] ?></td>
                    <td>
                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalCurso" 
                                onclick="editarCurso(<?= htmlspecialchars(json_encode($c)) ?>)">Editar</button>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar?')">
                            <input type="hidden" name="action" value="eliminar">
                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
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
<div class="modal fade" id="modalCurso" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Curso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" id="action" value="crear">
                <input type="hidden" name="id" id="cursoId">
                <div class="mb-2">
                    <label>Nombre</label>
                    <input type="text" name="nombre" id="nombre" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label>Descripción</label>
                    <textarea name="descripcion" id="descripcion" class="form-control"></textarea>
                </div>
                <div class="mb-2">
                    <label>Año</label>
                    <input type="number" name="anio" id="anio" class="form-control" value="<?= date('Y') ?>" required>
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
    document.getElementById('cursoId').value = '';
    document.getElementById('nombre').value = '';
    document.getElementById('descripcion').value = '';
    document.getElementById('anio').value = new Date().getFullYear();
}
function editarCurso(curso) {
    document.getElementById('action').value = 'editar';
    document.getElementById('cursoId').value = curso.id;
    document.getElementById('nombre').value = curso.nombre;
    document.getElementById('descripcion').value = curso.descripcion;
    document.getElementById('anio').value = curso.anio;
}
</script>
<?php include '../includes/footer.php'; ?>