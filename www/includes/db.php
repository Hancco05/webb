<?php
function getDB() {
    $conn = new mysqli("mysql", "root", "root123", "sistema_login");
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }
    $conn->set_charset("utf8");
    return $conn;
}

function registrarActividad($conn, $usuario_id, $accion, $detalle = "") {
    $stmt = $conn->prepare("INSERT INTO actividad_log (usuario_id, accion, detalle, ip) VALUES (?, ?, ?, ?)");
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
    $stmt->bind_param("isss", $usuario_id, $accion, $detalle, $ip);
    $stmt->execute();
    $stmt->close();
}
