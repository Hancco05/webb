<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /index.php');
    exit;
}
$rol = $_SESSION['rol'];
$nombre = $_SESSION['nombre'];
$titulo_pagina = $titulo_pagina ?? 'Panel de Control';

// Modo oscuro vía GET para cambiar y guardar en sesión
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
    <title><?= htmlspecialchars($titulo_pagina) ?> - Sistema Educativo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f8f9fc; color: #212529; transition: background-color 0.3s; }
        .sidebar { position: fixed; top: 0; left: 0; height: 100vh; width: 260px; background-color: #0a2b4e; overflow-y: auto; z-index: 1000; }
        main { margin-left: 260px; padding: 1rem; }
        @media (max-width: 768px) { .sidebar { transform: translateX(-100%); transition: transform 0.3s; } .sidebar.show { transform: translateX(0); } main { margin-left: 0; } }
        .sidebar .nav-link { color: white; }
        .sidebar .nav-link:hover { background-color: #1e3a6b; border-radius: 5px; }
        .sidebar-logo { text-align: center; padding: 20px 0; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 15px; }
        .sidebar-logo h4, .sidebar-logo h5 { color: white; margin: 0; }
        .card { background-color: #fff; border: none; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075); }
        .table { color: #212529; }

        html.dark-mode body { background-color: #1a1a2e; color: #e0e0e0; }
        html.dark-mode .sidebar { background-color: #0a0a15; }
        html.dark-mode .sidebar .nav-link { color: #ccc; }
        html.dark-mode .sidebar .nav-link:hover { background-color: #1e1e2e; }
        html.dark-mode .card { background-color: #16213e; color: #e0e0e0; }
        html.dark-mode .table { color: #e0e0e0; }
        html.dark-mode .table td, html.dark-mode .table th { border-color: #2c2c3e; }
        html.dark-mode .btn-outline-secondary { color: #e0e0e0; border-color: #e0e0e0; }
        html.dark-mode .btn-outline-secondary:hover { background-color: #e0e0e0; color: #1a1a2e; }
    </style>
</head>
<body>
<nav class="sidebar">
    <div class="sidebar-logo"><h4>Sistema Educativo</h4><h5>Mi Colegio</h5></div>
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
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1><?= htmlspecialchars($titulo_pagina) ?></h1>
        <div>
            <?php if ($theme_class === 'dark-mode'): ?>
                <a href="?theme=light" class="btn btn-sm btn-outline-secondary me-2"><i class="bi bi-sun"></i> Modo claro</a>
            <?php else: ?>
                <a href="?theme=dark" class="btn btn-sm btn-outline-secondary me-2"><i class="bi bi-moon-stars"></i> Modo oscuro</a>
            <?php endif; ?>
            <span class="badge bg-secondary"><?= ucfirst($rol) ?>: <?= htmlspecialchars($nombre) ?></span>
        </div>
    </div>