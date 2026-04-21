<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/header.php';
auth_required('director');
$u = current_user();
$conn = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $codigo = trim($_POST['codigo']);
    $eid    = (int)($_POST['edit_id'] ?? 0);
    if ($eid) {
        $s = $conn->prepare("UPDATE asignaturas SET nombre=?,codigo=? WHERE id=?");
        $s->bind_param("ssi",$nombre,$codigo,$eid);
    } else {
        $s = $conn->prepare("INSERT INTO asignaturas (nombre,codigo) VALUES (?,?)");
        $s->bind_param("ss",$nombre,$codigo);
    }
    $s->execute(); $s->close();
    header("Location: asignaturas.php?ok=1"); exit();
}
if (isset($_GET['del'])) {
    $conn->query("DELETE FROM asignaturas WHERE id=".(int)$_GET['del']);
    header("Location: asignaturas.php?ok=1"); exit();
}

$asigs  = $conn->query("SELECT * FROM asignaturas ORDER BY nombre");
$editar = isset($_GET['edit']) ? $conn->query("SELECT * FROM asignaturas WHERE id=".(int)$_GET['edit'])->fetch_assoc() : null;
$conn->close();
?>
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Asignaturas</title></head>
<body>
<?php include_sidebar('director','asignaturas');
global_css(".form-modal{position:fixed;inset:0;background:rgba(0,0,0,.6);display:flex;align-items:center;justify-content:center;z-index:999}.modal-box{background:#1a1f2e;border:1px solid rgba(255,255,255,.12);border-radius:16px;padding:28px;width:420px}");
?>
<div class="main">
    <div class="topbar">
        <div><div class="page-title">📖 Asignaturas</div><div class="page-sub">Gestiona las materias del establecimiento</div></div>
        <button class="btn btn-primary" onclick="document.getElementById('m-crear').style.display='flex'">+ Nueva Asignatura</button>
    </div>
    <?php if(isset($_GET['ok'])): ?><div class="alert alert-success">Operación realizada.</div><?php endif; ?>
    <div class="card">
        <table>
            <tr><th>#</th><th>Nombre</th><th>Código</th><th>Acciones</th></tr>
            <?php while ($a = $asigs->fetch_assoc()): ?>
            <tr>
                <td style="color:rgba(255,255,255,.3)"><?=$a['id']?></td>
                <td style="font-weight:600">📖 <?=htmlspecialchars($a['nombre'])?></td>
                <td><code style="background:rgba(255,255,255,.07);padding:2px 8px;border-radius:6px;font-size:12px"><?=$a['codigo']?></code></td>
                <td>
                    <a href="?edit=<?=$a['id']?>" class="btn btn-sm btn-gray">✏️</a>
                    <a href="?del=<?=$a['id']?>" class="btn btn-sm btn-red" onclick="return confirm('¿Eliminar esta asignatura?')">🗑️</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<div id="m-crear" class="form-modal" style="display:none" onclick="if(event.target===this)this.style.display='none'">
<div class="modal-box">
    <h3 style="color:#fff;margin-bottom:18px">➕ Nueva Asignatura</h3>
    <form method="POST">
        <input type="hidden" name="edit_id" value="0">
        <div class="form-group"><label>Nombre *</label><input name="nombre" required placeholder="Ej: Matemáticas"></div>
        <div class="form-group"><label>Código</label><input name="codigo" placeholder="Ej: MAT"></div>
        <div style="display:flex;gap:10px">
            <button class="btn btn-primary">✅ Crear</button>
            <button type="button" class="btn btn-gray" onclick="document.getElementById('m-crear').style.display='none'">Cancelar</button>
        </div>
    </form>
</div>
</div>

<?php if ($editar): ?>
<div class="form-modal" style="display:flex" onclick="if(event.target===this)location.href='asignaturas.php'">
<div class="modal-box">
    <h3 style="color:#fff;margin-bottom:18px">✏️ Editar Asignatura</h3>
    <form method="POST">
        <input type="hidden" name="edit_id" value="<?=$editar['id']?>">
        <div class="form-group"><label>Nombre</label><input name="nombre" value="<?=htmlspecialchars($editar['nombre'])?>" required></div>
        <div class="form-group"><label>Código</label><input name="codigo" value="<?=htmlspecialchars($editar['codigo'])?>"></div>
        <div style="display:flex;gap:10px">
            <button class="btn btn-primary">💾 Guardar</button>
            <a href="asignaturas.php" class="btn btn-gray">Cancelar</a>
        </div>
    </form>
</div>
</div>
<?php endif; ?>
</body></html>
