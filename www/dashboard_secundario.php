<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'secundario') {
    header("Location: index.php");
    exit();
}

$conn = new mysqli("mysql", "root", "root123", "sistema_login");
$normales = $conn->query("SELECT nombre, email, created_at FROM usuarios WHERE rol='usuario'")->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard — Admin Secundario</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #0d1117; color: #eee; display: flex; min-height: 100vh; }

        .sidebar {
            width: 240px; background: linear-gradient(180deg, #0a2a4a, #1a4a7a);
            padding: 30px 20px; display: flex; flex-direction: column; gap: 10px;
            box-shadow: 4px 0 20px rgba(0,0,0,0.4);
        }
        .sidebar h2 { color: #fff; font-size: 18px; margin-bottom: 20px; text-align: center; }
        .sidebar .badge {
            background: linear-gradient(90deg, #2563eb, #3b82f6);
            color: #fff; padding: 4px 10px; border-radius: 20px;
            font-size: 11px; display: inline-block; margin-bottom: 20px; text-align:center;
        }
        .sidebar a {
            display: block; padding: 11px 16px; color: rgba(255,255,255,0.7);
            text-decoration: none; border-radius: 10px; font-size: 14px; transition: all 0.3s;
        }
        .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.1); color: #fff; }
        .sidebar .logout {
            margin-top: auto; background: rgba(59,130,246,0.15);
            border: 1px solid rgba(59,130,246,0.3); color: #60a5fa;
        }
        .sidebar .logout:hover { background: rgba(59,130,246,0.3); }

        .main { flex: 1; padding: 30px; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .topbar h1 { font-size: 24px; color: #fff; }
        .topbar .user-info { color: rgba(255,255,255,0.5); font-size: 14px; }

        .info-box {
            background: rgba(59,130,246,0.1); border: 1px solid rgba(59,130,246,0.3);
            border-radius: 16px; padding: 20px; margin-bottom: 25px;
            display: flex; align-items: center; gap: 15px;
        }
        .info-box .icon { font-size: 40px; }
        .info-box p { color: rgba(255,255,255,0.6); font-size: 14px; line-height: 1.6; }
        .info-box strong { color: #60a5fa; }

        .permisos { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 25px; }
        .permiso-card {
            background: rgba(255,255,255,0.04); border-radius: 14px; padding: 18px;
            border: 1px solid rgba(255,255,255,0.07); text-align: center;
        }
        .permiso-card .icon { font-size: 28px; margin-bottom: 8px; }
        .permiso-card .title { font-size: 14px; color: #fff; margin-bottom: 4px; }
        .permiso-card .desc { font-size: 12px; color: rgba(255,255,255,0.4); }
        .permiso-card.ok { border-top: 3px solid #10b981; }
        .permiso-card.no { border-top: 3px solid #ef4444; opacity: 0.5; }

        .table-section { background: rgba(255,255,255,0.04); border-radius: 16px; padding: 25px; border: 1px solid rgba(255,255,255,0.08); }
        .table-section h3 { color: #fff; margin-bottom: 18px; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 10px 14px; font-size: 13px; color: rgba(255,255,255,0.4); border-bottom: 1px solid rgba(255,255,255,0.08); }
        td { padding: 12px 14px; font-size: 14px; border-bottom: 1px solid rgba(255,255,255,0.05); }
        tr:last-child td { border-bottom: none; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>🛡️ Panel</h2>
        <span class="badge">🛡️ Admin Secundario</span>
        <a href="#" class="active">🏠 Inicio</a>
        <a href="#">👥 Ver Usuarios</a>
        <a href="#">📋 Actividad</a>
        <a href="#">🔔 Alertas</a>
        <a href="logout.php" class="logout">🚪 Cerrar sesión</a>
    </div>

    <div class="main">
        <div class="topbar">
            <h1>Bienvenido, <?= htmlspecialchars($_SESSION['usuario']) ?> 🛡️</h1>
            <span class="user-info">Administrador Secundario • Acceso Moderado</span>
        </div>

        <div class="info-box">
            <div class="icon">ℹ️</div>
            <p>Tienes acceso <strong>moderado</strong> al sistema. Puedes ver y gestionar usuarios normales, pero <strong>no puedes modificar administradores</strong> ni cambiar configuraciones del sistema.</p>
        </div>

        <div class="permisos">
            <div class="permiso-card ok">
                <div class="icon">✅</div>
                <div class="title">Ver Usuarios</div>
                <div class="desc">Puedes ver la lista de usuarios normales</div>
            </div>
            <div class="permiso-card ok">
                <div class="icon">✅</div>
                <div class="title">Generar Reportes</div>
                <div class="desc">Acceso a reportes básicos</div>
            </div>
            <div class="permiso-card ok">
                <div class="icon">✅</div>
                <div class="title">Enviar Alertas</div>
                <div class="desc">Puedes notificar al admin principal</div>
            </div>
            <div class="permiso-card no">
                <div class="icon">❌</div>
                <div class="title">Gestionar Admins</div>
                <div class="desc">Sin acceso</div>
            </div>
            <div class="permiso-card no">
                <div class="icon">❌</div>
                <div class="title">Configuración</div>
                <div class="desc">Sin acceso</div>
            </div>
            <div class="permiso-card no">
                <div class="icon">❌</div>
                <div class="title">Eliminar Datos</div>
                <div class="desc">Sin acceso</div>
            </div>
        </div>

        <div class="table-section">
            <h3>👥 Usuarios normales del sistema</h3>
            <table>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Registro</th>
                </tr>
                <?php foreach ($normales as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['nombre']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= $u['created_at'] ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</body>
</html>
