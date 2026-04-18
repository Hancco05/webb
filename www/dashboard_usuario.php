<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'usuario') {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Panel — Usuario</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #0d1a0d; color: #eee; min-height: 100vh; display: flex; }

        .sidebar {
            width: 240px; background: linear-gradient(180deg, #0a2e0a, #1a4d1a);
            padding: 30px 20px; display: flex; flex-direction: column; gap: 10px;
            box-shadow: 4px 0 20px rgba(0,0,0,0.4);
        }
        .sidebar h2 { color: #fff; font-size: 18px; margin-bottom: 20px; text-align: center; }
        .sidebar .badge {
            background: linear-gradient(90deg, #059669, #10b981);
            color: #fff; padding: 4px 10px; border-radius: 20px;
            font-size: 11px; display: inline-block; margin-bottom: 20px; text-align:center;
        }
        .sidebar a {
            display: block; padding: 11px 16px; color: rgba(255,255,255,0.7);
            text-decoration: none; border-radius: 10px; font-size: 14px; transition: all 0.3s;
        }
        .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.1); color: #fff; }
        .sidebar .logout {
            margin-top: auto; background: rgba(16,185,129,0.15);
            border: 1px solid rgba(16,185,129,0.3); color: #34d399;
        }
        .sidebar .logout:hover { background: rgba(16,185,129,0.3); }

        .main { flex: 1; padding: 30px; }
        .topbar { margin-bottom: 30px; }
        .topbar h1 { font-size: 24px; color: #fff; margin-bottom: 4px; }
        .topbar p { color: rgba(255,255,255,0.4); font-size: 14px; }

        .welcome-card {
            background: linear-gradient(135deg, rgba(16,185,129,0.2), rgba(5,150,105,0.1));
            border: 1px solid rgba(16,185,129,0.3); border-radius: 20px;
            padding: 30px; margin-bottom: 25px; display: flex; align-items: center; gap: 20px;
        }
        .welcome-card .avatar {
            width: 70px; height: 70px; background: linear-gradient(135deg, #059669, #10b981);
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-size: 30px; flex-shrink: 0;
        }
        .welcome-card h2 { color: #fff; margin-bottom: 6px; }
        .welcome-card p { color: rgba(255,255,255,0.5); font-size: 14px; }

        .accesos { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 25px; }
        .acceso-card {
            background: rgba(255,255,255,0.04); border-radius: 14px; padding: 22px;
            border: 1px solid rgba(255,255,255,0.07); text-align: center;
            cursor: pointer; transition: all 0.3s; border-top: 3px solid #10b981;
        }
        .acceso-card:hover { background: rgba(16,185,129,0.1); transform: translateY(-3px); }
        .acceso-card .icon { font-size: 32px; margin-bottom: 10px; }
        .acceso-card .title { font-size: 14px; color: #fff; margin-bottom: 4px; }
        .acceso-card .desc { font-size: 12px; color: rgba(255,255,255,0.4); }

        .info-limitada {
            background: rgba(245,158,11,0.08); border: 1px solid rgba(245,158,11,0.25);
            border-radius: 14px; padding: 18px; font-size: 13px; color: rgba(255,255,255,0.5);
            display: flex; gap: 12px; align-items: flex-start;
        }
        .info-limitada .icon { font-size: 20px; flex-shrink: 0; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>🌿 Mi Panel</h2>
        <span class="badge">🙋 Usuario</span>
        <a href="#" class="active">🏠 Inicio</a>
        <a href="#">👤 Mi Perfil</a>
        <a href="#">📄 Mis Documentos</a>
        <a href="#">🔔 Notificaciones</a>
        <a href="logout.php" class="logout">🚪 Cerrar sesión</a>
    </div>

    <div class="main">
        <div class="topbar">
            <h1>¡Hola, <?= htmlspecialchars($_SESSION['usuario']) ?>! 👋</h1>
            <p>Usuario estándar • Acceso básico al sistema</p>
        </div>

        <div class="welcome-card">
            <div class="avatar">🙋</div>
            <div>
                <h2>Bienvenido a tu panel personal</h2>
                <p>Desde aquí puedes ver tu información, gestionar tu perfil y acceder a tus recursos asignados. Si necesitas más permisos, contacta a un administrador.</p>
            </div>
        </div>

        <div class="accesos">
            <div class="acceso-card">
                <div class="icon">👤</div>
                <div class="title">Mi Perfil</div>
                <div class="desc">Ver y editar tu información personal</div>
            </div>
            <div class="acceso-card">
                <div class="icon">📄</div>
                <div class="title">Mis Archivos</div>
                <div class="desc">Documentos y recursos disponibles</div>
            </div>
            <div class="acceso-card">
                <div class="icon">📩</div>
                <div class="title">Mensajes</div>
                <div class="desc">Comunicaciones del sistema</div>
            </div>
        </div>

        <div class="info-limitada">
            <span class="icon">⚠️</span>
            <span>Tu cuenta tiene acceso <strong style="color:#fbbf24">básico</strong>. No tienes acceso a la gestión de usuarios ni a la configuración del sistema. Si crees que necesitas más permisos, contacta al administrador principal.</span>
        </div>
    </div>
</body>
</html>
