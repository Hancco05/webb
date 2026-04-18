<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php"); exit();
}
require_once '../includes/db.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre']);
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);
    $rol      = $_POST['rol'];
    $telefono = trim($_POST['telefono']);
    $descripcion = trim($_POST['descripcion']);

    if (!$nombre || !$email || !$password || !$rol) {
        $error = "Todos los campos obligatorios deben estar completos.";
    } else {
        $conn = getDB();
        $check = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "Ya existe un usuario con ese email.";
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, password, rol, telefono, descripcion) VALUES (?,?,?,?,?,?)");
            $stmt->bind_param("ssssss", $nombre, $email, $hash, $rol, $telefono, $descripcion);
            $stmt->execute();
            $nuevo_id = $conn->insert_id;
            registrarActividad($conn, $_SESSION['id'], 'crear_usuario', "Creó al usuario: $nombre ($email) con rol: $rol");
            $stmt->close();
            $conn->close();
            header("Location: usuarios.php?creado=1"); exit();
        }
        $check->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Crear Usuario</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Segoe UI',sans-serif; background:#0f0f1a; color:#eee; display:flex; min-height:100vh; }
.sidebar { width:240px; background:linear-gradient(180deg,#1a0533,#2d1b69); padding:30px 20px; display:flex; flex-direction:column; gap:10px; }
.sidebar h2 { color:#fff; font-size:18px; margin-bottom:20px; text-align:center; }
.sidebar .badge { background:linear-gradient(90deg,#7c3aed,#a855f7); color:#fff; padding:4px 10px; border-radius:20px; font-size:11px; display:inline-block; margin-bottom:20px; text-align:center; }
.sidebar a { display:block; padding:11px 16px; color:rgba(255,255,255,0.7); text-decoration:none; border-radius:10px; font-size:14px; transition:all .3s; }
.sidebar a:hover, .sidebar a.active { background:rgba(255,255,255,0.1); color:#fff; }
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
.form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color:#7c3aed; }
.form-group select option { background:#1a1a2e; }
.form-group textarea { resize:vertical; min-height:80px; }
.btns { display:flex; gap:12px; margin-top:5px; }
.btn { padding:11px 22px; border-radius:10px; border:none; cursor:pointer; font-size:14px; font-weight:bold; text-decoration:none; display:inline-block; transition:opacity .3s; }
.btn-purple { background:linear-gradient(90deg,#7c3aed,#a855f7); color:#fff; }
.btn-gray   { background:rgba(255,255,255,0.08); color:rgba(255,255,255,0.6); }
.btn:hover  { opacity:.8; }
.error { background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.3); color:#f87171; padding:12px; border-radius:10px; margin-bottom:18px; font-size:14px; }
.required { color:#e94560; }
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
    <h1>➕ Crear Nuevo Usuario</h1>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label>Nombre completo <span class="required">*</span></label>
                    <input type="text" name="nombre" placeholder="Ej: Juan Pérez" required>
                </div>
                <div class="form-group">
                    <label>Email <span class="required">*</span></label>
                    <input type="email" name="email" placeholder="correo@ejemplo.com" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Contraseña <span class="required">*</span></label>
                    <input type="password" name="password" placeholder="Mínimo 6 caracteres" required>
                </div>
                <div class="form-group">
                    <label>Rol <span class="required">*</span></label>
                    <select name="rol" required>
                        <option value="">— Seleccionar —</option>
                        <option value="admin">👑 Administrador</option>
                        <option value="secundario">🛡️ Secundario</option>
                        <option value="usuario">🙋 Usuario Normal</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Teléfono</label>
                <input type="text" name="telefono" placeholder="+56 9 1234 5678">
            </div>
            <div class="form-group">
                <label>Descripción</label>
                <textarea name="descripcion" placeholder="Breve descripción del usuario..."></textarea>
            </div>
            <div class="btns">
                <button type="submit" class="btn btn-purple">✅ Crear Usuario</button>
                <a href="usuarios.php" class="btn btn-gray">Cancelar</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
