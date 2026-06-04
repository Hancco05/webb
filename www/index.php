<?php
session_start();
if (isset($_SESSION['user_id'])) {
    $rol = $_SESSION['rol'];
    $redirect = match($rol) {
        'director' => '/director/dashboard.php',
        'profesor' => '/profesor/dashboard.php',
        'auxiliar' => '/auxiliar/dashboard.php',
        'estudiante' => '/estudiante/dashboard.php',
        'apoderado' => '/apoderado/dashboard.php',
        default => '/logout.php'
    };
    header("Location: $redirect");
    exit;
}
$num1 = rand(1, 10);
$num2 = rand(1, 10);
$_SESSION['captcha'] = $num1 + $num2;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Sistema Educativo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white"><h3>Inicio de Sesión</h3></div>
                <div class="card-body">
                    <?php if (isset($_GET['error'])): ?>
                        <?php if ($_GET['error'] == 'captcha'): ?>
                            <div class="alert alert-danger">Código de verificación incorrecto.</div>
                        <?php elseif ($_GET['error'] == 'bloqueado'): ?>
                            <div class="alert alert-danger">Demasiados intentos. Cuenta bloqueada 15 minutos.</div>
                        <?php else: ?>
                            <div class="alert alert-danger">Credenciales incorrectas.</div>
                        <?php endif; ?>
                    <?php endif; ?>
                    <form action="/includes/auth.php" method="POST">
                        <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control" required></div>
                        <div class="mb-3"><label>Contraseña</label><input type="password" name="password" class="form-control" required></div>
                        <div class="mb-3"><label>Captcha: ¿Cuánto es <?= $num1 ?> + <?= $num2 ?>?</label><input type="number" name="captcha" class="form-control" required></div>
                        <button type="submit" class="btn btn-primary w-100">Ingresar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>