<?php
require_once '../includes/auth.php';
verificarSesion('profesor');
require_once '../includes/db.php';
require_once '../vendor/autoload.php';
use Dompdf\Dompdf;

$titulo_pagina = 'Reportes Profesor';
include '../includes/header.php';

if (isset($_GET['tipo']) && isset($_GET['curso_id'])) {
    $tipo = $_GET['tipo'];
    $curso_id = $_GET['curso_id'];
    $dompdf = new Dompdf();
    ob_start();
    ?>
    <html>
    <head><title>Reporte</title><style>body{font-family: sans-serif;} table{width:100%; border-collapse:collapse;} th,td{border:1px solid #ddd; padding:8px;}</style></head>
    <body>
    <?php
    if ($tipo == 'notas') {
        $asignatura_id = $_GET['asignatura_id'] ?? 0;
        $periodo = $_GET['periodo'] ?? '1er Bimestre';
        $estudiantes = obtenerEstudiantesPorCurso($curso_id);
        $asignatura = $pdo->prepare("SELECT * FROM asignaturas WHERE id=?");
        $asignatura->execute([$asignatura_id]);
        $asignatura = $asignatura->fetch();
        echo "<h2>Reporte de Notas - {$asignatura['nombre']} - $periodo</h2><table><tr><th>Estudiante</th><th>Nota</th></tr>";
        foreach($estudiantes as $e) {
            $stmt = $pdo->prepare("SELECT nota FROM notas WHERE estudiante_id=? AND asignatura_id=? AND periodo=?");
            $stmt->execute([$e['id'], $asignatura_id, $periodo]);
            $nota = $stmt->fetchColumn();
            echo "<tr><td>{$e['nombre']}</td><td>".($nota!==false?$nota:'-')."</td></tr>";
        }
        echo "</table>";
    } elseif ($tipo == 'asistencia') {
        $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-t');
        $estudiantes = obtenerEstudiantesPorCurso($curso_id);
        echo "<h2>Reporte de Asistencia del $fecha_inicio al $fecha_fin</h2><table><tr><th>Estudiante</th><th>Presente</th><th>Ausente</th><th>Tarde</th></tr>";
        foreach($estudiantes as $e) {
            $stmt = $pdo->prepare("SELECT estado, COUNT(*) FROM asistencia WHERE estudiante_id=? AND fecha BETWEEN ? AND ? GROUP BY estado");
            $stmt->execute([$e['id'], $fecha_inicio, $fecha_fin]);
            $stats = ['presente'=>0, 'ausente'=>0, 'tarde'=>0];
            while($row = $stmt->fetch()) {
                $stats[$row['estado']] = $row['COUNT(*)'];
            }
            echo "<tr><td>{$e['nombre']}</td><td>{$stats['presente']}</td><td>{$stats['ausente']}</td><td>{$stats['tarde']}</td></tr>";
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

$cursos = obtenerCursosPorProfesor($_SESSION['user_id']);
?>
<div class="card">
    <div class="card-header">Generar Reporte</div>
    <div class="card-body">
        <form method="GET" target="_blank">
            <div class="mb-3">
                <label>Curso</label>
                <select name="curso_id" class="form-select" required>
                    <option value="">Seleccione</option>
                    <?php foreach($cursos as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label>Tipo de reporte</label>
                <select name="tipo" class="form-select" required>
                    <option value="notas">Notas</option>
                    <option value="asistencia">Asistencia</option>
                </select>
            </div>
            <div id="notas-fields" style="display:none;">
                <div class="mb-3">
                    <label>Asignatura</label>
                    <select name="asignatura_id" class="form-select"></select>
                </div>
                <div class="mb-3">
                    <label>Período</label>
                    <select name="periodo" class="form-select">
                        <option>1er Bimestre</option>
                        <option>2do Bimestre</option>
                        <option>3er Bimestre</option>
                        <option>4to Bimestre</option>
                    </select>
                </div>
            </div>
            <div id="asistencia-fields" style="display:none;">
                <div class="mb-3">
                    <label>Fecha inicio</label>
                    <input type="date" name="fecha_inicio" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Fecha fin</label>
                    <input type="date" name="fecha_fin" class="form-control">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Generar PDF</button>
        </form>
    </div>
</div>
<script>
document.querySelector('select[name="tipo"]').addEventListener('change', function() {
    var tipo = this.value;
    document.getElementById('notas-fields').style.display = tipo === 'notas' ? 'block' : 'none';
    document.getElementById('asistencia-fields').style.display = tipo === 'asistencia' ? 'block' : 'none';
    if(tipo === 'notas') {
        var curso_id = document.querySelector('select[name="curso_id"]').value;
        if(curso_id) {
            fetch('../ajax/asignaturas_por_curso.php?curso_id='+curso_id)
                .then(res=>res.json())
                .then(data=>{
                    var select = document.querySelector('select[name="asignatura_id"]');
                    select.innerHTML = '';
                    data.forEach(a=>{
                        select.innerHTML += `<option value="${a.id}">${a.nombre}</option>`;
                    });
                });
        }
    }
});
document.querySelector('select[name="curso_id"]').addEventListener('change', function() {
    if(document.querySelector('select[name="tipo"]').value === 'notas') {
        var curso_id = this.value;
        fetch('../ajax/asignaturas_por_curso.php?curso_id='+curso_id)
            .then(res=>res.json())
            .then(data=>{
                var select = document.querySelector('select[name="asignatura_id"]');
                select.innerHTML = '';
                data.forEach(a=>{
                    select.innerHTML += `<option value="${a.id}">${a.nombre}</option>`;
                });
            });
    }
});
</script>
<?php include '../includes/footer.php'; ?>