<?php
require_once '../includes/auth.php';
verificarSesion('profesor');
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entrega_id = $_POST['entrega_id'];
    $calificacion = $_POST['calificacion'];
    $comentario = $_POST['comentario'] ?? null;
    if (calificarEntrega($entrega_id, $calificacion, $comentario)) {
        $_SESSION['mensaje'] = "Entrega calificada.";
    } else {
        $_SESSION['mensaje'] = "Error al calificar.";
    }
    header("Location: tareas.php");
    exit;
}
?>