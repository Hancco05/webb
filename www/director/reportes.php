<?php
require_once '../includes/auth.php';
verificarSesion('director');
require_once '../includes/db.php';
require_once '../vendor/autoload.php';
use Dompdf\Dompdf;

if (isset($_GET['tipo'])) {
    $tipo = $_GET['tipo'];
    $dompdf = new Dompdf();
    ob_start();
    echo "<html><head><title>Reporte</title><style>body{font-family: sans-serif;} table{width:100%; border-collapse:collapse;} th,td{border:1px solid #ddd; padding:8px;}</style></head><body>";
    if ($tipo == 'usuarios') {
        $data = $pdo->query("SELECT id, nombre, email, rol, created_at FROM usuarios ORDER BY rol, nombre")->fetchAll();
        echo "<h2>Usuarios</h2><table><tr><th>ID</th><th>Nombre</th><th>Email</th><th>Rol</th><th>Registro</th></tr>";
        foreach($data as $row) echo "<tr><td>{$row['id']}</td><td>{$row['nombre']}</td><td>{$row['email']}</td><td>{$row['rol']}</td><td>{$row['created_at']}</td></tr>";
        echo "</table>";
    } elseif ($tipo == 'cursos') {
        $data = $pdo->query("SELECT c.*, COUNT(e.user_id) as estudiantes FROM cursos c LEFT JOIN estudiantes e ON c.id = e.curso_id GROUP BY c.id")->fetchAll();
        echo "<h2>Cursos</h2><table><tr><th>ID</th><th>Nombre</th><th>Año</th><th>Estudiantes</th></tr>";
        foreach($data as $row) echo "<tr><td>{$row['id']}</td><td>{$row['nombre']}</td><td>{$row['anio']}</td><td>{$row['estudiantes']}</td></tr>";
        echo "</table>";
    } elseif ($tipo == 'tareas') {
        $data = $pdo->query("SELECT t.titulo, c.nombre as curso, a.nombre as asignatura, t.fecha_entrega, u.nombre as profesor FROM tareas t JOIN cursos c ON t.curso_id = c.id JOIN asignaturas a ON t.asignatura_id = a.id JOIN usuarios u ON t.creado_por = u.id ORDER BY t.fecha_entrega")->fetchAll();
        echo "<h2>Tareas</h2><table><tr><th>Título</th><th>Curso</th><th>Asignatura</th><th>Entrega</th><th>Profesor</th></tr>";
        foreach($data as $row) echo "<tr><td>{$row['titulo']}</td><td>{$row['curso']}</td><td>{$row['asignatura']}</td><td>{$row['fecha_entrega']}</td><td>{$row['profesor']}</td></tr>";
        echo "</table>";
    }
    echo "</body></html>";
    $html = ob_get_clean();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
    $dompdf->stream("reporte_$tipo.pdf", array("Attachment" => 0));
    exit;
}
$titulo_pagina = 'Reportes';
include '../includes/header.php';
?>
<div class="row"><div class="col-md-6"><div class="card"><div class="card-header">Generar Reporte</div><div class="card-body"><a href="?tipo=usuarios" class="btn btn-primary" target="_blank">Usuarios PDF</a> <a href="?tipo=cursos" class="btn btn-success" target="_blank">Cursos PDF</a> <a href="?tipo=tareas" class="btn btn-info" target="_blank">Tareas PDF</a></div></div></div></div>
<?php include '../includes/footer.php'; ?>