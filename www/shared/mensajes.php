<?php
require_once '../includes/auth.php';
// No restringir rol específico, todos pueden usar mensajería
require_once '../includes/db.php';
$titulo_pagina = 'Mensajería';
include '../includes/header.php';

$user_id = $_SESSION['user_id'];
$vista = $_GET['vista'] ?? 'recibidos';
$pagina = $_GET['pagina'] ?? 1;
$limite = 10;
$offset = ($pagina - 1) * $limite;

$totalMensajes = 0;
$mensajes = [];

if ($vista == 'recibidos') {
    $mensajes = obtenerMensajesRecibidos($user_id, $limite, $offset);
    $totalMensajes = $pdo->prepare("SELECT COUNT(*) FROM mensajes WHERE destinatario_id = ?");
    $totalMensajes->execute([$user_id]);
    $totalMensajes = $totalMensajes->fetchColumn();
} elseif ($vista == 'enviados') {
    $mensajes = obtenerMensajesEnviados($user_id, $limite, $offset);
    $totalMensajes = $pdo->prepare("SELECT COUNT(*) FROM mensajes WHERE remitente_id = ?");
    $totalMensajes->execute([$user_id]);
    $totalMensajes = $totalMensajes->fetchColumn();
}
?>
<div class="row">
    <div class="col-md-3">
        <div class="card">
            <div class="card-header">Acciones</div>
            <div class="card-body">
                <a href="nuevo_mensaje.php" class="btn btn-primary w-100 mb-2">+ Nuevo mensaje</a>
                <a href="mensajes.php?vista=recibidos" class="btn btn-outline-secondary w-100 mb-2">Recibidos <?php $noLeidos = contarMensajesNoLeidos($user_id); if($noLeidos > 0) echo "<span class='badge bg-danger'>$noLeidos</span>"; ?></a>
                <a href="mensajes.php?vista=enviados" class="btn btn-outline-secondary w-100">Enviados</a>
            </div>
        </div>
        <div class="card mt-3">
            <div class="card-header">Notificaciones</div>
            <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                <?php $notis = obtenerNotificaciones($user_id, 5); ?>
                <?php foreach($notis as $n): ?>
                    <div class="alert alert-sm alert-<?= $n['leido'] ? 'secondary' : 'info' ?>">
                        <strong><?= htmlspecialchars($n['titulo']) ?></strong><br>
                        <small><?= $n['mensaje'] ?></small><br>
                        <?php if($n['link']): ?><a href="<?= $n['link'] ?>">Ver</a><?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="card">
            <div class="card-header"><?= ucfirst($vista) ?></div>
            <div class="card-body">
                <?php if(empty($mensajes)): ?>
                    <p>No hay mensajes.</p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr><th>De/Para</th><th>Asunto</th><th>Fecha</th><th></th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($mensajes as $m): ?>
                                <?php 
                                    $nombreCampo = ($vista == 'recibidos') ? 'remitente_nombre' : 'destinatario_nombre';
                                    $leidoClass = ($vista == 'recibidos' && !$m['leido']) ? 'fw-bold' : '';
                                ?>
                                <tr class="<?= $leidoClass ?>">
                                    <td><?= htmlspecialchars($m[$nombreCampo]) ?></td>
                                    <td><?= htmlspecialchars($m['asunto']) ?></td>
                                    <td><?= $m['fecha_envio'] ?></td>
                                    <td><a href="ver_mensaje.php?id=<?= $m['id'] ?>&vista=<?= $vista ?>" class="btn btn-sm btn-primary">Ver</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php
                    $totalPaginas = ceil($totalMensajes / $limite);
                    if($totalPaginas > 1): ?>
                        <nav><ul class="pagination"><?php for($i=1;$i<=$totalPaginas;$i++): ?><li class="page-item <?= ($i==$pagina)?'active':'' ?>"><a class="page-link" href="?vista=<?= $vista ?>&pagina=<?= $i ?>"><?= $i ?></a></li><?php endfor; ?></ul></nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>