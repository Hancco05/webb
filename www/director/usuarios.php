<?php
// Paginación
$limite = 10; // registros por página
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina - 1) * $limite;

// Total de registros
$total = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
$totalPaginas = ceil($total / $limite);

// Consulta con LIMIT
$stmt = $pdo->prepare("SELECT * FROM usuarios ORDER BY created_at DESC LIMIT :offset, :limite");
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
$stmt->execute();
$usuarios = $stmt->fetchAll();

?>

<input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">

<!-- Después de cerrar la tabla -->
<nav>
    <ul class="pagination">
        <?php if ($pagina > 1): ?>
            <li class="page-item"><a class="page-link" href="?pagina=<?= $pagina-1 ?>">Anterior</a></li>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
            <li class="page-item <?= ($i == $pagina) ? 'active' : '' ?>">
                <a class="page-link" href="?pagina=<?= $i ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
        <?php if ($pagina < $totalPaginas): ?>
            <li class="page-item"><a class="page-link" href="?pagina=<?= $pagina+1 ?>">Siguiente</a></li>
        <?php endif; ?>
    </ul>
</nav>