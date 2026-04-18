<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php"); exit();
}
require_once '../includes/db.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) { header("Location: usuarios.php"); exit(); }

$conn = getDB();
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$usuario) { header("Location: usuarios.php"); exit(); }

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre      = trim($_POST['nombre']);
    $email       = trim($_POST['email']);
    $rol         = $_POST['rol'];
    $telefono    = trim($_POST['telefono']);
    $descripcion = trim($_POST['descripcion']);
    $nueva_pass  = trim($_POST['nueva_pass']);

    if (!$nombre || !$email || !$rol) {
        $error = "Nombre, email y rol son obligatorios.";
    } else {
        // Verificar email duplicado en otro usuario
        $chk = $conn->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
        $chk->bind_param("si", $email, $id);
        $chk->execute();
        $chk->store_result();

        if ($chk->num_rows > 0) {
            $error = "Ese email ya está en uso por otro usuario.";
        } else {
            if ($nueva_pass) {
                $hash = password_hash($nueva_pass, PASSWORD_BCRYPT);
                $upd = $conn->prepare("UPDATE usuarios SET nombre=?, email=?, password=?, rol=?, telefono=?, descripcion=? WHERE id=?");
                $upd->bind_param("ssssssi", $nombre, $email, $hash, $rol, $telefono, $descripcion, $id);
            } else {
                $upd = $conn->prepare("UPDATE usuarios SET nombre=?, email=?, rol=?, telefono=?, descripcion=? WHERE id=?");
                $upd->bind_param("sssssi", $nombre, $email, $rol, $telefono, $descripcion, $id);
            }
            $upd->execute();
            registrarActividad($conn, $_SESSION['id'], 'editar_usuario', "Editó al usuario ID $id: $nombre");
            $upd->close();
            $conn->close();
            header("Location: usuarios.php?editado=1"); exit();
        }
        $chk->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar Usuario</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Segoe UI',sans-serif; background:#0f0f1a; color:#eee; display:flex; min-height:100vh; }
.sidebar { width:240px; background:linear-gradient(180deg,#1a0533,#2d1b69); padding:30px 20px; display:flex; flex-direction:column; gap:10px; }
.sidebar h2 { color:#fff; font-size:18px; margin-bottom:20px; text-align:center; }
.sidebar .badge { background:linear-gradient(90deg,#7c3aed,#a855f7); color:#fff; padding:4px 10px; border-radius:20px; font-size:11px; display:inline-block; margin-bottom:20px; text-align:center; }
.sidebar a { display:block; padding:11px 16px; color:rgba(255,255,255,0.7); text-decoration:none; border-radius:10px; font-size:14px; transition:all .3s; }
.sidebar a:hover,.sidebar a.active { background:rgba(255,255,255,0.1); color:#fff; }
.sidebar .logout { margin-top:auto; background:rgba(233,69,96,0.15); border:1px solid rgba(233,69,96,0.3); color:#e94560; }
.main { flex:1; padding:30px; }
h1 { font-size:22px; color:#fff; margin-bottom:25px; }
.form-card { background:rgba(255,255,255,0.04); border-radius:16px; padding:30px; border:1px solid rgba(255,255,255,0.08); max-width:600px; }
.form-row { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
.form-group { margin-bottom:18px; }
.form-group label { display:block; color:rgba(255,255,255,0.6); font-size:13px; margin-bottom:7px; }
.form-group input, .form-group select, .form-group textarea {
    width:100%; padding:11px 14px; background:rgba(255,255,255,0.07);
    border:1px solid rgba(255,255,255,0.12); border-radius:10px; color:#fff; font-size:14px; outline:none; transition:border .3s;
}
.form-group input:focus,.form-group select:focus,.form-group textarea:focus { border-color:#7c3aed; }
.form-group select option { background:#1a1a2e; }
.form-group textarea { resize:vertical; min-height:80px; }
.form-group small { color:rgba(255,255,255,0.35); font-size:12px; margin-top:5px; display:block; }
.btns { display:flex; gap:12px; margin-top:5px; }
.btn { padding:11px 22px; border-radius:10px; border:none; cursor:pointer; font-size:14px; font-weight:bold; text-decoration:none; display:inline-block; transition:opacity .3s; }
.btn-purple { background:linear-gradient(90deg,#7c3aed,#a855f7); color:#fff; }
.btn-gray { background:rgba(255,255,255,0.08); color:rgba(255,255,255,0.6); }
.btn:hover { opacity:.8; }
.error { background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.3); color:#f87171; padding:12px; border-radius:10px; margin-bottom:18px; font-size:14px; }
.required { color:#e94560; }
.divider { border:none; border-top:1px solid rgba(255,255,255,0.07); margin:20px 0; }
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
    <h1>✏️ Editar Usuario — <?= htmlspecialchars($usuario['nombre']) ?></h1>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label>Nombre completo <span class="required">*</span></label>
                    <input type="text" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Email <span class="required">*</span></label>
                    <input type="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Rol <span class="required">*</span></label>
                    <select name="rol" required>
                        <option value="admin"      <?= $usuario['rol']==='admin'?'selected':'' ?>>👑 Administrador</option>
                        <option value="secundario" <?= $usuario['rol']==='secundario'?'selected':'' ?>>🛡️ Secundario</option>
                        <option value="usuario"    <?= $usuario['rol']==='usuario'?'selected':'' ?>>🙋 Usuario Normal</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="text" name="telefono" value="<?= htmlspecialchars($usuario['telefono']) ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Descripción</label>
                <textarea name="descripcion"><?= htmlspecialchars($usuario['descripcion']) ?></textarea>
            </div>
            <hr class="divider">
            <div class="form-group">
                <label>Nueva contraseña</label>
                <input type="password" name="nueva_pass" placeholder="Dejar vacío para no cambiar">
                <small>Solo completa si deseas cambiar la contraseña del usuario.</small>
            </div>
            <div class="btns">
                <button type="submit" class="btn btn-purple">💾 Guardar Cambios</button>
                <a href="usuarios.php" class="btn btn-gray">Cancelar</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
