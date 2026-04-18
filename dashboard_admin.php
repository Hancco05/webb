<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Conexión para obtener estadísticas
$conn = new mysqli("mysql", "root", "root123", "sistema_login");
$total_usuarios = $conn->query("SELECT COUNT(*) as total FROM usuarios")->fetch_assoc()['total'];
$admins         = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE rol='admin'")->fetch_assoc()['total'];
$secundarios    = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE rol='secundario'")->fetch_assoc()['total'];
$normales       = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE rol='usuario'")->fetch_assoc()['total'];
$lista_usuarios = $conn->query("SELECT nombre, email, rol, created_at FROM usuarios ORDER BY id");
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard — Admin Principal</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #0f0f1a; color: #eee; display: flex; min-height: 100vh; }

        /* Sidebar */
        .sidebar {
            width: 240px; background: linear-gradient(180deg, #1a0533, #2d1b69);
            padding: 30px 20px; display: flex; flex-direction: column; gap: 10px;
            box-shadow: 4px 0 20px rgba(0,0,0,0.4);
        }
        .sidebar h2 { color: #fff; font-size: 18px; margin-bottom: 20px; text-align: center; }
        .sidebar .badge {
            background: linear-gradient(90deg, #7c3aed, #a855f7);
            color: #fff; padding: 4px 10px; border-radius: 20px;
            font-size: 11px; display: inline-block; margin-bottom: 20px; text-align:center;
        }
        .sidebar a {
            display: block; padding: 11px 16px; color: rgba(255,255,255,0.7);
            text-decoration: none; border-radius: 10px; font-size: 14px; transition: all 0.3s;
        }
        .sidebar a:hover, .sidebar a.active {
            background: rgba(255,255,255,0.1); color: #fff;
        }
        .sidebar .logout {
            margin-top: auto; background: rgba(233,69,96,0.15);
            border: 1px solid rgba(233,69,96,0.3); color: #e94560;
        }
        .sidebar .logout:hover { background: rgba(233,69,96,0.3); }

        /* Main */
        .main { flex: 1; padding: 30px; overflow-y: auto; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .topbar h1 { font-size: 24px; color: #fff; }
        .topbar .user-info { color: rgba(255,255,255,0.5); font-size: 14px; }

        /* Cards */
        .cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .card {
            background: rgba(255,255,255,0.05); border-radius: 16px;
            padding: 20px; border: 1px solid rgba(255,255,255,0.08);
            text-align: center;
        }
        .card .icon { font-size: 32px; margin-bottom: 10px; }
        .card .num { font-size: 36px; font-weight: bold; color: #fff; }
        .card .label { font-size: 13px; color: rgba(255,255,255,0.5); margin-top: 4px; }
        .card.purple { border-top: 3px solid #7c3aed; }
        .card.blue   { border-top: 3px solid #3b82f6; }
        .card.green  { border-top: 3px solid #10b981; }
        .card.orange { border-top: 3px solid #f59e0b; }

        /* Table */
        .table-section { background: rgba(255,255,255,0.04); border-radius: 16px; padding: 25px; border: 1px solid rgba(255,255,255,0.08); }
        .table-section h3 { color: #fff; margin-bottom: 18px; font-size: 17px; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 10px 14px; font-size: 13px; color: rgba(255,255,255,0.4); border-bottom: 1px solid rgba(255,255,255,0.08); }
        td { padding: 12px 14px; font-size: 14px; border-bottom: 1px solid rgba(255,255,255,0.05); }
        tr:last-child td { border-bottom: none; }
        .rol-badge {
            padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: bold;
        }
        .rol-admin      { background: rgba(124,58,237,0.2); color: #a855f7; }
        .rol-secundario { background: rgba(59,130,246,0.2); color: #60a5fa; }
        .rol-usuario    { background: rgba(16,185,129,0.2); color: #34d399; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>⚡ Panel</h2>
        <span class="badge">👑 Admin Principal</span>
        <a href="#" class="active">🏠 Inicio</a>
        <a href="#">👥 Usuarios</a>
        <a href="#">⚙️ Configuración</a>
        <a href="#">📊 Reportes</a>
        <a href="#">🔔 Notificaciones</a>
        <a href="logout.php" class="logout">🚪 Cerrar sesión</a>
    </div>

    <div class="main">
        <div class="topbar">
            <h1>Bienvenido, <?= htmlspecialchars($_SESSION['usuario']) ?> 👑</h1>
            <span class="user-info">Administrador Principal • Panel de Control Total</span>
        </div>

        <div class="cards">
            <div class="card purple">
                <div class="icon">👥</div>
                <div class="num"><?= $total_usuarios ?></div>
                <div class="label">Total Usuarios</div>
            </div>
            <div class="card blue">
                <div class="icon">👑</div>
                <div class="num"><?= $admins ?></div>
                <div class="label">Administradores</div>
            </div>
            <div class="card green">
                <div class="icon">🛡️</div>
                <div class="num"><?= $secundarios ?></div>
                <div class="label">Secundarios</div>
            </div>
            <div class="card orange">
                <div class="icon">🙋</div>
                <div class="num"><?= $normales ?></div>
                <div class="label">Usuarios Normales</div>
            </div>
        </div>

        <div class="table-section">
            <h3>📋 Lista de todos los usuarios</h3>
            <table>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Fecha de registro</th>
                </tr>
                <?php while ($u = $lista_usuarios->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($u['nombre']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><span class="rol-badge rol-<?= $u['rol'] ?>"><?= ucfirst($u['rol']) ?></span></td>
                    <td><?= $u['created_at'] ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</body>
</html>
