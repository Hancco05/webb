<?php
require_once '../includes/auth.php';
verificarSesion('director');
require_once '../includes/db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $hora_inicio = $_POST['hora_inicio'] ?: null;
    $hora_fin = $_POST['hora_fin'] ?: null;
    $tipo = $_POST['tipo'];
    $curso_id = $_POST['curso_id'] ?: null;
    $asignatura_id = $_POST['asignatura_id'] ?: null;
    crearEvento($titulo, $descripcion, $fecha_inicio, $fecha_fin, $hora_inicio, $hora_fin, $tipo, $curso_id, $asignatura_id, $_SESSION['user_id']);
    $_SESSION['mensaje'] = "Evento creado";
    header("Location: /shared/calendario.php");
    exit;
}
?>