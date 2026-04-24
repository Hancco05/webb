<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}
$titulo_pagina = 'Cambiar Contraseña';
include '../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $actual = $_POST['actual'];
    $nueva = $_POST['nueva'];
    $confirmar = $_POST['confirmar'];
    $user_id = $_SESSION['user_id'];
    $usuario = obtenerDatosUsuario($user_id);
    if (password_verify($actual, $usuario['password_hash'])) {
        if ($nueva === $confirmar) {
            $hash = password_hash($nueva, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE usuarios SET password_hash=? WHERE id=?");
            $stmt->execute([$hash, $user_id]);
            $_SESSION['mensaje'] = "Contraseña actualizada";
            header("Location: cambiar_password.php");
            exit;
        } else {
            $error = "Las nuevas contraseñas no coinciden";
        }
    } else {
        $error = "Contraseña actual incorrecta";
    }
}
?>
<div class="card">
    <div class="card-header">Cambiar Contraseña</div>
    <div class="card-body">
        <?php if(isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-success"><?= $_SESSION['mensaje']; unset($_SESSION['mensaje']); ?></div>
        <?php endif; ?>
        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label>Contraseña actual</label>
                <input type="password" name="actual" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Nueva contraseña</label>
                <input type="password" name="nueva" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Confirmar nueva contraseña</label>
                <input type="password" name="confirmar" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Cambiar</button>
        </form>
    </div>
</div>
<?php include '../includes/footer.php'; ?>