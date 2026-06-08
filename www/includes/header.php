<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /index.php');
    exit;
}
$rol = $_SESSION['rol'];
$nombre = $_SESSION['nombre'];
$titulo_pagina = $titulo_pagina ?? 'Panel de Control';

// Modo oscuro vía GET (se guarda en sesión)
if (isset($_GET['theme'])) {
    $_SESSION['theme'] = $_GET['theme'] === 'dark' ? 'dark' : 'light';
    $redirect = strtok($_SERVER['REQUEST_URI'], '?');
    header("Location: $redirect");
    exit;
}
$theme_class = (isset($_SESSION['theme']) && $_SESSION['theme'] === 'dark') ? 'dark-mode' : '';
?>
<!DOCTYPE html>
<html lang="es" class="<?= $theme_class ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo_pagina) ?> - Sistema Educativo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <style>
        /* ---------- ESTILOS GLOBALES ---------- */
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
        }
        /* Sidebar moderno */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 260px;
            background: rgba(26, 26, 46, 0.9);
            backdrop-filter: blur(10px);
            color: white;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 2px 0 15px rgba(0,0,0,0.2);
            border-right: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar .nav-link {
            color: #f0f0f0;
            border-radius: 10px;
            margin: 5px 10px;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover {
            background: rgba(255,255,255,0.2);
            transform: translateX(5px);
        }
        .sidebar-logo {
            text-align: center;
            padding: 25px 0;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            margin-bottom: 15px;
        }
        .sidebar-logo h4, .sidebar-logo h5 {
            color: white;
            margin: 0;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        }
        /* Contenido principal */
        main {
            margin-left: 260px;
            padding: 1.5rem;
        }
        /* Tarjetas modernas */
        .card {
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(5px);
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            margin-bottom: 20px;
        }
        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        .card-header {
            background: rgba(255,255,255,0.7);
            border-bottom: 1px solid rgba(0,0,0,0.05);
            border-radius: 20px 20px 0 0 !important;
            font-weight: 600;
        }
        .table {
            background: transparent;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        /* Botones personalizados */
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 25px;
            padding: 8px 20px;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            transform: scale(1.02);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .btn-outline-secondary {
            border-radius: 25px;
        }
        /* Barra superior */
        .top-bar {
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(5px);
            border-radius: 15px;
            padding: 0.5rem 1rem;
            margin-bottom: 1.5rem;
        }
        /* Modo oscuro */
        html.dark-mode body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        }
        html.dark-mode .sidebar {
            background: rgba(10, 10, 21, 0.95);
        }
        html.dark-mode .card {
            background: rgba(30, 30, 50, 0.9);
            color: #e0e0e0;
        }
        html.dark-mode .card-header {
            background: rgba(40, 40, 60, 0.8);
            border-bottom-color: #2c2c3e;
        }
        html.dark-mode .table {
            color: #e0e0e0;
        }
        html.dark-mode .table td, 
        html.dark-mode .table th {
            border-color: #2c2c3e;
        }
        html.dark-mode .top-bar {
            background: rgba(0,0,0,0.3);
        }
        html.dark-mode .btn-outline-secondary {
            color: #e0e0e0;
            border-color: #e0e0e0;
        }
        html.dark-mode .btn-outline-secondary:hover {
            background-color: #e0e0e0;
            color: #1a1a2e;
        }
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }
            .sidebar.show {
                transform: translateX(0);
            }
            main {
                margin-left: 0;
            }
            .top-bar .btn-sm {
                margin-top: 5px;
            }
        }
    </style>
</head>
<body>

<!-- Sidebar moderno -->
<nav class="sidebar">
    <div class="sidebar-logo">
        <h4>Sistema Educativo</h4>
        <h5>Mi Colegio</h5>
    </div>
    <ul class="nav nav-pills flex-column">
        <?php if ($rol == 'director'): ?>
            <li class="nav-item"><a href="/director/dashboard.php" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li class="nav-item"><a href="/director/usuarios.php" class="nav-link"><i class="bi bi-people"></i> Usuarios</a></li>
            <li class="nav-item"><a href="/director/cursos.php" class="nav-link"><i class="bi bi-book"></i> Cursos</a></li>
            <li class="nav-item"><a href="/director/asignaturas.php" class="nav-link"><i class="bi bi-journal"></i> Asignaturas</a></li>
            <li class="nav-item"><a href="/director/noticias.php" class="nav-link"><i class="bi bi-newspaper"></i> Noticias</a></li>
            <li class="nav-item"><a href="/director/logs.php" class="nav-link"><i class="bi bi-journal-text"></i> Logs</a></li>
            <li class="nav-item"><a href="/director/reportes.php" class="nav-link"><i class="bi bi-file-earmark-pdf"></i> Reportes</a></li>
            <li class="nav-item"><a href="/shared/calendario.php" class="nav-link"><i class="bi bi-calendar"></i> Calendario</a></li>
            <li class="nav-item"><a href="/shared/mensajes.php" class="nav-link"><i class="bi bi-envelope"></i> Mensajes</a></li>
        <?php elseif ($rol == 'profesor'): ?>
            <li class="nav-item"><a href="/profesor/dashboard.php" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li class="nav-item"><a href="/profesor/mis_cursos.php" class="nav-link"><i class="bi bi-book"></i> Mis Cursos</a></li>
            <li class="nav-item"><a href="/profesor/notas.php" class="nav-link"><i class="bi bi-pencil-square"></i> Notas</a></li>
            <li class="nav-item"><a href="/profesor/asistencia.php" class="nav-link"><i class="bi bi-calendar-check"></i> Asistencia</a></li>
            <li class="nav-item"><a href="/profesor/horarios.php" class="nav-link"><i class="bi bi-clock"></i> Horarios</a></li>
            <li class="nav-item"><a href="/profesor/tareas.php" class="nav-link"><i class="bi bi-list-check"></i> Tareas</a></li>
            <li class="nav-item"><a href="/profesor/cuestionarios.php" class="nav-link"><i class="bi bi-question-circle"></i> Cuestionarios</a></li>
            <li class="nav-item"><a href="/profesor/reportes.php" class="nav-link"><i class="bi bi-file-earmark-pdf"></i> Reportes</a></li>
            <li class="nav-item"><a href="/shared/calendario.php" class="nav-link"><i class="bi bi-calendar"></i> Calendario</a></li>
            <li class="nav-item"><a href="/shared/mensajes.php" class="nav-link"><i class="bi bi-envelope"></i> Mensajes</a></li>
        <?php elseif ($rol == 'auxiliar'): ?>
            <li class="nav-item"><a href="/auxiliar/asistencia.php" class="nav-link"><i class="bi bi-calendar-check"></i> Asistencia</a></li>
            <li class="nav-item"><a href="/shared/calendario.php" class="nav-link"><i class="bi bi-calendar"></i> Calendario</a></li>
            <li class="nav-item"><a href="/shared/mensajes.php" class="nav-link"><i class="bi bi-envelope"></i> Mensajes</a></li>
        <?php elseif ($rol == 'estudiante'): ?>
            <li class="nav-item"><a href="/estudiante/mis_notas.php" class="nav-link"><i class="bi bi-pencil-square"></i> Mis Notas</a></li>
            <li class="nav-item"><a href="/estudiante/horarios.php" class="nav-link"><i class="bi bi-clock"></i> Mi Horario</a></li>
            <li class="nav-item"><a href="/estudiante/asistencia.php" class="nav-link"><i class="bi bi-calendar-check"></i> Mi Asistencia</a></li>
            <li class="nav-item"><a href="/estudiante/tareas.php" class="nav-link"><i class="bi bi-list-check"></i> Tareas</a></li>
            <li class="nav-item"><a href="/estudiante/cuestionarios.php" class="nav-link"><i class="bi bi-pencil-square"></i> Evaluaciones</a></li>
            <li class="nav-item"><a href="/shared/calendario.php" class="nav-link"><i class="bi bi-calendar"></i> Calendario</a></li>
            <li class="nav-item"><a href="/shared/mensajes.php" class="nav-link"><i class="bi bi-envelope"></i> Mensajes</a></li>
        <?php elseif ($rol == 'apoderado'): ?>
            <li class="nav-item"><a href="/apoderado/hijos.php" class="nav-link"><i class="bi bi-person-badge"></i> Mis Hijos</a></li>
            <li class="nav-item"><a href="/apoderado/notas.php" class="nav-link"><i class="bi bi-pencil-square"></i> Notas de Hijos</a></li>
            <li class="nav-item"><a href="/apoderado/asistencia.php" class="nav-link"><i class="bi bi-calendar-check"></i> Asistencia</a></li>
            <li class="nav-item"><a href="/apoderado/horarios.php" class="nav-link"><i class="bi bi-clock"></i> Horario</a></li>
            <li class="nav-item"><a href="/apoderado/tareas.php" class="nav-link"><i class="bi bi-list-check"></i> Tareas</a></li>
            <li class="nav-item"><a href="/apoderado/noticias.php" class="nav-link"><i class="bi bi-newspaper"></i> Noticias</a></li>
            <li class="nav-item"><a href="/apoderado/recordatorios.php" class="nav-link"><i class="bi bi-bell"></i> Recordatorios</a></li>
            <li class="nav-item"><a href="/apoderado/reportes.php" class="nav-link"><i class="bi bi-file-earmark-pdf"></i> Reportes</a></li>
            <li class="nav-item"><a href="/shared/calendario.php" class="nav-link"><i class="bi bi-calendar"></i> Calendario</a></li>
            <li class="nav-item"><a href="/shared/mensajes.php" class="nav-link"><i class="bi bi-envelope"></i> Mensajes</a></li>
        <?php endif; ?>
        <li class="nav-item"><a href="/shared/perfil.php" class="nav-link"><i class="bi bi-person-circle"></i> Mi Perfil</a></li>
        <li class="nav-item"><a href="/shared/cambiar_password.php" class="nav-link"><i class="bi bi-key"></i> Cambiar Contraseña</a></li>
        <li class="nav-item"><a href="/logout.php" class="nav-link"><i class="bi bi-box-arrow-right"></i> Cerrar Sesión</a></li>
    </ul>
</nav>

<main>
    <!-- Barra superior con título, modo oscuro y datos de usuario -->
    <div class="top-bar d-flex flex-wrap justify-content-between align-items-center">
        <h1 class="h3 mb-0"><?= htmlspecialchars($titulo_pagina) ?></h1>
        <div class="mt-2 mt-sm-0">
            <?php if ($theme_class === 'dark-mode'): ?>
                <a href="?theme=light" class="btn btn-sm btn-outline-secondary me-2"><i class="bi bi-sun"></i> Modo claro</a>
            <?php else: ?>
                <a href="?theme=dark" class="btn btn-sm btn-outline-secondary me-2"><i class="bi bi-moon-stars"></i> Modo oscuro</a>
            <?php endif; ?>
            <span class="badge bg-dark"><?= ucfirst($rol) ?>: <?= htmlspecialchars($nombre) ?></span>
        </div>
    </div>
    <!-- Aquí se inyecta el contenido específico de cada página -->