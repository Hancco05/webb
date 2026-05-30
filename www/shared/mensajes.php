<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
$titulo_pagina = 'Mensajes';
include '../includes/header.php';

$user_id = $_SESSION['user_id'];
$tab = $_GET['tab'] ?? 'inbox';
$pagina = $_GET['pagina'] ?? 1;
$limite = 15;
$offset = ($pagina - 1) * $limite;

if ($tab == 'inbox') {
    $mensajes = obtenerMensajesRecibidos($user_id, $limite, $offset);
    $total = $pdo->prepare("SELECT COUNT(*) FROM mensajes WHERE to_user_id = ?");
    $total->execute([$user_id]);
    $total = $total->fetchColumn();
} else {
    $mensajes = obtenerMensajesEnviados($user_id, $limite, $offset);
    $total = $pdo->prepare("SELECT COUNT(*) FROM mensajes WHERE from_user_id = ?");
    $total->execute([$user_id]);
    $total = $total->fetchColumn();
}
$totalPaginas = ceil($total / $limite);
?>
<div class="row">
    <div class="col-md-3">
        <div class="card">
            <div class="card-header">Opciones</div>
            <div class="card-body">
                <a href="enviar_mensaje.php" class="btn btn-primary w-100 mb-2">+ Nuevo mensaje</a>
                <a href="mensajes.php?tab=inbox" class="btn btn-outline-secondary w-100 mb-2">Recibidos <?php $noLeidos = contarMensajesNoLeidos($user_id); if($noLeidos > 0) echo "<span class='badge bg-danger'>$noLeidos</span>"; ?></a>
                <a href="mensajes.php?tab=sent" class="btn btn-outline-secondary w-100">Enviados</a>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="card">
            <div class="card-header"><?= $tab == 'inbox' ? 'Bandeja de entrada' : 'Mensajes enviados' ?></div>
            <div class="card-body">
                <?php if (empty($mensajes)): ?>
                    <p>No hay mensajes.</p>
                <?php else: ?>
                    <table class="table table-hover">
                        <thead>
                            <tr><th>De/Para</th><th>Asunto</th><th>Fecha</th><th>Estado</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($mensajes as $m): ?>
                            <tr>
                                <td>
                                    <?php if ($tab == 'inbox'): ?>
                                        <?= htmlspecialchars($m['remitente_nombre']) ?>
                                    <?php else: ?>
                                        <?= htmlspecialchars($m['destinatario_nombre']) ?>
                                    <?php endif; ?>
                                </td>
                                <td><a href="ver_mensaje.php?id=<?= $m['id'] ?>"><?= htmlspecialchars($m['asunto']) ?></a></td>
                                <td><?= $m['created_at'] ?></td>
                                <td>
                                    <?php if ($tab == 'inbox' && !$m['is_read']): ?>
                                        <span class="badge bg-primary">No leído</span>
                                    <?php elseif ($tab == 'inbox' && $m['is_read']): ?>
                                        <span class="badge bg-secondary">Leído</span>
                                    <?php else: ?>
                                        <span class="badge bg-info">Enviado</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <nav>
                        <ul class="pagination">
                            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                                <li class="page-item <?= ($i == $pagina) ? 'active' : '' ?>">
                                    <a class="page-link" href="?tab=<?= $tab ?>&pagina=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>