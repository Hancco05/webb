<?php
require_once '../includes/auth.php';
verificarSesion('estudiante');
require_once '../includes/db.php';
$titulo_pagina = 'Responder Cuestionario';
include '../includes/header.php';

$cuestionario_id = $_GET['id'] ?? 0;
$cuestionario = obtenerCuestionario($cuestionario_id);
if (!$cuestionario) die("Cuestionario no existe");
$user_id = $_SESSION['user_id'];

$calif = obtenerCalificacionEstudiante($cuestionario_id, $user_id);
if ($calif && $calif['intentos'] >= $cuestionario['intentos_permitidos']) {
    echo "<div class='alert alert-danger'>Has agotado tus intentos.</div>";
    include '../includes/footer.php';
    exit;
}
if (strtotime($cuestionario['fecha_inicio']) > time()) {
    echo "<div class='alert alert-warning'>Aún no disponible.</div>";
    include '../includes/footer.php';
    exit;
}
if (strtotime($cuestionario['fecha_fin']) < time()) {
    echo "<div class='alert alert-danger'>Vencido.</div>";
    include '../includes/footer.php';
    exit;
}

$preguntas = obtenerPreguntas($cuestionario_id);
$preguntas_con_opciones = [];
foreach ($preguntas as $p) {
    $preguntas_con_opciones[] = ['pregunta' => $p, 'opciones' => obtenerOpciones($p['id'])];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $respuestas = $_POST['respuestas'] ?? [];
    foreach ($respuestas as $pregunta_id => $opcion_id) {
        $pregunta = obtenerPregunta($pregunta_id);
        $es_correcta = false;
        if ($pregunta['tipo'] == 'multiple' || $pregunta['tipo'] == 'verdadero_falso') {
            $stmt = $pdo->prepare("SELECT es_correcta FROM opciones WHERE id = ?");
            $stmt->execute([$opcion_id]);
            $es_correcta = (bool)$stmt->fetchColumn();
        }
        guardarRespuesta($cuestionario_id, $pregunta_id, $user_id, $opcion_id, null, $es_correcta);
    }
    $resultado = calcularCalificacion($cuestionario_id, $user_id);
    $_SESSION['mensaje'] = "Has obtenido {$resultado['obtenido']}/{$resultado['total']} puntos.";
    header("Location: cuestionarios.php");
    exit;
}
?>
<div class="card">
    <div class="card-header"><?= htmlspecialchars($cuestionario['titulo']) ?></div>
    <div class="card-body">
        <p><?= nl2br(htmlspecialchars($cuestionario['descripcion'])) ?></p>
        <form method="POST">
            <?php foreach($preguntas_con_opciones as $idx => $item):
                $p = $item['pregunta'];
                $opciones = $item['opciones'];
            ?>
            <div class="mb-4 border p-2">
                <strong><?= ($idx+1) . '. ' . htmlspecialchars($p['enunciado']) ?></strong> (<?= $p['puntos'] ?> ptos)<br>
                <?php if ($p['tipo'] == 'multiple'): ?>
                    <?php foreach($opciones as $op): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="respuestas[<?= $p['id'] ?>]" value="<?= $op['id'] ?>" required>
                        <label class="form-check-label"><?= htmlspecialchars($op['texto']) ?></label>
                    </div>
                    <?php endforeach; ?>
                <?php elseif ($p['tipo'] == 'verdadero_falso' && count($opciones) >= 2): ?>
                    <div class="form-check"><input type="radio" name="respuestas[<?= $p['id'] ?>]" value="<?= $opciones[0]['id'] ?>" required> Verdadero</div>
                    <div class="form-check"><input type="radio" name="respuestas[<?= $p['id'] ?>]" value="<?= $opciones[1]['id'] ?>"> Falso</div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <button type="submit" class="btn btn-primary">Enviar respuestas</button>
        </form>
    </div>
</div>
<?php include '../includes/footer.php'; ?>