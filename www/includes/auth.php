<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $captcha = $_POST['captcha'] ?? '';
    $captcha_esperado = $_SESSION['captcha'] ?? '';

    // Validar captcha
    if ($captcha != $captcha_esperado) {
        header('Location: ../index.php?error=captcha');
        exit;
    }

    $ip = $_SERVER['REMOTE_ADDR'] ?? '';

    // Verificar intentos fallidos
    if (!verificarIntentosFallidos($email, $ip, 5, 15)) {
        header('Location: ../index.php?error=bloqueado');
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        // Login exitoso: limpiar intentos
        limpiarIntentosExitosos($email);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nombre'] = $user['nombre'];
        $_SESSION['rol'] = $user['rol'];
        registrarLog($user['id'], 'login', null, null, "Inicio de sesión exitoso");
        
        // Redirección según rol
        $rol = $user['rol'];
        $redirect = [
            'director' => '../director/dashboard.php',
            'profesor' => '../profesor/dashboard.php',
            'auxiliar' => '../auxiliar/dashboard.php',
            'estudiante' => '../estudiante/dashboard.php',
            'apoderado' => '../apoderado/dashboard.php',
        ];
        header('Location: ' . ($redirect[$rol] ?? '../logout.php'));
        exit;
    } else {
        // Login fallido: registrar intento
        registrarIntentoFallido($email, $ip);
        header('Location: ../index.php?error=1');
        exit;
    }
} else {
    function verificarSesion($rol_permitido = null) {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ../index.php');
            exit;
        }
        if ($rol_permitido && $_SESSION['rol'] !== $rol_permitido) {
            header('Location: ../index.php?error=acceso');
            exit;
        }
    }
}
?>