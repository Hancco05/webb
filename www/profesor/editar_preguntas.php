<?php
require_once '../includes/auth.php';
verificarSesion('profesor');
require_once '../includes/db.php';
$titulo_pagina = 'Editar Preguntas';
include '../includes/header.php';

$cuestionario_id = $_GET['id'] ?? 0;
$cuestionario = obtenerCuestionario($cuestionario_id);
if (!$cuestionario || $cuestionario['profesor_id'] != $_SESSION['user_id']) {
    die("No autorizado o cuestionario no existe");
}

// Procesar nueva pregunta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nueva_pregunta'])) {
    if (!verificarTokenCSRF($_POST['csrf_token'] ?? '')) die("CSRF inválido");
    crearPregunta($cuestionario_id, $_POST['enunciado'], $_POST['tipo'], $_POST['puntos']);
    $_SESSION['mensaje'] = "Pregunta agregada";
    header("Location: editar_preguntas.php?id=$cuestionario_id");
    exit;
}

// Eliminar pregunta
if (isset($_GET['eliminar_pregunta'])) {
    eliminarPregunta((int)$_GET['eliminar_pregunta']);
    $_SESSION['mensaje'] = "Pregunta eliminada";
    header("Location: editar_preguntas.php?id=$cuestionario_id");
    exit;
}

// Guardar opciones (después de enviar el formulario de opciones)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_opciones'])) {
    if (!verificarTokenCSRF($_POST['csrf_token'] ?? '')) die("CSRF inválido");
    foreach ($_POST['opciones'] as $pregunta_id => $opciones) {
        // Eliminar opciones existentes de esa pregunta
        $pdo->prepare("DELETE FROM opciones WHERE pregunta_id = ?")->execute([$pregunta_id]);
        foreach ($opciones as $opcion) {
            if (trim($opcion['texto']) != '') {
                $es_correcta = isset($opcion['correcta']) ? 1 : 0;
                crearOpcion($pregunta_id, $opcion['texto'], $es_correcta);
            }
        }
    }
    $_SESSION['mensaje'] = "Opciones guardadas";
    header("Location: editar_preguntas.php?id=$cuestionario_id");
    exit;
}

$preguntas = obtenerPreguntas($cuestionario_id);
?>
<div class="card">
    <div class="card-header">Preguntas - <?= htmlspecialchars($cuestionario['titulo']) ?></div>
    <div class="card-body">
        <?php if (isset($_SESSION['mensaje'])): ?><div class="alert alert-success"><?= $_SESSION['mensaje']; unset($_SESSION['mensaje']); ?></div><?php endif; ?>
        
        <!-- Formulario para agregar pregunta -->
        <form method="POST" class="border p-3 mb-4">
            <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
            <h5>Agregar nueva pregunta</h5>
            <div class="mb-2"><label>Enunciado</label><input type="text" name="enunciado" class="form-control" required></div>
            <div class="row">
                <div class="col-md-6"><label>Tipo</label><select name="tipo" class="form-select"><option value="multiple">Opción múltiple</option><option value="verdadero_falso">Verdadero/Falso</option></select></div>
                <div class="col-md-6"><label>Puntos</label><input type="number" name="puntos" class="form-control" step="0.5" value="1"></div>
            </div>
            <button type="submit" name="nueva_pregunta" class="btn btn-primary mt-2">Agregar pregunta</button>
        </form>

        <!-- Formulario para opciones de cada pregunta -->
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
            <?php foreach ($preguntas as $p): ?>
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between">
                    <strong><?= htmlspecialchars($p['enunciado']) ?> (<?= $p['puntos'] ?> ptos, <?= $p['tipo'] == 'multiple' ? 'Múltiple' : 'V/F' ?>)</strong>
                    <a href="?id=<?= $cuestionario_id ?>&eliminar_pregunta=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar pregunta?')">Eliminar</a>
                </div>
                <div class="card-body">
                    <?php
                    $opciones = obtenerOpciones($p['id']);
                    $num_opciones = ($p['tipo'] == 'multiple') ? 4 : 2;
                    ?>
                    <div id="opciones-<?= $p['id'] ?>">
                        <?php for ($i=0; $i<$num_opciones; $i++):
                            $texto = isset($opciones[$i]) ? $opciones[$i]['texto'] : '';
                            $es_correcta = isset($opciones[$i]) && $opciones[$i]['es_correcta'];
                        ?>
                        <div class="row mb-2">
                            <div class="col-md-8"><input type="text" name="opciones[<?= $p['id'] ?>][<?= $i ?>][texto]" class="form-control" value="<?= htmlspecialchars($texto) ?>" placeholder="Opción <?= $i+1 ?>"></div>
                            <div class="col-md-4"><input type="checkbox" name="opciones[<?= $p['id'] ?>][<?= $i ?>][correcta]" value="1" <?= $es_correcta ? 'checked' : '' ?>> Correcta</div>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <button type="submit" name="guardar_opciones" class="btn btn-success">Guardar todas las opciones</button>
        </form>
    </div>
</div>
<?php include '../includes/footer.php'; ?>