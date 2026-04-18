<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php"); exit();
}
require_once '../includes/db.php';

$id = intval($_GET['id'] ?? 0);

// No puede eliminarse a sí mismo
if (!$id || $id == $_SESSION['id']) {
    header("Location: usuarios.php"); exit();
}

$conn = getDB();
$stmt = $conn->prepare("SELECT nombre, email FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$u = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($u) {
    registrarActividad($conn, $_SESSION['id'], 'eliminar_usuario', "Eliminó al usuario: {$u['nombre']} ({$u['email']})");
    $del = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
    $del->bind_param("i", $id);
    $del->execute();
    $del->close();
}

$conn->close();
header("Location: usuarios.php?eliminado=1");
exit();
