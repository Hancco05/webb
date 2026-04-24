<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nombre'] = $user['nombre'];
        $_SESSION['rol'] = $user['rol'];
        
        // Redirección según rol
        $rol = $user['rol'];
        if ($rol === 'director') {
            header('Location: ../director/dashboard.php');
        } elseif ($rol === 'profesor') {
            header('Location: ../profesor/dashboard.php');
        } elseif ($rol === 'auxiliar') {
            header('Location: ../auxiliar/dashboard.php');
        } elseif ($rol === 'estudiante') {
            header('Location: ../estudiante/dashboard.php');
        } elseif ($rol === 'apoderado') {
            header('Location: ../apoderado/dashboard.php');
        } else {
            header('Location: ../logout.php');
        }
        exit;
    } else {
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