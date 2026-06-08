<?php
require_once '../includes/auth.php';
verificarSesion('director');
require_once '../includes/db.php';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="logs_export_' . date('Y-m-d') . '.csv"');
$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Usuario', 'Rol', 'Acción', 'Tabla', 'Registro ID', 'Detalles', 'IP', 'Fecha']);
$logs = $pdo->query("SELECT id, usuario_nombre, usuario_rol, accion, tabla_afectada, registro_id, detalles, ip_address, created_at FROM logs ORDER BY created_at DESC")->fetchAll();
foreach ($logs as $log) {
    fputcsv($output, [$log['id'], $log['usuario_nombre'], $log['usuario_rol'], $log['accion'], $log['tabla_afectada'], $log['registro_id'], $log['detalles'], $log['ip_address'], $log['created_at']]);
}
fclose($output);
exit;
?>