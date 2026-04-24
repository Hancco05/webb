<?php
session_start();
if (isset($_SESSION['user_id'])) {
    $rol = $_SESSION['rol'];
    switch ($rol) {
        case 'director': header('Location: director/dashboard.php'); break;
        case 'profesor': header('Location: profesor/dashboard.php'); break;
        case 'auxiliar': header('Location: auxiliar/dashboard.php'); break;
        case 'estudiante': header('Location: estudiante/dashboard.php'); break;
        case 'apoderado': header('Location: apoderado/dashboard.php'); break;
        default: header('Location: logout.php'); break;
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema Educativo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">Inicio de Sesión</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger">Credenciales incorrectas.</div>
                        <?php endif; ?>
                        <form action="includes/auth.php" method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Ingresar</button>
                        </form>
                        <hr>
                        <p class="text-muted">Usuarios de prueba: director@colegio.com / 123456, profesor@colegio.com / 123456, etc.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>