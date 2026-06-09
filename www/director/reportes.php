<?php
require_once '../includes/auth.php';
verificarSesion('director');
require_once '../includes/db.php';
require_once '../vendor/autoload.php';

use Dompdf\Dompdf;

if (isset($_GET['tipo'])) {
    $tipo = $_GET['tipo'];
    
    $dompdf = new Dompdf();
    
    // Datos del usuario que genera el reporte
    $usuario = obtenerDatosUsuario($_SESSION['user_id']);
    $fecha_emision = date('d/m/Y H:i');
    $numero_certificado = strtoupper(uniqid('CERT-'));
    
    // Construir HTML con estilo de certificado (sin QR)
    $html = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Certificado Oficial - Sistema Educativo</title>
        <style>
            @page {
                margin: 2cm;
                size: landscape;
            }
            body {
                font-family: 'DejaVu Sans', 'Segoe UI', sans-serif;
                background: #f0f2f5;
                padding: 0;
                margin: 0;
            }
            .certificado {
                border: 3px double #2c3e50;
                border-radius: 20px;
                padding: 30px;
                background: white;
                box-shadow: 0 10px 25px rgba(0,0,0,0.1);
                position: relative;
            }
            .header {
                text-align: center;
                border-bottom: 2px solid #3498db;
                margin-bottom: 25px;
                padding-bottom: 15px;
            }
            .header h1 {
                color: #2c3e50;
                font-size: 32px;
                margin: 0;
                letter-spacing: 2px;
            }
            .header p {
                color: #7f8c8d;
                font-size: 14px;
                margin: 5px 0 0;
            }
            .sello {
                position: absolute;
                top: 50px;
                right: 50px;
                width: 100px;
                height: 100px;
                border: 2px solid #c0392b;
                border-radius: 50%;
                text-align: center;
                line-height: 100px;
                font-size: 12px;
                font-weight: bold;
                color: #c0392b;
                transform: rotate(-15deg);
                opacity: 0.7;
            }
            .titulo {
                background: #3498db;
                color: white;
                text-align: center;
                padding: 10px;
                border-radius: 30px;
                margin: 25px 0;
                font-size: 22px;
                font-weight: bold;
                letter-spacing: 1px;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
                font-size: 13px;
            }
            th {
                background: #ecf0f1;
                padding: 10px;
                text-align: left;
                border-bottom: 2px solid #3498db;
            }
            td {
                padding: 8px;
                border-bottom: 1px solid #ddd;
            }
            .info {
                margin: 20px 0;
                font-size: 13px;
            }
            .info p {
                margin: 5px 0;
            }
            .footer {
                margin-top: 40px;
                display: flex;
                justify-content: space-between;
                align-items: flex-end;
                border-top: 1px solid #ddd;
                padding-top: 20px;
                font-size: 11px;
                color: #7f8c8d;
            }
            .firma {
                text-align: center;
                width: 250px;
            }
            .firma-linea {
                border-top: 1px solid #2c3e50;
                margin-top: 20px;
                padding-top: 5px;
                font-size: 12px;
                font-style: italic;
            }
            .firma-texto {
                font-size: 12px;
                color: #555;
            }
        </style>
    </head>
    <body>
        <div class='certificado'>
            <div class='sello'>CERTIFICADO<br>OFICIAL</div>
            <div class='header'>
                <h1>MI COLEGIO</h1>
                <p>Educación de Excelencia</p>
            </div>
            <div class='titulo'>CERTIFICADO DE REPORTE - " . strtoupper($tipo) . "</div>
            <div class='info'>
                <p><strong>Número de Certificado:</strong> $numero_certificado</p>
                <p><strong>Emisión:</strong> $fecha_emision</p>
                <p><strong>Generado por:</strong> " . htmlspecialchars($usuario['nombre']) . " (" . ucfirst(htmlspecialchars($usuario['rol'])) . ")</p>
            </div>
    ";
    
    // Contenido de la tabla según el tipo
    if ($tipo == 'usuarios') {
        $data = $pdo->query("SELECT id, nombre, email, rol, created_at FROM usuarios ORDER BY rol, nombre")->fetchAll();
        $html .= "<table>
                    <thead>
                        <tr><th>ID</th><th>Nombre completo</th><th>Correo electrónico</th><th>Rol</th><th>Fecha registro</th></tr>
                    </thead>
                    <tbody>";
        foreach ($data as $row) {
            $html .= "<tr>
                        <td>" . $row['id'] . "</td>
                        <td>" . htmlspecialchars($row['nombre']) . "</td>
                        <td>" . htmlspecialchars($row['email']) . "</td>
                        <td>" . htmlspecialchars($row['rol']) . "</td>
                        <td>" . $row['created_at'] . "</td>
                      </tr>";
        }
        $html .= "</tbody></table>";
    } elseif ($tipo == 'cursos') {
        $data = $pdo->query("SELECT c.id, c.nombre, c.descripcion, c.anio, COUNT(e.user_id) as estudiantes FROM cursos c LEFT JOIN estudiantes e ON c.id = e.curso_id GROUP BY c.id")->fetchAll();
        $html .= "<table>
                    <thead>
                        <tr><th>ID</th><th>Curso</th><th>Descripción</th><th>Año</th><th>Estudiantes</th></tr>
                    </thead>
                    <tbody>";
        foreach ($data as $row) {
            $html .= "<tr>
                        <td>" . $row['id'] . "</td>
                        <td>" . htmlspecialchars($row['nombre']) . "</td>
                        <td>" . htmlspecialchars($row['descripcion']) . "</td>
                        <td>" . $row['anio'] . "</td>
                        <td>" . $row['estudiantes'] . "</td>
                      </tr>";
        }
        $html .= "</tbody></table>";
    } elseif ($tipo == 'tareas') {
        $data = $pdo->query("SELECT t.titulo, c.nombre as curso, a.nombre as asignatura, t.fecha_entrega, u.nombre as profesor FROM tareas t JOIN cursos c ON t.curso_id = c.id JOIN asignaturas a ON t.asignatura_id = a.id JOIN usuarios u ON t.creado_por = u.id ORDER BY t.fecha_entrega")->fetchAll();
        $html .= "<table>
                    <thead>
                        <tr><th>Título</th><th>Curso</th><th>Asignatura</th><th>Fecha entrega</th><th>Profesor</th></tr>
                    </thead>
                    <tbody>";
        foreach ($data as $row) {
            $html .= "<tr>
                        <td>" . htmlspecialchars($row['titulo']) . "</td>
                        <td>" . htmlspecialchars($row['curso']) . "</td>
                        <td>" . htmlspecialchars($row['asignatura']) . "</td>
                        <td>" . $row['fecha_entrega'] . "</td>
                        <td>" . htmlspecialchars($row['profesor']) . "</td>
                      </tr>";
        }
        $html .= "</tbody></table>";
    }
    
    $html .= "
            <div class='footer'>
                <div class='firma'>
                    <div class='firma-linea'></div>
                    <div class='firma-texto'>" . htmlspecialchars($usuario['nombre']) . "<br>Director / Administrador</div>
                </div>
                <div class='firma'>
                    <div class='firma-linea'></div>
                    <div class='firma-texto'>Sello del Colegio<br>Autenticidad garantizada</div>
                </div>
            </div>
            <div style='text-align: center; margin-top: 15px; font-size: 9px; color: #aaa;'>
                Documento válido sin firma manuscrita. Verificar en sistema interno.
            </div>
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

// Si no se pasa tipo, mostrar el formulario
$titulo_pagina = 'Reportes';
include '../includes/header.php';
?>
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Generar Certificado (PDF)</div>
            <div class="card-body">
                <a href="?tipo=usuarios" class="btn btn-primary" target="_blank">Certificado de Usuarios</a>
                <a href="?tipo=cursos" class="btn btn-success" target="_blank">Certificado de Cursos</a>
                <a href="?tipo=tareas" class="btn btn-info" target="_blank">Certificado de Tareas</a>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>