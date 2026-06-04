<?php
require_once '../includes/auth.php';
verificarSesion('director');
require_once '../includes/db.php';
$titulo_pagina = 'Asignaturas';
include '../includes/header.php';

$error = '';
$cursos = obtenerCursos();

$limite = 10;
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina - 1) * $limite;
$total = $pdo->query("SELECT COUNT(*) FROM asignaturas")->fetchColumn();
$totalPaginas = ceil($total / $limite);
$stmt = $pdo->prepare("SELECT a.*, c.nombre as curso_nombre FROM asignaturas a JOIN cursos c ON a.curso_id = c.id ORDER BY c.nombre, a.nombre LIMIT :offset, :limite");
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
$stmt->execute();
$asignaturas = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verificarTokenCSRF($_POST['csrf_token'] ?? '')) { $error = "Token inválido"; }
    else {
        $action = $_POST['action'];
        $id = $_POST['id'] ?? 0;
        $nombre = trim($_POST['nombre']);
        $codigo = trim($_POST['codigo']);
        $curso_id = (int)$_POST['curso_id'];
        if ($action === 'crear') {
            $stmt = $pdo->prepare("INSERT INTO asignaturas (nombre, codigo, curso_id) VALUES (?,?,?)");
            if ($stmt->execute([$nombre, $codigo, $curso_id])) { $_SESSION['mensaje']="Asignatura creada"; header("Location: /director/asignaturas.php"); exit; }
            else $error = "Error";
        } elseif ($action === 'editar') {
            $stmt = $pdo->prepare("UPDATE asignaturas SET nombre=?, codigo=?, curso_id=? WHERE id=?");
            if ($stmt->execute([$nombre, $codigo, $curso_id, $id])) { $_SESSION['mensaje']="Asignatura actualizada"; header("Location: /director/asignaturas.php"); exit; }
            else $error = "Error";
        } elseif ($action === 'eliminar') {
            $stmt = $pdo->prepare("DELETE FROM asignaturas WHERE id=?");
            if ($stmt->execute([$id])) { $_SESSION['mensaje']="Asignatura eliminada"; header("Location: /director/asignaturas.php"); exit; }
            else $error = "Error";
        }
    }
}
?>
<div class="card">
    <div class="card-header d-flex justify-content-between"><span>Asignaturas</span><button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAsignatura" onclick="resetForm()">+ Nueva</button></div>
    <div class="card-body">
        <?php if(isset($_SESSION['mensaje'])): ?><div class="alert alert-success"><?= $_SESSION['mensaje']; unset($_SESSION['mensaje']); ?></div><?php endif; ?>
        <?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
        <table class="table table-bordered"><thead><tr><th>ID</th><th>Nombre</th><th>Código</th><th>Curso</th><th>Acciones</th></tr></thead>
        <tbody><?php foreach($asignaturas as $a): ?><tr><td><?= $a['id'] ?></td><td><?= htmlspecialchars($a['nombre']) ?></td><td><?= htmlspecialchars($a['codigo']) ?></td><td><?= htmlspecialchars($a['curso_nombre']) ?></td>
        <td><button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalAsignatura" onclick="editarAsignatura(<?= htmlspecialchars(json_encode($a)) ?>)">Editar</button>
            <form method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar?')"><input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>"><input type="hidden" name="action" value="eliminar"><input type="hidden" name="id" value="<?= $a['id'] ?>"><button class="btn btn-danger btn-sm">Eliminar</button></form>
        </td></tr><?php endforeach; ?></tbody>
        </table>
        <nav><ul class="pagination"><?php for($i=1;$i<=$totalPaginas;$i++): ?><li class="page-item <?= ($i==$pagina)?'active':'' ?>"><a class="page-link" href="?pagina=<?= $i ?>"><?= $i ?></a></li><?php endfor; ?></ul></nav>
    </div>
</div>
<div class="modal fade" id="modalAsignatura" tabindex="-1"><div class="modal-dialog"><form method="POST" class="modal-content">
    <div class="modal-header"><h5>Asignatura</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
        <input type="hidden" name="action" id="action" value="crear">
        <input type="hidden" name="id" id="asignaturaId">
        <div class="mb-2"><label>Nombre</label><input type="text" name="nombre" id="nombre" class="form-control" required></div>
        <div class="mb-2"><label>Código</label><input type="text" name="codigo" id="codigo" class="form-control"></div>
        <div class="mb-2"><label>Curso</label><select name="curso_id" id="curso_id" class="form-select" required>
            <option value="">Seleccione</option><?php foreach($cursos as $c): ?><option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option><?php endforeach; ?>
        </select></div>
    </div>
    <div class="modal-footer"><button type="submit" class="btn btn-primary">Guardar</button></div>
</form></div></div>
<script>
function resetForm() { document.getElementById('action').value='crear'; document.getElementById('asignaturaId').value=''; document.getElementById('nombre').value=''; document.getElementById('codigo').value=''; document.getElementById('curso_id').value=''; }
function editarAsignatura(a) { document.getElementById('action').value='editar'; document.getElementById('asignaturaId').value=a.id; document.getElementById('nombre').value=a.nombre; document.getElementById('codigo').value=a.codigo; document.getElementById('curso_id').value=a.curso_id; }
</script>
<?php include '../includes/footer.php'; ?>