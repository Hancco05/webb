<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}
$titulo_pagina = 'Mi Perfil';
include '../includes/header.php';

$user_id = $_SESSION['user_id'];
$usuario = obtenerDatosUsuario($user_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $stmt = $pdo->prepare("UPDATE usuarios SET nombre=?, email=? WHERE id=?");
    $stmt->execute([$nombre, $email, $user_id]);
    $_SESSION['nombre'] = $nombre;
    $_SESSION['mensaje'] = "Perfil actualizado";
    header("Location: perfil.php");
    exit;
}
?>
<div class="card">
    <div class="card-header">Mi Perfil</div>
    <div class="card-body">
        <?php if(isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-success"><?= $_SESSION['mensaje']; unset($_SESSION['mensaje']); ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label>Nombre</label>
                <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
            </div>
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($usuario['email']) ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Actualizar</button>
        </form>
    </div>
</div>
<?php include '../includes/footer.php'; ?>