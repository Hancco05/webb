<?php
require_once '../includes/auth.php';
verificarSesion('apoderado');
require_once '../includes/db.php';
require_once '../vendor/autoload.php';
use Dompdf\Dompdf;

$estudiante_id = $_SESSION['hijo_actual'] ?? 0;
if (!$estudiante_id) { header("Location: hijos.php"); exit; }

if (isset($_GET['tipo'])) {
    $tipo = $_GET['tipo'];
    $dompdf = new Dompdf();
    ob_start();
    echo "<html><head><title>Reporte</title><style>body{font-family:sans-serif;} table{width:100%;}</style></head><body>";
    if ($tipo == 'notas') {
        $stmt = $pdo->prepare("SELECT a.nombre as asignatura, n.periodo, n.nota FROM notas n JOIN asignaturas a ON n.asignatura_id = a.id WHERE n.estudiante_id = ? ORDER BY a.nombre, n.periodo");
        $stmt->execute([$estudiante_id]);
        $notas = $stmt->fetchAll();
        echo "<h2>Reporte de Notas</h2><table border='1'><tr><th>Asignatura</th><th>Período</th><th>Nota</th></tr>";
        foreach($notas as $n) echo "<tr><td>{$n['asignatura']}</td><td>{$n['periodo']}</td><td>{$n['nota']}</td></tr>";
        echo "</table>";
    } elseif ($tipo == 'asistencia') {
        $stmt = $pdo->prepare("SELECT fecha, estado FROM asistencia WHERE estudiante_id = ? ORDER BY fecha");
        $stmt->execute([$estudiante_id]);
        $asis = $stmt->fetchAll();
        echo "<h2>Reporte de Asistencia</h2><table border='1'><tr><th>Fecha</th><th>Estado</th></tr>";
        foreach($asis as $a) echo "<tr><td>{$a['fecha']}</td><td>{$a['estado']}</td></tr>";
        echo "</table>";
    }
    echo "</body></html>";
    $html = ob_get_clean();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream("reporte_$tipo.pdf", array("Attachment" => 0));
    exit;
}
$titulo_pagina = 'Reportes';
include '../includes/header.php';
?>
<div class="card"><div class="card-header">Generar Reporte del Hijo</div><div class="card-body"><a href="?tipo=notas" class="btn btn-primary" target="_blank">Notas PDF</a> <a href="?tipo=asistencia" class="btn btn-success" target="_blank">Asistencia PDF</a></div></div>
<?php include '../includes/footer.php'; ?>