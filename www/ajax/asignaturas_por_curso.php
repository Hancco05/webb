<?php
require_once '../includes/db.php';
$curso_id = $_GET['curso_id'] ?? 0;
$stmt = $pdo->prepare("SELECT id, nombre FROM asignaturas WHERE curso_id = ?");
$stmt->execute([$curso_id]);
echo json_encode($stmt->fetchAll());
?>