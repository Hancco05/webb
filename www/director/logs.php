<?php
require_once '../includes/auth.php';
verificarSesion('director');
require_once '../includes/db.php';
$titulo_pagina = 'Registro de Actividades (Logs)';
include '../includes/header.php';

$limite = 20;
$pagina = $_GET['pagina'] ?? 1;
$offset = ($pagina - 1) * $limite;
$filtro_usuario = $_GET['usuario'] ?? null;
$filtro_accion = $_GET['accion'] ?? null;

$total = contarLogs($filtro_usuario, $filtro_accion);
$totalPaginas = ceil($total / $limite);
$logs = obtenerLogs($limite, $offset, $filtro_usuario, $filtro_accion);

// Obtener lista de usuarios y acciones para filtros
$usuarios = $pdo->query("SELECT id, nombre FROM usuarios ORDER BY nombre")->fetchAll();
$acciones = $pdo->query("SELECT DISTINCT accion FROM logs ORDER BY accion")->fetchAll();
?>
<div class="card">
    <div class="card-header">Filtros</div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label>Usuario</label>
                <select name="usuario" class="form-select">
                    <option value="">Todos</option>
                    <?php foreach($usuarios as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= ($filtro_usuario == $u['id']) ? 'selected' : '' ?>><?= htmlspecialchars($u['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label>Acción</label>
                <select name="accion" class="form-select">
                    <option value="">Todas</option>
                    <?php foreach($acciones as $a): ?>
                        <option value="<?= $a['accion'] ?>" <?= ($filtro_accion == $a['accion']) ? 'selected' : '' ?>><?= $a['accion'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 align-self-end">
                <button type="submit" class="btn btn-primary">Filtrar</button>
                <a href="logs.php" class="btn btn-secondary">Limpiar</a>
            </div>
        </form>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">Registro de actividades</div>
    <div class="card-body">
        <?php if (empty($logs)): ?>
            <p>No hay registros.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr><th>Fecha</th><th>Usuario</th><th>Rol</th><th>Acción</th><th>Tabla</th><th>ID registro</th><th>Detalles</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($logs as $log): ?>
                        <tr>
                            <td><?= $log['created_at'] ?></td>
                            <td><?= htmlspecialchars($log['usuario_nombre']) ?> (ID <?= $log['usuario_id'] ?>)</td>
                            <td><?= $log['usuario_rol'] ?></td>
                            <td><?= $log['accion'] ?></td>
                            <td><?= $log['tabla_afectada'] ?>:</td>
                            <td><?= $log['registro_id'] ?>:</td>
                            <td><?= nl2br(htmlspecialchars($log['detalles'])) ?>:</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <nav>
                <ul class="pagination">
                    <?php if ($pagina > 1): ?>
                        <li class="page-item"><a class="page-link" href="?pagina=<?= $pagina-1 ?>&usuario=<?= $filtro_usuario ?>&accion=<?= $filtro_accion ?>">Anterior</a></li>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                        <li class="page-item <?= ($i == $pagina) ? 'active' : '' ?>">
                            <a class="page-link" href="?pagina=<?= $i ?>&usuario=<?= $filtro_usuario ?>&accion=<?= $filtro_accion ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <?php if ($pagina < $totalPaginas): ?>
                        <li class="page-item"><a class="page-link" href="?pagina=<?= $pagina+1 ?>&usuario=<?= $filtro_usuario ?>&accion=<?= $filtro_accion ?>">Siguiente</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>
<?php include '../includes/footer.php'; ?>