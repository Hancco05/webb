<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php"); exit();
}
require_once '../includes/db.php';

$rol = $_SESSION['rol'];
$id  = $_SESSION['id'];

// Colores y textos por rol
$config = [
    'admin'      => ['color'=>'#7c3aed','grad'=>'linear-gradient(180deg,#1a0533,#2d1b69)','badge'=>'👑 Admin Principal','icon'=>'⚡'],
    'secundario' => ['color'=>'#2563eb','grad'=>'linear-gradient(180deg,#0a2a4a,#1a4a7a)','badge'=>'🛡️ Admin Secundario','icon'=>'🛡️'],
    'usuario'    => ['color'=>'#059669','grad'=>'linear-gradient(180deg,#0a2e0a,#1a4d1a)','badge'=>'🙋 Usuario','icon'=>'🌿'],
];
$c = $config[$rol];

// Links de sidebar según rol
$links = [
    'admin'      => [['../dashboard_admin.php','🏠 Inicio'],['../admin/usuarios.php','👥 Usuarios'],['perfil.php','👤 Mi Perfil','active'],['cambiar_password.php','🔑 Contraseña'],['../logs/actividad.php','📋 Actividad']],
    'secundario' => [['../dashboard_secundario.php','🏠 Inicio'],['perfil.php','👤 Mi Perfil','active'],['cambiar_password.php','🔑 Contraseña'],['../logs/actividad.php','📋 Actividad']],
    'usuario'    => [['../dashboard_usuario.php','🏠 Inicio'],['perfil.php','👤 Mi Perfil','active'],['cambiar_password.php','🔑 Contraseña']],
];

$conn = getDB();
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$msg = $error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre      = trim($_POST['nombre']);
    $telefono    = trim($_POST['telefono']);
    $descripcion = trim($_POST['descripcion']);

    if (!$nombre) {
        $error = "El nombre no puede estar vacío.";
    } else {
        $upd = $conn->prepare("UPDATE usuarios SET nombre=?, telefono=?, descripcion=? WHERE id=?");
        $upd->bind_param("sssi", $nombre, $telefono, $descripcion, $id);
        $upd->execute();
        $upd->close();
        $_SESSION['usuario'] = $nombre;
        registrarActividad($conn, $id, 'editar_perfil', "Actualizó su perfil.");
        $msg = "Perfil actualizado correctamente.";
        $user['nombre']      = $nombre;
        $user['telefono']    = $telefono;
        $user['descripcion'] = $descripcion;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Mi Perfil</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Segoe UI',sans-serif; background:#0d0d1a; color:#eee; display:flex; min-height:100vh; }
.sidebar { width:240px; background:<?= $c['grad'] ?>; padding:30px 20px; display:flex; flex-direction:column; gap:10px; }
.sidebar h2 { color:#fff; font-size:18px; margin-bottom:20px; text-align:center; }
.sidebar .badge { background:<?= $c['color'] ?>; color:#fff; padding:4px 10px; border-radius:20px; font-size:11px; display:inline-block; margin-bottom:20px; text-align:center; }
.sidebar a { display:block; padding:11px 16px; color:rgba(255,255,255,0.7); text-decoration:none; border-radius:10px; font-size:14px; transition:all .3s; }
.sidebar a:hover,.sidebar a.active { background:rgba(255,255,255,0.1); color:#fff; }
.sidebar .logout { margin-top:auto; background:rgba(233,69,96,0.15); border:1px solid rgba(233,69,96,0.3); color:#e94560; }
.main { flex:1; padding:30px; }
h1 { font-size:22px; color:#fff; margin-bottom:25px; }
.profile-top { display:flex; align-items:center; gap:20px; margin-bottom:28px; background:rgba(255,255,255,0.04); border-radius:16px; padding:24px; border:1px solid rgba(255,255,255,0.07); }
.avatar { width:72px; height:72px; border-radius:50%; background:<?= $c['color'] ?>; display:flex; align-items:center; justify-content:center; font-size:30px; flex-shrink:0; }
.profile-top h2 { color:#fff; font-size:20px; margin-bottom:4px; }
.profile-top p { color:rgba(255,255,255,0.45); font-size:13px; }
.form-card { background:rgba(255,255,255,0.04); border-radius:16px; padding:28px; border:1px solid rgba(255,255,255,0.08); max-width:560px; }
.form-group { margin-bottom:18px; }
.form-group label { display:block; color:rgba(255,255,255,0.55); font-size:13px; margin-bottom:7px; }
.form-group input,.form-group textarea {
    width:100%; padding:11px 14px; background:rgba(255,255,255,0.07);
    border:1px solid rgba(255,255,255,0.12); border-radius:10px; color:#fff; font-size:14px; outline:none; transition:border .3s;
}
.form-group input:focus,.form-group textarea:focus { border-color:<?= $c['color'] ?>; }
.form-group input[readonly] { opacity:.5; cursor:not-allowed; }
.form-group textarea { resize:vertical; min-height:80px; }
.btn { padding:11px 22px; border-radius:10px; border:none; cursor:pointer; font-size:14px; font-weight:bold; color:#fff; transition:opacity .3s; background:<?= $c['color'] ?>; }
.btn:hover { opacity:.8; }
.msg { padding:12px 16px; border-radius:10px; margin-bottom:18px; font-size:14px; background:rgba(16,185,129,0.15); border:1px solid rgba(16,185,129,0.3); color:#34d399; }
.err { background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.3); color:#f87171; }
</style>
</head>
<body>
<div class="sidebar">
    <h2><?= $c['icon'] ?> Panel</h2>
    <span class="badge"><?= $c['badge'] ?></span>
    <?php foreach ($links[$rol] as $l): ?>
        <a href="<?= $l[0] ?>" <?= isset($l[2]) ? 'class="active"' : '' ?>><?= $l[1] ?></a>
    <?php endforeach; ?>
    <a href="../logout.php" class="logout">🚪 Cerrar sesión</a>
</div>
<div class="main">
    <h1>👤 Mi Perfil</h1>

    <?php if ($msg): ?><div class="msg"><?= $msg ?></div><?php endif; ?>
    <?php if ($error): ?><div class="msg err"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="profile-top">
        <div class="avatar">👤</div>
        <div>
            <h2><?= htmlspecialchars($user['nombre']) ?></h2>
            <p><?= htmlspecialchars($user['email']) ?> • Miembro desde <?= date('d/m/Y', strtotime($user['created_at'])) ?></p>
        </div>
    </div>

    <div class="form-card">
        <form method="POST">
            <div class="form-group">
                <label>Nombre completo</label>
                <input type="text" name="nombre" value="<?= htmlspecialchars($user['nombre']) ?>" required>
            </div>
            <div class="form-group">
                <label>Email (no editable)</label>
                <input type="email" value="<?= htmlspecialchars($user['email']) ?>" readonly>
            </div>
            <div class="form-group">
                <label>Teléfono</label>
                <input type="text" name="telefono" value="<?= htmlspecialchars($user['telefono'] ?? '') ?>" placeholder="+56 9 ...">
            </div>
            <div class="form-group">
                <label>Descripción / Bio</label>
                <textarea name="descripcion"><?= htmlspecialchars($user['descripcion'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn">💾 Guardar cambios</button>
        </form>
    </div>
</div>
</body>
</html>
