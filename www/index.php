<?php
// www/index.php
session_start();
require_once 'includes/db.php';

// Si ya está logueado, redirigir
if (!empty($_SESSION['usuario_id'])) {
    header('Location: /' . $_SESSION['rol'] . '/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email && $password) {
        $pdo  = getDB();
        $stmt = $pdo->prepare("
            SELECT u.id, u.nombre, u.email, u.password, r.nombre AS rol
            FROM usuarios u
            JOIN roles r ON r.id = u.rol_id
            WHERE u.email = ? AND u.activo = 1
            LIMIT 1
        ");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($password, $usuario['password'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['nombre']     = $usuario['nombre'];
            $_SESSION['email']      = $usuario['email'];
            $_SESSION['rol']        = $usuario['rol'];

            header('Location: /' . $usuario['rol'] . '/dashboard.php');
            exit;
        } else {
            $error = 'Correo o contraseña incorrectos.';
        }
    } else {
        $error = 'Por favor completa todos los campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Webb — Acceso</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: #0f172a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 12px;
            padding: 2.5rem;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 25px 50px rgba(0,0,0,.4);
        }

        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo h1 {
            color: #38bdf8;
            font-size: 2rem;
            letter-spacing: 3px;
            font-weight: 700;
        }

        .logo p {
            color: #94a3b8;
            font-size: .85rem;
            margin-top: .3rem;
        }

        label {
            display: block;
            color: #cbd5e1;
            font-size: .875rem;
            margin-bottom: .4rem;
            font-weight: 500;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: .75rem 1rem;
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 8px;
            color: #f1f5f9;
            font-size: 1rem;
            margin-bottom: 1.2rem;
            transition: border-color .2s;
        }

        input:focus {
            outline: none;
            border-color: #38bdf8;
        }

        button[type="submit"] {
            width: 100%;
            padding: .85rem;
            background: #0284c7;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background .2s;
        }

        button[type="submit"]:hover { background: #0369a1; }

        .error {
            background: #450a0a;
            border: 1px solid #b91c1c;
            color: #fca5a5;
            padding: .75rem 1rem;
            border-radius: 8px;
            margin-bottom: 1.2rem;
            font-size: .875rem;
        }

        .hint {
            margin-top: 1.5rem;
            background: #0c2340;
            border: 1px solid #1e4a7a;
            border-radius: 8px;
            padding: .9rem 1rem;
            color: #7dd3fc;
            font-size: .8rem;
        }

        .hint strong { display: block; margin-bottom: .4rem; color: #38bdf8; }
        .hint span { display: block; line-height: 1.7; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">
            <h1>WEBB</h1>
            <p>Sistema de Gestión Escolar</p>
        </div>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <label for="email">Correo institucional</label>
            <input
                type="email"
                id="email"
                name="email"
                placeholder="usuario@webb.cl"
                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                required
                autocomplete="email"
            >

            <label for="password">Contraseña</label>
            <input
                type="password"
                id="password"
                name="password"
                placeholder="••••••••"
                required
                autocomplete="current-password"
            >

            <button type="submit">Ingresar</button>
        </form>

        <div class="hint">
            <strong>🔑 Usuarios de prueba (contraseña: Test1234)</strong>
            <span>director@webb.cl</span>
            <span>profesor@webb.cl</span>
            <span>auxiliar@webb.cl</span>
            <span>estudiante@webb.cl</span>
            <span>apoderado@webb.cl</span>
        </div>
    </div>
</body>
</html>
