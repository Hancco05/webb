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
    <title><?php echo $titulo_pagina; ?> - Sistema Educativo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar simple -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-dark text-white vh-100 p-3">
                <h4 class="text-center">Sistema Educativo</h4>
                <hr>
                <ul class="nav nav-pills flex-column">
                    <li class="nav-item"><a href="dashboard.php" class="nav-link text-white"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                    <?php if ($rol == 'director'): ?>
                        <li class="nav-item"><a href="usuarios.php" class="nav-link text-white"><i class="bi bi-people"></i> Usuarios</a></li>
                        <li class="nav-item"><a href="cursos.php" class="nav-link text-white"><i class="bi bi-book"></i> Cursos</a></li>
                        <li class="nav-item"><a href="asignaturas.php" class="nav-link text-white"><i class="bi bi-journal"></i> Asignaturas</a></li>
                        <li class="nav-item"><a href="noticias.php" class="nav-link text-white"><i class="bi bi-newspaper"></i> Noticias</a></li>
                        <li class="nav-item"><a href="reportes.php" class="nav-link text-white"><i class="bi bi-file-earmark-pdf"></i> Reportes</a></li>
                    <?php elseif ($rol == 'profesor'): ?>
                        <li class="nav-item"><a href="mis_cursos.php" class="nav-link text-white"><i class="bi bi-book"></i> Mis Cursos</a></li>
                        <li class="nav-item"><a href="notas.php" class="nav-link text-white"><i class="bi bi-pencil-square"></i> Notas</a></li>
                        <li class="nav-item"><a href="asistencia.php" class="nav-link text-white"><i class="bi bi-calendar-check"></i> Asistencia</a></li>
                        <li class="nav-item"><a href="reportes.php" class="nav-link text-white"><i class="bi bi-file-earmark-pdf"></i> Reportes</a></li>
                    <?php elseif ($rol == 'auxiliar'): ?>
                        <li class="nav-item"><a href="asistencia.php" class="nav-link text-white"><i class="bi bi-calendar-check"></i> Asistencia</a></li>
                    <?php elseif ($rol == 'estudiante'): ?>
                        <li class="nav-item"><a href="mis_notas.php" class="nav-link text-white"><i class="bi bi-pencil-square"></i> Mis Notas</a></li>
                        <li class="nav-item"><a href="asistencia.php" class="nav-link text-white"><i class="bi bi-calendar-check"></i> Mi Asistencia</a></li>
                    <?php elseif ($rol == 'apoderado'): ?>
                        <li class="nav-item"><a href="hijos.php" class="nav-link text-white"><i class="bi bi-person-badge"></i> Mis Hijos</a></li>
                        <li class="nav-item"><a href="notas.php" class="nav-link text-white"><i class="bi bi-pencil-square"></i> Notas de Hijos</a></li>
                        <li class="nav-item"><a href="asistencia.php" class="nav-link text-white"><i class="bi bi-calendar-check"></i> Asistencia</a></li>
                        <li class="nav-item"><a href="noticias.php" class="nav-link text-white"><i class="bi bi-newspaper"></i> Noticias</a></li>
                        <li class="nav-item"><a href="recordatorios.php" class="nav-link text-white"><i class="bi bi-bell"></i> Recordatorios</a></li>
                        <li class="nav-item"><a href="reportes.php" class="nav-link text-white"><i class="bi bi-file-earmark-pdf"></i> Reportes</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a href="../shared/perfil.php" class="nav-link text-white"><i class="bi bi-person-circle"></i> Mi Perfil</a></li>
                    <li class="nav-item"><a href="../shared/cambiar_password.php" class="nav-link text-white"><i class="bi bi-key"></i> Cambiar Contraseña</a></li>
                    <li class="nav-item"><a href="../logout.php" class="nav-link text-white"><i class="bi bi-box-arrow-right"></i> Cerrar Sesión</a></li>
                </ul>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-3">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><?php echo $titulo_pagina; ?></h1>
                    <span class="badge bg-secondary"><?php echo ucfirst($rol); ?>: <?php echo htmlspecialchars($nombre); ?></span>
                </div>