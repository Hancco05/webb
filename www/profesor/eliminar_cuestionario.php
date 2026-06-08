<?php
require_once '../includes/auth.php';
verificarSesion('profesor');
require_once '../includes/db.php';
$id = $_GET['id'] ?? 0;
$cuestionario = obtenerCuestionario($id);
if ($cuestionario && $cuestionario['profesor_id'] == $_SESSION['user_id']) {
    eliminarCuestionario($id);
    $_SESSION['mensaje'] = "Cuestionario eliminado";
}
header("Location: cuestionarios.php");
exit;
?>