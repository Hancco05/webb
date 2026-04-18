<?php
session_start();

// Si ya está logueado, redirigir a su dashboard
if (isset($_SESSION['usuario'])) {
    $rol = $_SESSION['rol'];
    if ($rol === 'admin')       header("Location: dashboard_admin.php");
    if ($rol === 'secundario')  header("Location: dashboard_secundario.php");
    if ($rol === 'usuario')     header("Location: dashboard_usuario.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Conexión a MySQL
    $conn = new mysqli("mysql", "root", "root123", "sistema_login");

    if ($conn->connect_error) {
        $error = "Error de conexión: " . $conn->connect_error;
    } else {
        $stmt = $conn->prepare("SELECT id, nombre, password, rol FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['usuario'] = $user['nombre'];
                $_SESSION['rol']     = $user['rol'];
                $_SESSION['id']      = $user['id'];

                if ($user['rol'] === 'admin')       header("Location: dashboard_admin.php");
                if ($user['rol'] === 'secundario')  header("Location: dashboard_secundario.php");
                if ($user['rol'] === 'usuario')     header("Location: dashboard_usuario.php");
                exit();
            } else {
                $error = "Contraseña incorrecta.";
            }
        } else {
            $error = "Usuario no encontrado.";
        }
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #1a1a2e, #16213e, #0f3460);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-box {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px;
            padding: 40px;
            width: 380px;
            box-shadow: 0 25px 45px rgba(0,0,0,0.3);
        }
        .login-box h1 {
            color: #fff;
            text-align: center;
            margin-bottom: 8px;
            font-size: 28px;
        }
        .login-box p.subtitle {
            color: rgba(255,255,255,0.5);
            text-align: center;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            color: rgba(255,255,255,0.7);
            margin-bottom: 8px;
            font-size: 14px;
        }
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 10px;
            color: #fff;
            font-size: 15px;
            outline: none;
            transition: border 0.3s;
        }
        .form-group input:focus {
            border-color: #e94560;
        }
        .btn-login {
            width: 100%;
            padding: 13px;
            background: linear-gradient(90deg, #e94560, #c0392b);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: opacity 0.3s;
            margin-top: 5px;
        }
        .btn-login:hover { opacity: 0.85; }
        .error {
            background: rgba(233,69,96,0.2);
            border: 1px solid #e94560;
            color: #ff6b81;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }
        .hint {
            margin-top: 25px;
            padding: 15px;
            background: rgba(255,255,255,0.05);
            border-radius: 10px;
            font-size: 12px;
            color: rgba(255,255,255,0.4);
        }
        .hint p { margin-bottom: 4px; }
        .hint strong { color: rgba(255,255,255,0.6); }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>🔐 Sistema</h1>
        <p class="subtitle">Ingresa tus credenciales para continuar</p>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Correo electrónico</label>
                <input type="email" name="email" placeholder="tu@correo.com" required>
            </div>
            <div class="form-group">
                <label>Contraseña</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-login">Iniciar Sesión</button>
        </form>

        <div class="hint">
            <p><strong>Admin:</strong> admin@sistema.com</p>
            <p><strong>Secundario:</strong> secundario@sistema.com</p>
            <p><strong>Usuario:</strong> usuario@sistema.com</p>
            <p style="margin-top:6px">Contraseña para todos: <strong>password</strong></p>
        </div>
    </div>
</body>
</html>
