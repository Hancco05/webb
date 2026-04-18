<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php"); exit();
}
require_once '../includes/db.php';
$conn = getDB();

$msg = "";
if (isset($_GET['eliminado'])) $msg = "success|Usuario eliminado correctamente.";
if (isset($_GET['creado']))    $msg = "success|Usuario creado correctamente.";
if (isset($_GET['editado']))   $msg = "success|Usuario actualizado correctamente.";

$usuarios = $conn->query("SELECT * FROM usuarios ORDER BY id");
$conn->close();

function badgeRol($rol) {
    $map = ['admin'=>'rol-admin','secundario'=>'rol-secundario','usuario'=>'rol-usuario'];
    $label = ['admin'=>'👑 Admin','secundario'=>'🛡️ Secundario','usuario'=>'🙋 Usuario'];
    return "<span class='rol-badge {$map[$rol]}'>{$label[$rol]}</span>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Gestión de Usuarios</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Segoe UI',sans-serif; background:#0f0f1a; color:#eee; display:flex; min-height:100vh; }
.sidebar { width:240px; background:linear-gradient(180deg,#1a0533,#2d1b69); padding:30px 20px; display:flex; flex-direction:column; gap:10px; }
.sidebar h2 { color:#fff; font-size:18px; margin-bottom:20px; text-align:center; }
.sidebar .badge { background:linear-gradient(90deg,#7c3aed,#a855f7); color:#fff; padding:4px 10px; border-radius:20px; font-size:11px; display:inline-block; margin-bottom:20px; text-align:center; }
.sidebar a { display:block; padding:11px 16px; color:rgba(255,255,255,0.7); text-decoration:none; border-radius:10px; font-size:14px; transition:all .3s; }
.sidebar a:hover, .sidebar a.active { background:rgba(255,255,255,0.1); color:#fff; }
.sidebar .logout { margin-top:auto; background:rgba(233,69,96,0.15); border:1px solid rgba(233,69,96,0.3); color:#e94560; }
.sidebar .logout:hover { background:rgba(233,69,96,0.3); }
.main { flex:1; padding:30px; overflow-y:auto; }
.topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:25px; }
.topbar h1 { font-size:22px; color:#fff; }
.btn { padding:10px 20px; border-radius:10px; border:none; cursor:pointer; font-size:14px; font-weight:bold; text-decoration:none; display:inline-block; transition:opacity .3s; }
.btn-purple { background:linear-gradient(90deg,#7c3aed,#a855f7); color:#fff; }
.btn-blue   { background:rgba(59,130,246,0.2); color:#60a5fa; border:1px solid rgba(59,130,246,0.3); }
.btn-red    { background:rgba(239,68,68,0.2); color:#f87171; border:1px solid rgba(239,68,68,0.3); }
.btn:hover  { opacity:.8; }
.msg { padding:12px 18px; border-radius:10px; margin-bottom:20px; font-size:14px; }
.msg.success { background:rgba(16,185,129,0.15); border:1px solid rgba(16,185,129,0.3); color:#34d399; }
.table-box { background:rgba(255,255,255,0.04); border-radius:16px; padding:25px; border:1px solid rgba(255,255,255,0.08); }
table { width:100%; border-collapse:collapse; }
th { text-align:left; padding:10px 14px; font-size:13px; color:rgba(255,255,255,0.4); border-bottom:1px solid rgba(255,255,255,0.08); }
td { padding:12px 14px; font-size:14px; border-bottom:1px solid rgba(255,255,255,0.05); vertical-align:middle; }
tr:last-child td { border-bottom:none; }
.rol-badge { padding:3px 10px; border-radius:20px; font-size:12px; font-weight:bold; }
.rol-admin      { background:rgba(124,58,237,0.2); color:#a855f7; }
.rol-secundario { background:rgba(59,130,246,0.2); color:#60a5fa; }
.rol-usuario    { background:rgba(16,185,129,0.2); color:#34d399; }
.acciones { display:flex; gap:8px; }
</style>
</head>
<body>
<div class="sidebar">
    <h2>⚡ Panel</h2>
    <span class="badge">👑 Admin Principal</span>
    <a href="../dashboard_admin.php">🏠 Inicio</a>
    <a href="usuarios.php" class="active">👥 Usuarios</a>
    <a href="../shared/perfil.php">👤 Mi Perfil</a>
    <a href="../shared/cambiar_password.php">🔑 Contraseña</a>
    <a href="../logs/actividad.php">📋 Actividad</a>
    <a href="../logout.php" class="logout">🚪 Cerrar sesión</a>
</div>
<div class="main">
    <div class="topbar">
        <h1>👥 Gestión de Usuarios</h1>
        <a href="crear_usuario.php" class="btn btn-purple">+ Nuevo Usuario</a>
    </div>

    <?php if ($msg): [$tipo, $texto] = explode("|", $msg); ?>
        <div class="msg <?= $tipo ?>"><?= $texto ?></div>
    <?php endif; ?>

    <div class="table-box">
        <table>
            <tr>
                <th>#</th><th>Nombre</th><th>Email</th><th>Teléfono</th><th>Rol</th><th>Registro</th><th>Acciones</th>
            </tr>
            <?php while ($u = $usuarios->fetch_assoc()): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td><?= htmlspecialchars($u['nombre']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['telefono']) ?></td>
                <td><?= badgeRol($u['rol']) ?></td>
                <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                <td>
                    <div class="acciones">
                        <a href="editar_usuario.php?id=<?= $u['id'] ?>" class="btn btn-blue">✏️ Editar</a>
                        <?php if ($u['id'] != $_SESSION['id']): ?>
                        <a href="eliminar_usuario.php?id=<?= $u['id'] ?>" class="btn btn-red"
                           onclick="return confirm('¿Eliminar a <?= htmlspecialchars($u['nombre']) ?>?')">🗑️ Eliminar</a>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>
</body>
</html>
