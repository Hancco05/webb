<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/header.php';
auth_required('director');
$u = current_user();
$conn = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre  = trim($_POST['nombre']);
    $nivel   = trim($_POST['nivel']);
    $letra   = trim($_POST['letra']);
    $año     = (int)$_POST['año'];
    $pjefe   = (int)$_POST['profesor_jefe'] ?: null;
    $eid     = (int)($_POST['edit_id'] ?? 0);

    if ($eid) {
        $s = $conn->prepare("UPDATE cursos SET nombre=?,nivel=?,letra=?,año=?,profesor_jefe_id=? WHERE id=?");
        $s->bind_param("sssiii",$nombre,$nivel,$letra,$año,$pjefe,$eid);
        $s->execute(); $s->close();
        log_actividad($conn,$u['id'],'editar_curso',"Editó curso: $nombre");
    } else {
        $s = $conn->prepare("INSERT INTO cursos (nombre,nivel,letra,año,profesor_jefe_id) VALUES (?,?,?,?,?)");
        $s->bind_param("sssii",$nombre,$nivel,$letra,$año,$pjefe);
        $s->execute(); $s->close();
        log_actividad($conn,$u['id'],'crear_curso',"Creó curso: $nombre");
    }
    header("Location: cursos.php?ok=1"); exit();
}

if (isset($_GET['del'])) {
    $did = (int)$_GET['del'];
    $conn->query("DELETE FROM cursos WHERE id=$did");
    header("Location: cursos.php?ok=1"); exit();
}

$cursos = $conn->query("SELECT c.*,u.nombre profesor FROM cursos c LEFT JOIN usuarios u ON c.profesor_jefe_id=u.id ORDER BY c.año DESC,c.nivel,c.letra");
$profesores = $conn->query("SELECT id,nombre FROM usuarios WHERE rol='profesor' ORDER BY nombre");
$editar = null;
if (isset($_GET['edit'])) $editar = $conn->query("SELECT * FROM cursos WHERE id=".(int)$_GET['edit'])->fetch_assoc();
$conn->close();

$niveles = ['Pre-Kinder','Kinder','1° Básico','2° Básico','3° Básico','4° Básico','5° Básico','6° Básico','7° Básico','8° Básico','1° Medio','2° Medio','3° Medio','4° Medio'];
?>
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Cursos</title></head>
<body>
<?php include_sidebar('director','cursos'); global_css(".form-modal{position:fixed;inset:0;background:rgba(0,0,0,.6);display:flex;align-items:center;justify-content:center;z-index:999}.modal-box{background:#1a1f2e;border:1px solid rgba(255,255,255,0.12);border-radius:16px;padding:28px;width:480px}"); ?>
<div class="main">
    <div class="topbar">
        <div>
            <div class="page-title">📚 Gestión de Cursos</div>
            <div class="page-sub">Pre-Kinder a 4° Medio — Año 2025</div>
        </div>
        <button class="btn btn-primary" onclick="document.getElementById('m-crear').style.display='flex'">+ Nuevo Curso</button>
    </div>
    <?php if (isset($_GET['ok'])): ?><div class="alert alert-success">Operación realizada correctamente.</div><?php endif; ?>
    <div class="card">
        <table>
            <tr><th>#</th><th>Nombre</th><th>Nivel</th><th>Letra</th><th>Año</th><th>Profesor Jefe</th><th>Acciones</th></tr>
            <?php while ($c = $cursos->fetch_assoc()): ?>
            <tr>
                <td style="color:rgba(255,255,255,.35)"><?=$c['id']?></td>
                <td style="font-weight:600"><?=htmlspecialchars($c['nombre'])?></td>
                <td><?=htmlspecialchars($c['nivel'])?></td>
                <td><?=$c['letra']?></td>
                <td><?=$c['año']?></td>
                <td style="color:rgba(255,255,255,.5)"><?= $c['profesor'] ? '👨‍🏫 '.htmlspecialchars($c['profesor']) : '<span style="color:rgba(255,255,255,.25)">Sin asignar</span>' ?></td>
                <td>
                    <a href="?edit=<?=$c['id']?>" class="btn btn-sm btn-gray">✏️</a>
                    <a href="?del=<?=$c['id']?>" class="btn btn-sm btn-red" onclick="return confirm('¿Eliminar este curso?')">🗑️</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<!-- MODAL CREAR/EDITAR -->
<?php $modal_id = $editar ? 'm-editar' : 'm-crear'; ?>
<div id="m-crear" class="form-modal" style="display:none" onclick="if(event.target===this)this.style.display='none'">
<div class="modal-box">
    <h3 style="color:#fff;margin-bottom:18px">➕ Nuevo Curso</h3>
    <form method="POST">
        <input type="hidden" name="edit_id" value="0">
        <div class="grid2">
            <div class="form-group"><label>Nivel</label>
                <select name="nivel" required>
                    <?php foreach ($niveles as $nv): ?><option value="<?=$nv?>"><?=$nv?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="form-group"><label>Letra</label>
                <select name="letra" required>
                    <?php foreach (['A','B','C','D'] as $l): ?><option value="<?=$l?>"><?=$l?></option><?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-group"><label>Nombre completo</label><input name="nombre" placeholder="Ej: 8° Básico A" required></div>
        <div class="grid2">
            <div class="form-group"><label>Año</label><input type="number" name="año" value="2025" required></div>
            <div class="form-group"><label>Profesor Jefe</label>
                <select name="profesor_jefe">
                    <option value="">Sin asignar</option>
                    <?php while ($p = $profesores->fetch_assoc()): ?><option value="<?=$p['id']?>"><?=htmlspecialchars($p['nombre'])?></option><?php endwhile; ?>
                </select>
            </div>
        </div>
        <div style="display:flex;gap:10px">
            <button class="btn btn-primary">✅ Crear</button>
            <button type="button" class="btn btn-gray" onclick="document.getElementById('m-crear').style.display='none'">Cancelar</button>
        </div>
    </form>
</div>
</div>

<?php if ($editar): ?>
<div id="m-editar" class="form-modal" style="display:flex" onclick="if(event.target===this)location.href='cursos.php'">
<div class="modal-box">
    <h3 style="color:#fff;margin-bottom:18px">✏️ Editar — <?=htmlspecialchars($editar['nombre'])?></h3>
    <form method="POST">
        <input type="hidden" name="edit_id" value="<?=$editar['id']?>">
        <div class="grid2">
            <div class="form-group"><label>Nivel</label>
                <select name="nivel" required>
                    <?php foreach ($niveles as $nv): ?><option value="<?=$nv?>" <?=$editar['nivel']===$nv?'selected':''?>><?=$nv?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="form-group"><label>Letra</label>
                <select name="letra" required>
                    <?php foreach (['A','B','C','D'] as $l): ?><option value="<?=$l?>" <?=$editar['letra']===$l?'selected':''?>><?=$l?></option><?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-group"><label>Nombre</label><input name="nombre" value="<?=htmlspecialchars($editar['nombre'])?>" required></div>
        <div class="grid2">
            <div class="form-group"><label>Año</label><input type="number" name="año" value="<?=$editar['año']?>" required></div>
            <div class="form-group"><label>Profesor Jefe</label>
                <select name="profesor_jefe">
                    <option value="">Sin asignar</option>
                    <?php
                    $conn2 = getDB();
                    $profs2 = $conn2->query("SELECT id,nombre FROM usuarios WHERE rol='profesor' ORDER BY nombre");
                    while ($p = $profs2->fetch_assoc()):
                    ?><option value="<?=$p['id']?>" <?=$editar['profesor_jefe_id']==$p['id']?'selected':''?>><?=htmlspecialchars($p['nombre'])?></option><?php endwhile; $conn2->close(); ?>
                </select>
            </div>
        </div>
        <div style="display:flex;gap:10px">
            <button class="btn btn-primary">💾 Guardar</button>
            <a href="cursos.php" class="btn btn-gray">Cancelar</a>
        </div>
    </form>
</div>
</div>
<?php endif; ?>
</body></html>
