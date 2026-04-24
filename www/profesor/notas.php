<?php
require_once '../includes/auth.php';
verificarSesion('profesor');
require_once '../includes/db.php';
$titulo_pagina = 'Gestión de Notas';
include '../includes/header.php';

$asignatura_id = $_GET['asignatura_id'] ?? 0;
$curso_id = $_GET['curso_id'] ?? 0;
$periodo = $_GET['periodo'] ?? '1er Bimestre';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notas'])) {
    foreach($_POST['notas'] as $estudiante_id => $nota) {
        // Verificar si ya existe nota para ese periodo
        $stmt = $pdo->prepare("SELECT id FROM notas WHERE estudiante_id=? AND asignatura_id=? AND periodo=?");
        $stmt->execute([$estudiante_id, $asignatura_id, $periodo]);
        if($stmt->fetch()) {
            $stmt = $pdo->prepare("UPDATE notas SET nota=?, registrado_por=? WHERE estudiante_id=? AND asignatura_id=? AND periodo=?");
            $stmt->execute([$nota, $_SESSION['user_id'], $estudiante_id, $asignatura_id, $periodo]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO notas (estudiante_id, asignatura_id, periodo, nota, registrado_por) VALUES (?,?,?,?,?)");
            $stmt->execute([$estudiante_id, $asignatura_id, $periodo, $nota, $_SESSION['user_id']]);
        }
    }
    $_SESSION['mensaje'] = "Notas guardadas";
    header("Location: notas.php?asignatura_id=$asignatura_id&curso_id=$curso_id&periodo=$periodo");
    exit;
}

$estudiantes = obtenerEstudiantesPorCurso($curso_id);
$asignatura = $pdo->prepare("SELECT * FROM asignaturas WHERE id=?");
$asignatura->execute([$asignatura_id]);
$asignatura = $asignatura->fetch();

// Obtener notas existentes
$notas_existentes = [];
$stmt = $pdo->prepare("SELECT estudiante_id, nota FROM notas WHERE asignatura_id=? AND periodo=?");
$stmt->execute([$asignatura_id, $periodo]);
while($row = $stmt->fetch()) {
    $notas_existentes[$row['estudiante_id']] = $row['nota'];
}
?>
<div class="card">
    <div class="card-header">
        Notas - <?= htmlspecialchars($asignatura['nombre']) ?> - <?= $periodo ?>
        <div class="float-end">
            <select id="periodoSelect" class="form-select form-select-sm w-auto d-inline-block" onchange="cambiarPeriodo()">
                <option value="1er Bimestre" <?= $periodo=='1er Bimestre'?'selected':'' ?>>1er Bimestre</option>
                <option value="2do Bimestre" <?= $periodo=='2do Bimestre'?'selected':'' ?>>2do Bimestre</option>
                <option value="3er Bimestre" <?= $periodo=='3er Bimestre'?'selected':'' ?>>3er Bimestre</option>
                <option value="4to Bimestre" <?= $periodo=='4to Bimestre'?'selected':'' ?>>4to Bimestre</option>
            </select>
        </div>
    </div>
    <div class="card-body">
        <?php if(isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-success"><?= $_SESSION['mensaje']; unset($_SESSION['mensaje']); ?></div>
        <?php endif; ?>
        <form method="POST">
            <table class="table table-bordered">
                <thead>
                    <tr><th>Estudiante</th><th>Nota</th></tr>
                </thead>
                <tbody>
                    <?php foreach($estudiantes as $e): ?>
                    <tr>
                        <td><?= htmlspecialchars($e['nombre']) ?></td>
                        <td>
                            <input type="number" step="0.01" min="0" max="7" name="notas[<?= $e['id'] ?>]" 
                                   class="form-control" value="<?= $notas_existentes[$e['id']] ?? '' ?>" required>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="submit" class="btn btn-primary">Guardar Notas</button>
        </form>
    </div>
</div>
<script>
function cambiarPeriodo() {
    var periodo = document.getElementById('periodoSelect').value;
    window.location.href = '?asignatura_id=<?= $asignatura_id ?>&curso_id=<?= $curso_id ?>&periodo=' + periodo;
}
</script>
<?php include '../includes/footer.php'; ?>