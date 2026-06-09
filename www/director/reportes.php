<?php
require_once '../includes/auth.php';
verificarSesion('director');
require_once '../includes/db.php';
require_once '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

if (isset($_GET['tipo'])) {
    $tipo = $_GET['tipo'];
    
    $optionsPdf = new Options();
    $optionsPdf->set('isHtml5ParserEnabled', true);
    $optionsPdf->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($optionsPdf);
    
    $usuario = obtenerDatosUsuario($_SESSION['user_id']);
    $fecha_emision = date('d/m/Y H:i');
    $reporte_id = uniqid('cert_');
    $verificacion_url = "http://localhost:8080/verificar_certificado.php?id=" . $reporte_id;
    
    // Generar QR con chillerlan
    $qrOptions = new QROptions([
        'version' => 5,
        'outputType' => QRCode::OUTPUT_IMAGE_PNG,
        'eccLevel' => QRCode::ECC_L,
        'scale' => 6,
    ]);
    $qrcode = new QRCode($qrOptions);
    $qr_png = $qrcode->render($verificacion_url);
    $qr_base64 = 'data:image/png;base64,' . base64_encode($qr_png);
    
    $html = "
    <!DOCTYPE html>
    <html>
    <head><meta charset='UTF-8'><title>Certificado</title>
    <style>
        @page { margin: 1.5cm; size: landscape; }
        body { font-family: 'DejaVu Sans', sans-serif; background: #f5f5f5; }
        .certificado { border: 2px solid #2c3e50; border-radius: 15px; padding: 20px; background: white; }
        .header { text-align: center; border-bottom: 2px solid #3498db; margin-bottom: 20px; }
        .header h1 { color: #2c3e50; font-size: 28px; }
        .titulo { background: #3498db; color: white; text-align: center; padding: 8px; border-radius: 8px; margin: 20px 0; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th { background: #ecf0f1; padding: 10px; text-align: left; }
        td { padding: 8px; border-bottom: 1px solid #ddd; }
        .footer { margin-top: 30px; display: flex; justify-content: space-between; border-top: 1px solid #ddd; padding-top: 15px; font-size: 10px; }
        .qr { text-align: right; }
        .firma { text-align: center; margin-top: 20px; font-style: italic; }
    </style>
    </head>
    <body>
    <div class='certificado'>
        <div class='header'><h1>Colegio San Miguel</h1><p>Educación de Excelencia</p></div>
        <div class='titulo'>CERTIFICADO DE REPORTE - " . ucfirst($tipo) . "</div>
        <p><strong>Emisión:</strong> $fecha_emision</p>
        <p><strong>Generado por:</strong> " . htmlspecialchars($usuario['nombre']) . " (".htmlspecialchars($usuario['rol']).")</p>
        <p><strong>Código de verificación:</strong> $reporte_id</p>
    ";
    
    if ($tipo == 'usuarios') {
        $data = $pdo->query("SELECT id, nombre, email, rol, created_at FROM usuarios ORDER BY rol, nombre")->fetchAll();
        $html .= "<table><thead><tr><th>ID</th><th>Nombre</th><th>Email</th><th>Rol</th><th>Registro</th></tr></thead><tbody>";
        foreach ($data as $row) {
            $html .= "<td>{$row['id']}</td><td>" . htmlspecialchars($row['nombre']) . "</td><td>" . htmlspecialchars($row['email']) . "</td><td>{$row['rol']}</td><td>{$row['created_at']}</td></tr>";
        }
        $html .= "</tbody></table>";
    } elseif ($tipo == 'cursos') {
        $data = $pdo->query("SELECT c.*, COUNT(e.user_id) as estudiantes FROM cursos c LEFT JOIN estudiantes e ON c.id = e.curso_id GROUP BY c.id")->fetchAll();
        $html .= "</table><thead><tr><th>ID</th><th>Curso</th><th>Año</th><th>Estudiantes</th></tr></thead><tbody>";
        foreach ($data as $row) {
            $html .= "<tr><td>{$row['id']}</td><td>" . htmlspecialchars($row['nombre']) . "</td><td>{$row['anio']}</td><td>{$row['estudiantes']}</td></tr>";
        }
        $html .= "</tbody></table>";
    } elseif ($tipo == 'tareas') {
        $data = $pdo->query("SELECT t.titulo, c.nombre as curso, a.nombre as asignatura, t.fecha_entrega, u.nombre as profesor FROM tareas t JOIN cursos c ON t.curso_id = c.id JOIN asignaturas a ON t.asignatura_id = a.id JOIN usuarios u ON t.creado_por = u.id ORDER BY t.fecha_entrega")->fetchAll();
        $html .= "<tr><thead><tr><th>Título</th><th>Curso</th><th>Asignatura</th><th>Entrega</th><th>Profesor</th></tr></thead><tbody>";
        foreach ($data as $row) {
            $html .= "<tr><td>" . htmlspecialchars($row['titulo']) . "</td><td>" . htmlspecialchars($row['curso']) . "</td><td>" . htmlspecialchars($row['asignatura']) . "</td><td>{$row['fecha_entrega']}</td><td>" . htmlspecialchars($row['profesor']) . "</td></tr>";
        }
        $html .= "</tbody></table>";
    }
    
    $html .= "
        <div class='footer'>
            <div>Documento generado electrónicamente</div>
            <div class='qr'><img src='$qr_base64' width='80' height='80'><br>Escanea para verificar</div>
        </div>
        <div class='firma'>_________________________________<br>" . htmlspecialchars($usuario['nombre']) . "<br>Director / Administrador</div>
    </div>
    </body>
    </html>
    ";
    
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
    $dompdf->stream("certificado_$tipo.pdf", array("Attachment" => 0));
    exit;
}

// Si no hay tipo, mostrar la interfaz
$titulo_pagina = 'Reportes';
include '../includes/header.php';
?>
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Generar Reporte con Certificado y QR</div>
            <div class="card-body">
                <a href="?tipo=usuarios" class="btn btn-primary" target="_blank">Certificado de Usuarios (PDF)</a>
                <a href="?tipo=cursos" class="btn btn-success" target="_blank">Certificado de Cursos (PDF)</a>
                <a href="?tipo=tareas" class="btn btn-info" target="_blank">Certificado de Tareas (PDF)</a>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>