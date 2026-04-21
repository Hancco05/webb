<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/header.php';
auth_required('director');
$u = current_user();
$conn = getDB();

// ── Eliminar ──────────────────────────────────────
if (isset($_GET['del'])) {
    $did = (int)$_GET['del'];
    if ($did !== $u['id']) {
        $nom = $conn->query("SELECT nombre FROM usuarios WHERE id=$did")->fetch_assoc()['nombre'] ?? '';
        $conn->query("DELETE FROM usuarios WHERE id=$did");
        log_actividad($conn, $u['id'], 'eliminar_usuario', "Eliminó: $nom");
    }
    header("Location: usuarios.php?ok=eliminado"); exit();
}

// ── Activar / Desactivar ──────────────────────────
if (isset($_GET['toggle'])) {
    $tid = (int)$_GET['toggle'];
    $actual = $conn->query("SELECT activo FROM usuarios WHERE id=$tid")->fetch_assoc()['activo'] ?? 1;
    $nuevo  = $actual ? 0 : 1;
    $conn->query("UPDATE usuarios SET activo=$nuevo WHERE id=$tid");
    log_actividad($conn, $u['id'], 'toggle_usuario', "Cambió estado activo=$nuevo a usuario ID $tid");
    header("Location: usuarios.php?ok=actualizado"); exit();
}

// ── Guardar (crear / editar) ──────────────────────
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre']);
    $email    = trim($_POST['email']);
    $rol      = $_POST['rol'];
    $telefono = trim($_POST['telefono']);
    $desc     = trim($_POST['descripcion']);
    $eid      = (int)($_POST['edit_id'] ?? 0);
    $avatares = ['director'=>'🎓','profesor'=>'👨‍🏫','auxiliar'=>'🔧','estudiante'=>'📚','apoderado'=>'👨‍👧'];
    $av = $avatares[$rol] ?? '👤';

    if (!$nombre || !$email || !$rol) {
        $error = "Nombre, email y rol son obligatorios.";
    } else {
        if ($eid) {
            $chk = $conn->prepare("SELECT id FROM usuarios WHERE email=? AND id!=?");
            $chk->bind_param("si",$email,$eid); $chk->execute(); $chk->store_result();
            if ($chk->num_rows > 0) { $error = "Ese email ya está en uso."; }
            else {
                $pass = trim($_POST['password'] ?? '');
                if ($pass) {
                    $h = password_hash($pass, PASSWORD_BCRYPT);
                    $s = $conn->prepare("UPDATE usuarios SET nombre=?,email=?,rol=?,telefono=?,descripcion=?,avatar=?,password=? WHERE id=?");
                    $s->bind_param("sssssssi",$nombre,$email,$rol,$telefono,$desc,$av,$h,$eid);
                } else {
                    $s = $conn->prepare("UPDATE usuarios SET nombre=?,email=?,rol=?,telefono=?,descripcion=?,avatar=? WHERE id=?");
                    $s->bind_param("ssssssi",$nombre,$email,$rol,$telefono,$desc,$av,$eid);
                }
                $s->execute(); $s->close();
                log_actividad($conn, $u['id'], 'editar_usuario', "Editó: $nombre");
                header("Location: usuarios.php?ok=editado"); exit();
            }
            $chk->close();
        } else {
            $pass = trim($_POST['password'] ?? '');
            if (!$pass) { $error = "La contraseña es obligatoria."; }
            else {
                $chk = $conn->prepare("SELECT id FROM usuarios WHERE email=?");
                $chk->bind_param("s",$email); $chk->execute(); $chk->store_result();
                if ($chk->num_rows > 0) { $error = "Ese email ya existe."; }
                else {
                    $h = password_hash($pass, PASSWORD_BCRYPT);
                    $s = $conn->prepare("INSERT INTO usuarios (nombre,email,password,rol,telefono,descripcion,avatar) VALUES (?,?,?,?,?,?,?)");
                    $s->bind_param("sssssss",$nombre,$email,$h,$rol,$telefono,$desc,$av);
                    $s->execute(); $s->close();
                    log_actividad($conn, $u['id'], 'crear_usuario', "Creó: $nombre ($rol)");
                    header("Location: usuarios.php?ok=creado"); exit();
                }
                $chk->close();
            }
        }
    }
}

// ── Filtros ───────────────────────────────────────
$filtro_rol    = $_GET['rol']    ?? '';
$filtro_activo = $_GET['activo'] ?? '';
$busqueda      = trim($_GET['q'] ?? '');

$where = "WHERE 1=1";
if ($filtro_rol)                 $where .= " AND rol='".mysqli_real_escape_string($conn,$filtro_rol)."'";
if ($filtro_activo !== '')       $where .= " AND activo=".((int)$filtro_activo);
if ($busqueda)                   $where .= " AND (nombre LIKE '%".mysqli_real_escape_string($conn,$busqueda)."%' OR email LIKE '%".mysqli_real_escape_string($conn,$busqueda)."%')";
$usuarios = $conn->query("SELECT * FROM usuarios $where ORDER BY rol,nombre");

// Contadores por rol
$contadores = [];
$cr = $conn->query("SELECT rol,COUNT(*) c FROM usuarios WHERE activo=1 GROUP BY rol");
while ($r = $cr->fetch_assoc()) $contadores[$r['rol']] = $r['c'];

$editar = isset($_GET['edit']) ? $conn->query("SELECT * FROM usuarios WHERE id=".(int)$_GET['edit'])->fetch_assoc() : null;
$conn->close();

$rol_cfg = [
    'director'   => ['🎓','#1e40af'],
    'profesor'   => ['👨‍🏫','#047857'],
    'auxiliar'   => ['🔧','#6b7280'],
    'estudiante' => ['📚','#0369a1'],
    'apoderado'  => ['👨‍👧','#7c3aed'],
];
$msgs = ['creado'=>'✅ Usuario creado correctamente.','editado'=>'✅ Usuario actualizado correctamente.','eliminado'=>'✅ Usuario eliminado.','actualizado'=>'✅ Estado actualizado.'];
?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><title>Gestión de Usuarios</title></head>
<body>
<?php include_sidebar('director','usuarios'); global_css("
.form-modal{position:fixed;inset:0;background:rgba(0,0,0,.65);display:flex;align-items:center;justify-content:center;z-index:999;padding:20px;}
.modal-box{background:#1a1f2e;border:1px solid rgba(255,255,255,.12);border-radius:16px;padding:28px;width:540px;max-height:90vh;overflow-y:auto;}
.modal-box h3{color:#fff;font-size:17px;margin-bottom:20px;}
.user-row:hover{background:rgba(255,255,255,.04);}
.filter-btn{padding:6px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.05);color:rgba(255,255,255,.6);text-decoration:none;font-size:13px;transition:all .2s;cursor:pointer;}
.filter-btn:hover,.filter-btn.active{background:rgba(59,130,246,.2);border-color:rgba(59,130,246,.4);color:#93c5fd;}
"); ?>
<div class="main">
    <div class="topbar">
        <div>
            <div class="page-title">👥 Gestión de Usuarios</div>
            <div class="page-sub">Administra todos los miembros del establecimiento</div>
        </div>
        <button class="btn btn-primary" onclick="document.getElementById('m-crear').style.display='flex'">➕ Nuevo Usuario</button>
    </div>

    <?php if (isset($_GET['ok']) && isset($msgs[$_GET['ok']])): ?>
    <div class="alert alert-success"><?=$msgs[$_GET['ok']]?></div>
    <?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?=htmlspecialchars($error)?></div><?php endif; ?>

    <!-- Resumen por rol -->
    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:18px">
        <?php foreach ($rol_cfg as $rv=>[$ico,$color]): ?>
        <div style="background:<?=$color?>18;border:1px solid <?=$color?>33;border-radius:10px;padding:8px 14px;display:flex;align-items:center;gap:8px">
            <span><?=$ico?></span>
            <span style="font-size:13px;color:rgba(255,255,255,.6)"><?=ucfirst($rv)?></span>
            <span style="font-weight:700;color:<?=$color?>;font-size:15px"><?=$contadores[$rv]??0?></span>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Filtros y búsqueda -->
    <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:16px">
        <input type="text" name="q" value="<?=htmlspecialchars($busqueda)?>" placeholder="🔍 Buscar nombre o email..." style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);border-radius:9px;color:#fff;padding:9px 13px;font-size:13px;outline:none;flex:1;min-width:200px">
        <select name="rol" style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);border-radius:9px;color:#fff;padding:9px 12px;outline:none;font-size:13px">
            <option value="">Todos los roles</option>
            <?php foreach ($rol_cfg as $rv=>[$ico,$_]): ?><option value="<?=$rv?>" <?=$filtro_rol===$rv?'selected':''?>><?=$ico?> <?=ucfirst($rv)?></option><?php endforeach; ?>
        </select>
        <select name="activo" style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);border-radius:9px;color:#fff;padding:9px 12px;outline:none;font-size:13px">
            <option value="">Todos</option>
            <option value="1" <?=$filtro_activo==='1'?'selected':''?>>✅ Activos</option>
            <option value="0" <?=$filtro_activo==='0'?'selected':''?>>❌ Inactivos</option>
        </select>
        <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
        <a href="usuarios.php" class="btn btn-gray btn-sm">Limpiar</a>
    </form>

    <!-- Tabla -->
    <div class="card">
        <table>
            <tr>
                <th>#</th><th>Usuario</th><th>Email</th><th>Rol</th><th>Teléfono</th><th>Estado</th><th>Registro</th><th>Acciones</th>
            </tr>
            <?php $cnt=0; while ($row = $usuarios->fetch_assoc()): $cnt++;
                [$ico,$color] = $rol_cfg[$row['rol']] ?? ['👤','#888'];
            ?>
            <tr class="user-row">
                <td style="color:rgba(255,255,255,.3);font-size:12px"><?=$row['id']?></td>
                <td>
                    <div style="display:flex;align-items:center;gap:8px">
                        <div style="width:32px;height:32px;border-radius:50%;background:<?=$color?>22;border:1px solid <?=$color?>44;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0"><?=$row['avatar']?></div>
                        <div>
                            <div style="font-size:13.5px;color:#fff;font-weight:600"><?=htmlspecialchars($row['nombre'])?></div>
                            <?php if($row['descripcion']): ?><div style="font-size:11px;color:rgba(255,255,255,.35)"><?=htmlspecialchars(substr($row['descripcion'],0,40))?></div><?php endif; ?>
                        </div>
                    </div>
                </td>
                <td style="color:rgba(255,255,255,.5);font-size:12px"><?=htmlspecialchars($row['email'])?></td>
                <td><span style="background:<?=$color?>22;color:<?=$color?>;padding:3px 9px;border-radius:20px;font-size:12px;font-weight:600"><?=$ico?> <?=ucfirst($row['rol'])?></span></td>
                <td style="color:rgba(255,255,255,.4);font-size:12px"><?=htmlspecialchars($row['telefono'])?></td>
                <td>
                    <?php if ($row['activo']): ?>
                    <span style="background:rgba(16,185,129,.15);color:#34d399;padding:3px 9px;border-radius:20px;font-size:12px">✅ Activo</span>
                    <?php else: ?>
                    <span style="background:rgba(239,68,68,.15);color:#f87171;padding:3px 9px;border-radius:20px;font-size:12px">❌ Inactivo</span>
                    <?php endif; ?>
                </td>
                <td style="color:rgba(255,255,255,.3);font-size:12px"><?=date('d/m/Y',strtotime($row['created_at']))?></td>
                <td>
                    <div style="display:flex;gap:5px">
                        <a href="?edit=<?=$row['id']?>" class="btn btn-sm btn-gray" title="Editar">✏️</a>
                        <?php if ($row['id'] != $u['id']): ?>
                        <a href="?toggle=<?=$row['id']?>" class="btn btn-sm btn-gray" title="<?=$row['activo']?'Desactivar':'Activar'?>"><?=$row['activo']?'🔴':'🟢'?></a>
                        <a href="?del=<?=$row['id']?>" class="btn btn-sm btn-red" onclick="return confirm('¿Eliminar a <?=htmlspecialchars(addslashes($row['nombre']))?>?')" title="Eliminar">🗑️</a>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endwhile;
            if (!$cnt): ?>
            <tr><td colspan="8" style="text-align:center;color:rgba(255,255,255,.3);padding:30px">Sin usuarios que coincidan con el filtro.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<!-- MODAL CREAR -->
<div id="m-crear" class="form-modal" style="display:none" onclick="if(event.target===this)this.style.display='none'">
<div class="modal-box">
    <h3>➕ Crear Nuevo Usuario</h3>
    <?php if ($error && !$editar): ?><div class="alert alert-error"><?=htmlspecialchars($error)?></div><?php endif; ?>
    <form method="POST">
        <input type="hidden" name="edit_id" value="0">
        <div class="grid2">
            <div class="form-group"><label>Nombre completo *</label><input name="nombre" required placeholder="Juan Pérez"></div>
            <div class="form-group"><label>Email institucional *</label><input type="email" name="email" required placeholder="juan@colegio.cl"></div>
        </div>
        <div class="grid2">
            <div class="form-group"><label>Contraseña *</label><input type="password" name="password" required placeholder="Mínimo 6 caracteres"></div>
            <div class="form-group"><label>Rol *</label>
                <select name="rol" required>
                    <option value="">— Seleccionar rol —</option>
                    <?php foreach ($rol_cfg as $rv=>[$ico,$_]): ?><option value="<?=$rv?>"><?=$ico?> <?=ucfirst($rv)?></option><?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-group"><label>Teléfono</label><input name="telefono" placeholder="+56 9 ..."></div>
        <div class="form-group"><label>Descripción / Cargo</label><textarea name="descripcion" placeholder="Ej: Profesor de Matemáticas 8° Básico"></textarea></div>
        <div style="display:flex;gap:10px">
            <button type="submit" class="btn btn-primary">✅ Crear Usuario</button>
            <button type="button" class="btn btn-gray" onclick="document.getElementById('m-crear').style.display='none'">Cancelar</button>
        </div>
    </form>
</div>
</div>

<!-- MODAL EDITAR -->
<?php if ($editar): ?>
<div class="form-modal" style="display:flex" onclick="if(event.target===this)location.href='usuarios.php'">
<div class="modal-box">
    <h3>✏️ Editar — <?=htmlspecialchars($editar['nombre'])?></h3>
    <?php if ($error): ?><div class="alert alert-error"><?=htmlspecialchars($error)?></div><?php endif; ?>
    <form method="POST">
        <input type="hidden" name="edit_id" value="<?=$editar['id']?>">
        <div class="grid2">
            <div class="form-group"><label>Nombre completo *</label><input name="nombre" value="<?=htmlspecialchars($editar['nombre'])?>" required></div>
            <div class="form-group"><label>Email *</label><input type="email" name="email" value="<?=htmlspecialchars($editar['email'])?>" required></div>
        </div>
        <div class="grid2">
            <div class="form-group"><label>Nueva contraseña</label><input type="password" name="password" placeholder="Dejar vacío para no cambiar"></div>
            <div class="form-group"><label>Rol *</label>
                <select name="rol" required>
                    <?php foreach ($rol_cfg as $rv=>[$ico,$_]): ?><option value="<?=$rv?>" <?=$editar['rol']===$rv?'selected':''?>><?=$ico?> <?=ucfirst($rv)?></option><?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-group"><label>Teléfono</label><input name="telefono" value="<?=htmlspecialchars($editar['telefono'])?>"></div>
        <div class="form-group"><label>Descripción</label><textarea name="descripcion"><?=htmlspecialchars($editar['descripcion'])?></textarea></div>
        <div style="display:flex;gap:10px">
            <button type="submit" class="btn btn-primary">💾 Guardar Cambios</button>
            <a href="usuarios.php" class="btn btn-gray">Cancelar</a>
        </div>
    </form>
</div>
</div>
<?php endif; ?>
</body>
</html>
