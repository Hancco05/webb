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
    'admin'      => [['../dashboard_admin.php','🏠 Inicio'],['../admin/usuarios.php','👥 Usuarios'],['../shared/perfil.php','👤 Mi Perfil'],['../shared/cambiar_password.php','🔑 Contraseña'],['actividad.php','📋 Actividad','active']],
    'secundario' => [['../dashboard_secundario.php','🏠 Inicio'],['../shared/perfil.php','👤 Mi Perfil'],['../shared/cambiar_password.php','🔑 Contraseña'],['actividad.php','📋 Actividad','active']],
    'usuario'    => [['../dashboard_usuario.php','🏠 Inicio'],['../shared/perfil.php','👤 Mi Perfil'],['../shared/cambiar_password.php','🔑 Contraseña']],
];

$conn = getDB();

// Admin ve TODO, los demás solo lo suyo
if ($rol === 'admin') {
    $logs = $conn->query("
        SELECT al.*, u.nombre, u.rol
        FROM actividad_log al
        JOIN usuarios u ON al.usuario_id = u.id
        ORDER BY al.fecha DESC
        LIMIT 100
    ");
} else {
    $stmt = $conn->prepare("
        SELECT al.*, u.nombre, u.rol
        FROM actividad_log al
        JOIN usuarios u ON al.usuario_id = u.id
        WHERE al.usuario_id = ?
        ORDER BY al.fecha DESC
        LIMIT 50
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $logs = $stmt->get_result();
}

function iconAccion($accion) {
    $map = [
        'crear_usuario'   => '➕',
        'editar_usuario'  => '✏️',
        'eliminar_usuario'=> '🗑️',
        'editar_perfil'   => '👤',
        'cambio_password' => '🔑',
        'login'           => '🔐',
    ];
    return $map[$accion] ?? '📋';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Registro de Actividad</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Segoe UI',sans-serif; background:#0d0d1a; color:#eee; display:flex; min-height:100vh; }
.sidebar { width:240px; background:<?= $c['grad'] ?>; padding:30px 20px; display:flex; flex-direction:column; gap:10px; }
.sidebar h2 { color:#fff; font-size:18px; margin-bottom:20px; text-align:center; }
.sidebar .badge { background:<?= $c['color'] ?>; color:#fff; padding:4px 10px; border-radius:20px; font-size:11px; display:inline-block; margin-bottom:20px; text-align:center; }
.sidebar a { display:block; padding:11px 16px; color:rgba(255,255,255,0.7); text-decoration:none; border-radius:10px; font-size:14px; transition:all .3s; }
.sidebar a:hover,.sidebar a.active { background:rgba(255,255,255,0.1); color:#fff; }
.sidebar .logout { margin-top:auto; background:rgba(233,69,96,0.15); border:1px solid rgba(233,69,96,0.3); color:#e94560; }
.main { flex:1; padding:30px; overflow-y:auto; }
h1 { font-size:22px; color:#fff; margin-bottom:8px; }
.subtitle { color:rgba(255,255,255,0.4); font-size:13px; margin-bottom:25px; }
.log-list { display:flex; flex-direction:column; gap:10px; }
.log-item {
    background:rgba(255,255,255,0.04); border-radius:12px; padding:16px 20px;
    border:1px solid rgba(255,255,255,0.07); display:flex; align-items:flex-start; gap:14px;
    transition:background .2s;
}
.log-item:hover { background:rgba(255,255,255,0.07); }
.log-icon { font-size:22px; flex-shrink:0; margin-top:2px; }
.log-body { flex:1; }
.log-accion { font-size:14px; color:#fff; font-weight:600; margin-bottom:3px; }
.log-detalle { font-size:13px; color:rgba(255,255,255,0.45); margin-bottom:5px; }
.log-meta { display:flex; gap:14px; font-size:12px; color:rgba(255,255,255,0.3); }
.rol-badge { padding:2px 8px; border-radius:20px; font-size:11px; font-weight:bold; }
.rol-admin      { background:rgba(124,58,237,0.2); color:#a855f7; }
.rol-secundario { background:rgba(59,130,246,0.2); color:#60a5fa; }
.rol-usuario    { background:rgba(16,185,129,0.2); color:#34d399; }
.empty { text-align:center; padding:50px; color:rgba(255,255,255,0.3); font-size:15px; }
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
    <h1>📋 Registro de Actividad</h1>
    <p class="subtitle"><?= $rol==='admin' ? 'Mostrando actividad de todos los usuarios (últimas 100 acciones)' : 'Mostrando tu actividad reciente' ?></p>

    <div class="log-list">
        <?php
        $count = 0;
        while ($log = $logs->fetch_assoc()):
            $count++;
        ?>
        <div class="log-item">
            <div class="log-icon"><?= iconAccion($log['accion']) ?></div>
            <div class="log-body">
                <div class="log-accion"><?= htmlspecialchars(str_replace('_', ' ', ucfirst($log['accion']))) ?></div>
                <?php if ($log['detalle']): ?>
                    <div class="log-detalle"><?= htmlspecialchars($log['detalle']) ?></div>
                <?php endif; ?>
                <div class="log-meta">
                    <?php if ($rol === 'admin'): ?>
                        <span><?= htmlspecialchars($log['nombre']) ?></span>
                        <span class="rol-badge rol-<?= $log['rol'] ?>"><?= ucfirst($log['rol']) ?></span>
                    <?php endif; ?>
                    <span>🕐 <?= date('d/m/Y H:i:s', strtotime($log['fecha'])) ?></span>
                    <span>🌐 <?= htmlspecialchars($log['ip']) ?></span>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
        <?php if ($count === 0): ?>
            <div class="empty">📭 No hay actividad registrada aún.</div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
