<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php"); exit();
}
require_once '../includes/db.php';

$rol = $_SESSION['rol'];
$id  = $_SESSION['id'];

$config = [
    'admin'      => ['color'=>'#7c3aed','grad'=>'linear-gradient(180deg,#1a0533,#2d1b69)','badge'=>'👑 Admin Principal','icon'=>'⚡'],
    'secundario' => ['color'=>'#2563eb','grad'=>'linear-gradient(180deg,#0a2a4a,#1a4a7a)','badge'=>'🛡️ Admin Secundario','icon'=>'🛡️'],
    'usuario'    => ['color'=>'#059669','grad'=>'linear-gradient(180deg,#0a2e0a,#1a4d1a)','badge'=>'🙋 Usuario','icon'=>'🌿'],
];
$c = $config[$rol];

$links = [
    'admin'      => [['../dashboard_admin.php','🏠 Inicio'],['../admin/usuarios.php','👥 Usuarios'],['perfil.php','👤 Mi Perfil'],['cambiar_password.php','🔑 Contraseña','active'],['../logs/actividad.php','📋 Actividad']],
    'secundario' => [['../dashboard_secundario.php','🏠 Inicio'],['perfil.php','👤 Mi Perfil'],['cambiar_password.php','🔑 Contraseña','active'],['../logs/actividad.php','📋 Actividad']],
    'usuario'    => [['../dashboard_usuario.php','🏠 Inicio'],['perfil.php','👤 Mi Perfil'],['cambiar_password.php','🔑 Contraseña','active']],
];

$msg = $error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $actual   = $_POST['actual'];
    $nueva    = $_POST['nueva'];
    $confirma = $_POST['confirma'];

    $conn = getDB();
    $stmt = $conn->prepare("SELECT password FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $hash_actual = $stmt->get_result()->fetch_assoc()['password'];
    $stmt->close();

    if (!password_verify($actual, $hash_actual)) {
        $error = "La contraseña actual es incorrecta.";
    } elseif (strlen($nueva) < 6) {
        $error = "La nueva contraseña debe tener al menos 6 caracteres.";
    } elseif ($nueva !== $confirma) {
        $error = "Las contraseñas nuevas no coinciden.";
    } else {
        $nuevo_hash = password_hash($nueva, PASSWORD_BCRYPT);
        $upd = $conn->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
        $upd->bind_param("si", $nuevo_hash, $id);
        $upd->execute();
        $upd->close();
        registrarActividad($conn, $id, 'cambio_password', "Cambió su contraseña.");
        $msg = "Contraseña actualizada correctamente.";
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Cambiar Contraseña</title>
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
.form-card { background:rgba(255,255,255,0.04); border-radius:16px; padding:28px; border:1px solid rgba(255,255,255,0.08); max-width:480px; }
.form-group { margin-bottom:18px; }
.form-group label { display:block; color:rgba(255,255,255,0.55); font-size:13px; margin-bottom:7px; }
.form-group input {
    width:100%; padding:11px 14px; background:rgba(255,255,255,0.07);
    border:1px solid rgba(255,255,255,0.12); border-radius:10px; color:#fff; font-size:14px; outline:none; transition:border .3s;
}
.form-group input:focus { border-color:<?= $c['color'] ?>; }
.btn { padding:11px 22px; border-radius:10px; border:none; cursor:pointer; font-size:14px; font-weight:bold; color:#fff; background:<?= $c['color'] ?>; transition:opacity .3s; }
.btn:hover { opacity:.8; }
.msg { padding:12px 16px; border-radius:10px; margin-bottom:18px; font-size:14px; background:rgba(16,185,129,0.15); border:1px solid rgba(16,185,129,0.3); color:#34d399; }
.err { background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.3); color:#f87171; }
.tips { margin-top:20px; padding:16px; background:rgba(255,255,255,0.03); border-radius:12px; font-size:13px; color:rgba(255,255,255,0.4); }
.tips p { margin-bottom:5px; }
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
    <h1>🔑 Cambiar Contraseña</h1>

    <?php if ($msg): ?><div class="msg"><?= $msg ?></div><?php endif; ?>
    <?php if ($error): ?><div class="msg err"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="form-card">
        <form method="POST">
            <div class="form-group">
                <label>Contraseña actual</label>
                <input type="password" name="actual" placeholder="Tu contraseña actual" required>
            </div>
            <div class="form-group">
                <label>Nueva contraseña</label>
                <input type="password" name="nueva" placeholder="Mínimo 6 caracteres" required>
            </div>
            <div class="form-group">
                <label>Confirmar nueva contraseña</label>
                <input type="password" name="confirma" placeholder="Repite la nueva contraseña" required>
            </div>
            <button type="submit" class="btn">🔒 Actualizar contraseña</button>
        </form>

        <div class="tips">
            <p>💡 Consejos para una contraseña segura:</p>
            <p>• Usa al menos 8 caracteres</p>
            <p>• Combina letras, números y símbolos</p>
            <p>• No uses tu nombre o fecha de nacimiento</p>
        </div>
    </div>
</div>
</body>
</html>
