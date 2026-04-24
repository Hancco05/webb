<?php
require_once '../includes/auth.php';
verificarSesion('director');
require_once '../includes/db.php';
$titulo_pagina = 'Gestión de Noticias';
include '../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'crear') {
        $titulo = $_POST['titulo'];
        $contenido = $_POST['contenido'];
        $rol_destino = $_POST['rol_destino'];
        $creado_por = $_SESSION['user_id'];
        $stmt = $pdo->prepare("INSERT INTO noticias (titulo, contenido, creado_por, rol_destino) VALUES (?,?,?,?)");
        $stmt->execute([$titulo, $contenido, $creado_por, $rol_destino]);
        $_SESSION['mensaje'] = "Noticia publicada";
    } elseif ($action === 'eliminar') {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM noticias WHERE id=?");
        $stmt->execute([$id]);
        $_SESSION['mensaje'] = "Noticia eliminada";
    }
    header("Location: noticias.php");
    exit;
}

$noticias = $pdo->query("SELECT n.*, u.nombre as autor FROM noticias n JOIN usuarios u ON n.creado_por = u.id ORDER BY fecha_publicacion DESC")->fetchAll();
?>
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <span>Noticias</span>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalNoticia">+ Nueva Noticia</button>
    </div>
    <div class="card-body">
        <?php if(isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-success"><?= $_SESSION['mensaje']; unset($_SESSION['mensaje']); ?></div>
        <?php endif; ?>
        <?php foreach($noticias as $n): ?>
            <div class="card mb-3">
                <div class="card-header">
                    <strong><?= htmlspecialchars($n['titulo']) ?></strong> 
                    <span class="badge bg-secondary"><?= $n['rol_destino'] ?></span>
                    <span class="float-end"><?= $n['fecha_publicacion'] ?> por <?= htmlspecialchars($n['autor']) ?></span>
                </div>
                <div class="card-body">
                    <p><?= nl2br(htmlspecialchars($n['contenido'])) ?></p>
                    <form method="POST" onsubmit="return confirm('¿Eliminar?')">
                        <input type="hidden" name="action" value="eliminar">
                        <input type="hidden" name="id" value="<?= $n['id'] ?>">
                        <button class="btn btn-danger btn-sm">Eliminar</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalNoticia" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva Noticia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" value="crear">
                <div class="mb-2">
                    <label>Título</label>
                    <input type="text" name="titulo" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label>Contenido</label>
                    <textarea name="contenido" class="form-control" rows="5" required></textarea>
                </div>
                <div class="mb-2">
                    <label>Destinado a</label>
                    <select name="rol_destino" class="form-select">
                        <option value="todos">Todos</option>
                        <option value="director">Director</option>
                        <option value="profesor">Profesor</option>
                        <option value="auxiliar">Auxiliar</option>
                        <option value="estudiante">Estudiante</option>
                        <option value="apoderado">Apoderado</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Publicar</button>
            </div>
        </form>
    </div>
</div>
<?php include '../includes/footer.php'; ?>