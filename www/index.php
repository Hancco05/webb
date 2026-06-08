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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Sistema Educativo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 35px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(2px);
            transition: transform 0.3s ease;
            overflow: hidden;
        }
        .login-card:hover {
            transform: translateY(-5px);
        }
        .card-header {
            background: linear-gradient(135deg, #4a00e0 0%, #8e2de2 100%);
            color: white;
            text-align: center;
            padding: 1.5rem;
            border-bottom: none;
        }
        .card-header h3 {
            margin: 0;
            font-weight: 600;
            letter-spacing: 1px;
        }
        .card-header i {
            font-size: 3rem;
            margin-bottom: 10px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 10px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            transform: scale(1.02);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .form-control {
            border-radius: 10px;
            padding: 12px;
            border: 1px solid #ddd;
            transition: all 0.3s;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .input-group-text {
            background-color: #f8f9fa;
            border-radius: 10px 0 0 10px;
        }
        .alert {
            border-radius: 10px;
        }
        .footer-text {
            text-align: center;
            margin-top: 20px;
            color: rgba(255,255,255,0.8);
            font-size: 0.9rem;
        }
        .footer-text a {
            color: white;
            text-decoration: none;
            font-weight: 600;
        }
        .captcha-box {
            background: #f0f2f5;
            padding: 12px;
            border-radius: 10px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
            letter-spacing: 5px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="login-card">
                    <div class="card-header">
                        <i class="bi bi-mortarboard-fill"></i>
                        <h3>Sistema Educativo</h3>
                        <p class="mb-0 small">Inicio de Sesión</p>
                    </div>
                    <div class="card-body p-4">
                        <?php if (isset($_GET['error'])): ?>
                            <?php if ($_GET['error'] == 'captcha'): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bi bi-exclamation-triangle-fill"></i> Código de verificación incorrecto.
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php elseif ($_GET['error'] == 'bloqueado'): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bi bi-hourglass-split"></i> Demasiados intentos. Cuenta bloqueada 15 minutos.
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bi bi-x-circle-fill"></i> Credenciales incorrectas.
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        <form action="/includes/auth.php" method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">Correo electrónico</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                                    <input type="email" name="email" id="email" class="form-control" placeholder="usuario@colegio.com" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                                    <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Verificación de seguridad</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="captcha-box text-center">
                                            <?= $num1 ?> + <?= $num2 ?> = ?
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-shield-lock-fill"></i></span>
                                            <input type="number" name="captcha" class="form-control" placeholder="Resultado" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-box-arrow-in-right"></i> Ingresar
                            </button>
                        </form>
                        <hr class="my-4">
                        <div class="text-center small text-muted">
                            <i class="bi bi-info-circle"></i> Usuarios de prueba:<br>
                            <strong>director@colegio.com</strong> / 123456<br>
                            <span class="text-secondary">profesor@colegio.com | estudiante@colegio.com | apoderado@colegio.com | auxiliar@colegio.com</span>
                        </div>
                    </div>
                </div>
                <div class="footer-text">
                    © <?= date('Y') ?> Sistema Educativo | Todos los derechos reservados
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>