<?php
require_once '../includes/auth.php';
verificarSesion('director');
require_once '../includes/db.php';
$titulo_pagina = 'Noticias';
include '../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verificarTokenCSRF($_POST['csrf_token'] ?? '')) { $error = "Token inválido"; }
    else {
        $action = $_POST['action'];
        $id = $_POST['id'] ?? 0;
        $titulo = trim($_POST['titulo']);
        $contenido = trim($_POST['contenido']);
        $rol_destino = $_POST['rol_destino'];
        if ($action === 'crear') {
            $stmt = $pdo->prepare("INSERT INTO noticias (titulo, contenido, creado_por, rol_destino) VALUES (?,?,?,?)");
            if ($stmt->execute([$titulo, $contenido, $_SESSION['user_id'], $rol_destino])) {
                $_SESSION['mensaje'] = "Noticia creada";
                header("Location: /director/noticias.php");
                exit;
            }
        } elseif ($action === 'eliminar') {
            $stmt = $pdo->prepare("DELETE FROM noticias WHERE id=?");
            if ($stmt->execute([$id])) {
                $_SESSION['mensaje'] = "Noticia eliminada";
                header("Location: /director/noticias.php");
                exit;
            }
        }
    }
}
$noticias = $pdo->query("SELECT n.*, u.nombre as autor FROM noticias n JOIN usuarios u ON n.creado_por = u.id ORDER BY fecha_publicacion DESC")->fetchAll();
?>
<div class="card">
    <div class="card-header d-flex justify-content-between"><span>Noticias</span><button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalNoticia">+ Nueva</button></div>
    <div class="card-body">
        <?php if(isset($_SESSION['mensaje'])): ?><div class="alert alert-success"><?= $_SESSION['mensaje']; unset($_SESSION['mensaje']); ?></div><?php endif; ?>
        <?php foreach($noticias as $n): ?>
        <div class="card mb-3"><div class="card-header"><strong><?= htmlspecialchars($n['titulo']) ?></strong> <span class="badge bg-secondary"><?= $n['rol_destino'] ?></span> <span class="float-end"><?= $n['fecha_publicacion'] ?> por <?= htmlspecialchars($n['autor']) ?></span></div><div class="card-body"><?= nl2br(htmlspecialchars($n['contenido'])) ?></div>
        <div class="card-footer"><form method="POST" onsubmit="return confirm('¿Eliminar?')"><input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>"><input type="hidden" name="action" value="eliminar"><input type="hidden" name="id" value="<?= $n['id'] ?>"><button class="btn btn-danger btn-sm">Eliminar</button></form></div></div>
        <?php endforeach; ?>
    </div>
</div>
<div class="modal fade" id="modalNoticia" tabindex="-1"><div class="modal-dialog"><form method="POST" class="modal-content"><div class="modal-header"><h5>Nueva Noticia</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>"><input type="hidden" name="action" value="crear"><div class="mb-2"><label>Título</label><input type="text" name="titulo" class="form-control" required></div><div class="mb-2"><label>Contenido</label><textarea name="contenido" class="form-control" rows="5" required></textarea></div><div class="mb-2"><label>Destinado a</label><select name="rol_destino" class="form-select"><option value="todos">Todos</option><option value="director">Director</option><option value="profesor">Profesor</option><option value="auxiliar">Auxiliar</option><option value="estudiante">Estudiante</option><option value="apoderado">Apoderado</option></select></div></div>
    <div class="modal-footer"><button type="submit" class="btn btn-primary">Publicar</button></div>
</form></div></div>
<?php include '../includes/footer.php'; ?>