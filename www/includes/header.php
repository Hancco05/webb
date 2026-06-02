<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}
$rol = $_SESSION['rol'];
$nombre = $_SESSION['nombre'];
$titulo_pagina = $titulo_pagina ?? 'Panel de Control';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($titulo_pagina); ?> - Sistema Educativo</title>
    <!-- Bootstrap 5 CSS (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- FullCalendar (para calendario) -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <!-- Custom CSS (si no existe, no importa) -->
    <link rel="stylesheet" href="/assets/css/custom.css">
    <style>
        /* Estilos mínimos para que el sidebar se vea bien */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 260px;
            background-color: #0a2b4e;
            overflow-y: auto;
            z-index: 1000;
        }
        main {
            margin-left: 260px;
            padding: 1rem;
        }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); transition: transform 0.3s; }
            .sidebar.show { transform: translateX(0); }
            main { margin-left: 0; }
        }
        .sidebar .nav-link { color: white; }
        .sidebar .nav-link:hover { background-color: #1e3a6b; border-radius: 5px; }
        .sidebar-logo { text-align: center; padding: 20px 0; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 15px; }
        .sidebar-logo h4, .sidebar-logo h5 { color: white; margin: 0; }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-logo">
            <h4>Sistema Educativo</h4>
            <h5>Mi Colegio</h5>
        </div>
        <ul class="nav nav-pills flex-column">
            <li class="nav-item"><a href="dashboard.php" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            
            <?php if ($rol == 'director'): ?>
                <li class="nav-item"><a href="usuarios.php" class="nav-link"><i class="bi bi-people"></i> Usuarios</a></li>
                <li class="nav-item"><a href="cursos.php" class="nav-link"><i class="bi bi-book"></i> Cursos</a></li>
                <li class="nav-item"><a href="asignaturas.php" class="nav-link"><i class="bi bi-journal"></i> Asignaturas</a></li>
                <li class="nav-item"><a href="noticias.php" class="nav-link"><i class="bi bi-newspaper"></i> Noticias</a></li>
                <li class="nav-item"><a href="logs.php" class="nav-link"><i class="bi bi-journal-text"></i> Registro de actividades</a></li>
                <li class="nav-item"><a href="reportes.php" class="nav-link"><i class="bi bi-file-earmark-pdf"></i> Reportes</a></li>
                <li class="nav-item"><a href="../shared/calendario.php" class="nav-link"><i class="bi bi-calendar"></i> Calendario</a></li>
                <li class="nav-item"><a href="../shared/mensajes.php" class="nav-link"><i class="bi bi-envelope"></i> Mensajes</a></li>
            <?php elseif ($rol == 'profesor'): ?>
                <li class="nav-item"><a href="mis_cursos.php" class="nav-link"><i class="bi bi-book"></i> Mis Cursos</a></li>
                <li class="nav-item"><a href="notas.php" class="nav-link"><i class="bi bi-pencil-square"></i> Notas</a></li>
                <li class="nav-item"><a href="asistencia.php" class="nav-link"><i class="bi bi-calendar-check"></i> Asistencia</a></li>
                <li class="nav-item"><a href="horarios.php" class="nav-link"><i class="bi bi-clock"></i> Horarios</a></li>
                <li class="nav-item"><a href="tareas.php" class="nav-link"><i class="bi bi-list-check"></i> Tareas</a></li>
                <li class="nav-item"><a href="cuestionarios.php" class="nav-link"><i class="bi bi-question-circle"></i> Cuestionarios</a></li>
                <li class="nav-item"><a href="reportes.php" class="nav-link"><i class="bi bi-file-earmark-pdf"></i> Reportes</a></li>
                <li class="nav-item"><a href="../shared/calendario.php" class="nav-link"><i class="bi bi-calendar"></i> Calendario</a></li>
                <li class="nav-item"><a href="../shared/mensajes.php" class="nav-link"><i class="bi bi-envelope"></i> Mensajes</a></li>
            <?php elseif ($rol == 'auxiliar'): ?>
                <li class="nav-item"><a href="asistencia.php" class="nav-link"><i class="bi bi-calendar-check"></i> Asistencia</a></li>
                <li class="nav-item"><a href="../shared/calendario.php" class="nav-link"><i class="bi bi-calendar"></i> Calendario</a></li>
                <li class="nav-item"><a href="../shared/mensajes.php" class="nav-link"><i class="bi bi-envelope"></i> Mensajes</a></li>
            <?php elseif ($rol == 'estudiante'): ?>
                <li class="nav-item"><a href="mis_notas.php" class="nav-link"><i class="bi bi-pencil-square"></i> Mis Notas</a></li>
                <li class="nav-item"><a href="horarios.php" class="nav-link"><i class="bi bi-clock"></i> Mi Horario</a></li>
                <li class="nav-item"><a href="asistencia.php" class="nav-link"><i class="bi bi-calendar-check"></i> Mi Asistencia</a></li>
                <li class="nav-item"><a href="tareas.php" class="nav-link"><i class="bi bi-list-check"></i> Tareas</a></li>
                <li class="nav-item"><a href="cuestionarios.php" class="nav-link"><i class="bi bi-pencil-square"></i> Evaluaciones</a></li>
                <li class="nav-item"><a href="../shared/calendario.php" class="nav-link"><i class="bi bi-calendar"></i> Calendario</a></li>
                <li class="nav-item"><a href="../shared/mensajes.php" class="nav-link"><i class="bi bi-envelope"></i> Mensajes</a></li>
            <?php elseif ($rol == 'apoderado'): ?>
                <li class="nav-item"><a href="hijos.php" class="nav-link"><i class="bi bi-person-badge"></i> Mis Hijos</a></li>
                <li class="nav-item"><a href="notas.php" class="nav-link"><i class="bi bi-pencil-square"></i> Notas de Hijos</a></li>
                <li class="nav-item"><a href="asistencia.php" class="nav-link"><i class="bi bi-calendar-check"></i> Asistencia</a></li>
                <li class="nav-item"><a href="horarios.php" class="nav-link"><i class="bi bi-clock"></i> Horario</a></li>
                <li class="nav-item"><a href="tareas.php" class="nav-link"><i class="bi bi-list-check"></i> Tareas</a></li>
                <li class="nav-item"><a href="noticias.php" class="nav-link"><i class="bi bi-newspaper"></i> Noticias</a></li>
                <li class="nav-item"><a href="recordatorios.php" class="nav-link"><i class="bi bi-bell"></i> Recordatorios</a></li>
                <li class="nav-item"><a href="reportes.php" class="nav-link"><i class="bi bi-file-earmark-pdf"></i> Reportes</a></li>
                <li class="nav-item"><a href="../shared/calendario.php" class="nav-link"><i class="bi bi-calendar"></i> Calendario</a></li>
                <li class="nav-item"><a href="../shared/mensajes.php" class="nav-link"><i class="bi bi-envelope"></i> Mensajes</a></li>
            <?php endif; ?>
            
            <li class="nav-item"><a href="../shared/perfil.php" class="nav-link"><i class="bi bi-person-circle"></i> Mi Perfil</a></li>
            <li class="nav-item"><a href="../shared/cambiar_password.php" class="nav-link"><i class="bi bi-key"></i> Cambiar Contraseña</a></li>
            <li class="nav-item"><a href="../logout.php" class="nav-link"><i class="bi bi-box-arrow-right"></i> Cerrar Sesión</a></li>
        </ul>
    </nav>

    <!-- Contenido principal -->
    <main>
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2"><?php echo htmlspecialchars($titulo_pagina); ?></h1>
            <div>
                <span class="badge bg-secondary"><?php echo ucfirst($rol); ?>: <?php echo htmlspecialchars($nombre); ?></span>
            </div>
        </div>