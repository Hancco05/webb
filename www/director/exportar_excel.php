<?php
require_once '../includes/auth.php';
verificarSesion('director');
require_once '../includes/db.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$tipo = $_GET['tipo'] ?? 'usuarios';

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

if ($tipo == 'usuarios') {
    $data = $pdo->query("SELECT id, nombre, email, rol, created_at FROM usuarios ORDER BY rol, nombre")->fetchAll();
    $sheet->setCellValue('A1', 'ID');
    $sheet->setCellValue('B1', 'Nombre');
    $sheet->setCellValue('C1', 'Email');
    $sheet->setCellValue('D1', 'Rol');
    $sheet->setCellValue('E1', 'Fecha registro');
    $row = 2;
    foreach ($data as $d) {
        $sheet->setCellValue('A'.$row, $d['id']);
        $sheet->setCellValue('B'.$row, $d['nombre']);
        $sheet->setCellValue('C'.$row, $d['email']);
        $sheet->setCellValue('D'.$row, $d['rol']);
        $sheet->setCellValue('E'.$row, $d['created_at']);
        $row++;
    }
    $filename = 'usuarios.xlsx';
} elseif ($tipo == 'cursos') {
    $data = $pdo->query("SELECT c.*, COUNT(e.user_id) as estudiantes FROM cursos c LEFT JOIN estudiantes e ON c.id = e.curso_id GROUP BY c.id")->fetchAll();
    $sheet->setCellValue('A1', 'ID');
    $sheet->setCellValue('B1', 'Nombre');
    $sheet->setCellValue('C1', 'Descripción');
    $sheet->setCellValue('D1', 'Año');
    $sheet->setCellValue('E1', 'Estudiantes');
    $row = 2;
    foreach ($data as $d) {
        $sheet->setCellValue('A'.$row, $d['id']);
        $sheet->setCellValue('B'.$row, $d['nombre']);
        $sheet->setCellValue('C'.$row, $d['descripcion']);
        $sheet->setCellValue('D'.$row, $d['anio']);
        $sheet->setCellValue('E'.$row, $d['estudiantes']);
        $row++;
    }
    $filename = 'cursos.xlsx';
} else {
    die('Tipo no válido');
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="'.$filename.'"');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;