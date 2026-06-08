<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
$titulo_pagina = 'Enviar mensaje';
include '../includes/header.php';

$user_id = $_SESSION['user_id'];
$para_id = $_GET['para'] ?? null;
$responder_a = $_GET['responder'] ?? null;
$asunto_default = '';
$mensaje_default = '';
if ($responder_a) {
    $original = obtenerMensaje($responder_a);
    if ($original && $original['to_user_id'] == $user_id) {
        $para_id = $original['from_user_id'];
        $asunto_default = 'Re: ' . $original['asunto'];
        $mensaje_default = "\n\n--- Mensaje original de " . $original['remitente_nombre'] . ":\n" . $original['mensaje'];
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $para = $_POST['para_id'];
    $asunto = $_POST['asunto'];
    $mensaje = $_POST['mensaje'];
    $parent = $_POST['parent_id'] ?? null;
    if (enviarMensaje($user_id, $para, $asunto, $mensaje, $parent)) {
        $_SESSION['mensaje'] = "Mensaje enviado";
        header("Location: mensajes.php");
        exit;
    } else $error = "Error al enviar";
}
?>
<div class="card"><div class="card-header">Nuevo mensaje</div><div class="card-body">
    <?php if(isset($error)): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
    <form method="POST">
        <div class="mb-3"><label>Para</label><input type="text" id="buscar_usuario" class="form-control" placeholder="Escriba nombre o email" autocomplete="off"><input type="hidden" name="para_id" id="para_id" required><div id="sugerencias" class="list-group mt-1" style="position:absolute; z-index:1000; display:none;"></div></div>
        <div class="mb-3"><label>Asunto</label><input type="text" name="asunto" class="form-control" value="<?= htmlspecialchars($asunto_default) ?>" required></div>
        <div class="mb-3"><label>Mensaje</label><textarea name="mensaje" class="form-control" rows="5" required><?= htmlspecialchars($mensaje_default) ?></textarea></div>
        <?php if ($responder_a): ?><input type="hidden" name="parent_id" value="<?= $responder_a ?>"><?php endif; ?>
        <button type="submit" class="btn btn-primary">Enviar</button> <a href="mensajes.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div></div>
<script>
const inputBuscar = document.getElementById('buscar_usuario');
const sugerenciasDiv = document.getElementById('sugerencias');
const paraIdHidden = document.getElementById('para_id');
inputBuscar.addEventListener('input', function() {
    let termino = this.value;
    if (termino.length < 2) { sugerenciasDiv.style.display = 'none'; return; }
    fetch('/ajax/buscar_usuarios.php?q=' + encodeURIComponent(termino))
        .then(res => res.json())
        .then(data => {
            sugerenciasDiv.innerHTML = '';
            if (data.length) {
                data.forEach(u => {
                    let item = document.createElement('a');
                    item.href = '#';
                    item.className = 'list-group-item list-group-item-action';
                    item.textContent = `${u.nombre} (${u.email}) - ${u.rol}`;
                    item.addEventListener('click', (e) => { e.preventDefault(); inputBuscar.value = u.nombre; paraIdHidden.value = u.id; sugerenciasDiv.style.display = 'none'; });
                    sugerenciasDiv.appendChild(item);
                });
                sugerenciasDiv.style.display = 'block';
            } else sugerenciasDiv.style.display = 'none';
        });
});
document.addEventListener('click', function(e) { if (!sugerenciasDiv.contains(e.target) && e.target !== inputBuscar) sugerenciasDiv.style.display = 'none'; });
</script>
<?php include '../includes/footer.php'; ?>