<?php
require_once '../includes/auth.php';
verificarSesion('apoderado');
require_once '../includes/db.php';
$titulo_pagina = 'Noticias';
include '../includes/header.php';

$stmt = $pdo->prepare("SELECT * FROM noticias WHERE rol_destino IN ('todos', 'apoderado') ORDER BY fecha_publicacion DESC");
$stmt->execute();
$noticias = $stmt->fetchAll();
?>
<div class="card">
    <div class="card-header">Noticias</div>
    <div class="card-body">
        <?php foreach($noticias as $n): ?>
            <div class="card mb-3">
                <div class="card-header"><strong><?= htmlspecialchars($n['titulo']) ?></strong> <span class="float-end"><?= $n['fecha_publicacion'] ?></span></div>
                <div class="card-body"><?= nl2br(htmlspecialchars($n['contenido'])) ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php include '../includes/footer.php'; ?>