<?php
require_once '../includes/auth.php';
verificarSesion('director');
require_once '../includes/db.php';
require_once '../vendor/autoload.php'; // Dompdf
use Dompdf\Dompdf;

$titulo_pagina = 'Reportes';
include '../includes/header.php';

if (isset($_GET['tipo'])) {
    $tipo = $_GET['tipo'];
    $dompdf = new Dompdf();
    ob_start();
    ?>
    <html>
    <head><title>Reporte</title><style>body{font-family: sans-serif;} table{width:100%; border-collapse:collapse;} th,td{border:1px solid #ddd; padding:8px;}</style></head>
    <body>
    <?php
    if ($tipo == 'usuarios') {
        $data = $pdo->query("SELECT id, nombre, email, rol, created_at FROM usuarios ORDER BY rol, nombre")->fetchAll();
        echo "<h2>Reporte de Usuarios</h2><table>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Email</th><th>Rol</th><th>Registro</th></tr>";
        foreach($data as $row) {
            echo "<tr><td>{$row['id']}</td><td>{$row['nombre']}</td><td>{$row['email']}</td><td>{$row['rol']}</td><td>{$row['created_at']}</td></tr>";
        }
        echo "</table>";
    } elseif ($tipo == 'cursos') {
        $data = $pdo->query("SELECT c.*, COUNT(e.user_id) as estudiantes FROM cursos c LEFT JOIN estudiantes e ON c.id = e.curso_id GROUP BY c.id")->fetchAll();
        echo "<h2>Reporte de Cursos</h2><table>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Año</th><th>Estudiantes</th></tr>";
        foreach($data as $row) {
            echo "<tr><td>{$row['id']}</td><td>{$row['nombre']}</td><td>{$row['anio']}</td><td>{$row['estudiantes']}</td></tr>";
        }
        echo "</table>";
    }
    $html = ob_get_clean();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
    $dompdf->stream("reporte_$tipo.pdf", array("Attachment" => 0));
    exit;
}
?>
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Generar Reporte</div>
            <div class="card-body">
                <a href="?tipo=usuarios" class="btn btn-primary" target="_blank">Reporte de Usuarios (PDF)</a>
                <a href="?tipo=cursos" class="btn btn-success" target="_blank">Reporte de Cursos (PDF)</a>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>