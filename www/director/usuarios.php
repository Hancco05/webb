<?php
require_once '../includes/auth.php';
verificarSesion('director');
require_once '../includes/db.php';
$titulo_pagina = 'Gestión de Usuarios';
include '../includes/header.php';

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verificarTokenCSRF($_POST['csrf_token'])) { die("CSRF inválido"); }
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'crear':
                $nombre = $_POST['nombre'];
                $email = $_POST['email'];
                $password = $_POST['password'];
                $rol = $_POST['rol'];
                // Validar contraseña fuerte
                if (!validarPassword($password)) {
                    $_SESSION['mensaje'] = "La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula y un número.";
                    break;
                }
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password_hash, rol) VALUES (?,?,?,?)");
                if ($stmt->execute([$nombre, $email, $password_hash, $rol])) {
                    $nuevo_id = $pdo->lastInsertId();
                    registrarLog($_SESSION['user_id'], 'crear', 'usuarios', $nuevo_id, "Usuario: $nombre, email: $email, rol: $rol");
                    // Enviar correo de bienvenida (opcional)
                    $cuerpo = "<h1>Bienvenido al sistema educativo</h1><p>Hola $nombre, tu cuenta ha sido creada.</p><p>Email: $email</p><p>Contraseña temporal: $password</p><p>Rol: $rol</p><p>Ingresa al sistema: <a href='http://localhost:8080'>http://localhost:8080</a></p>";
                    enviarCorreo($email, 'Tu cuenta ha sido creada', $cuerpo);
                    $_SESSION['mensaje'] = "Usuario creado y notificado.";
                } else {
                    $_SESSION['mensaje'] = "Error al crear usuario.";
                }
                break;
            case 'editar':
                $id = $_POST['id'];
                $nombre = $_POST['nombre'];
                $email = $_POST['email'];
                $rol = $_POST['rol'];
                $password = $_POST['password'] ?? '';
                $stmt = $pdo->prepare("UPDATE usuarios SET nombre=?, email=?, rol=? WHERE id=?");
                $stmt->execute([$nombre, $email, $rol, $id]);
                if (!empty($password)) {
                    if (!validarPassword($password)) {
                        $_SESSION['mensaje'] = "La nueva contraseña no cumple los requisitos mínimos.";
                        break;
                    }
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt2 = $pdo->prepare("UPDATE usuarios SET password_hash=? WHERE id=?");
                    $stmt2->execute([$password_hash, $id]);
                }
                registrarLog($_SESSION['user_id'], 'editar', 'usuarios', $id, "Usuario ID $id actualizado");
                $_SESSION['mensaje'] = "Usuario actualizado";
                break;
            case 'eliminar':
                $id = $_POST['id'];
                $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id=?");
                $stmt->execute([$id]);
                registrarLog($_SESSION['user_id'], 'eliminar', 'usuarios', $id, "Usuario ID $id eliminado");
                $_SESSION['mensaje'] = "Usuario eliminado";
                break;
        }
        header("Location: usuarios.php");
        exit;
    }
}

// Paginación
$limite = 10;
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina - 1) * $limite;
$total = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
$totalPaginas = ceil($total / $limite);
$stmt = $pdo->prepare("SELECT * FROM usuarios ORDER BY created_at DESC LIMIT :offset, :limite");
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
$stmt->execute();
$usuarios = $stmt->fetchAll();
?>
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <span>Lista de Usuarios</span>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalUsuario" onclick="resetForm()">+ Nuevo Usuario</button>
    </div>
    <div class="card-body">
        <?php if(isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-success"><?= $_SESSION['mensaje']; unset($_SESSION['mensaje']); ?></div>
        <?php endif; ?>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr><th>ID</th><th>Nombre</th><th>Email</th><th>Rol</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                    <?php foreach($usuarios as $u): ?>
                    <tr>
                        <td><?= $u['id'] ?></td>
                        <td><?= htmlspecialchars($u['nombre']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= $u['rol'] ?></td>
                        <td>
                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalUsuario" 
                                    onclick="editarUsuario(<?= htmlspecialchars(json_encode($u)) ?>)">Editar</button>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar?')">
                                <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
                                <input type="hidden" name="action" value="eliminar">
                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                <button class="btn btn-danger btn-sm">Eliminar</button>
                            </form>
                         </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
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
    </div>
</div>

<!-- Modal para crear/editar -->
<div class="modal fade" id="modalUsuario" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
                <input type="hidden" name="action" id="action" value="crear">
                <input type="hidden" name="id" id="userId">
                <div class="mb-2">
                    <label>Nombre</label>
                    <input type="text" name="nombre" id="nombre" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label>Email</label>
                    <input type="email" name="email" id="email" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label>Contraseña</label>
                    <input type="password" name="password" id="password" class="form-control">
                    <small class="text-muted">Mínimo 8 caracteres, mayúscula, minúscula y número. Dejar en blanco para no cambiar (solo edición).</small>
                </div>
                <div class="mb-2">
                    <label>Rol</label>
                    <select name="rol" id="rol" class="form-select" required>
                        <option value="director">Director</option>
                        <option value="profesor">Profesor</option>
                        <option value="auxiliar">Auxiliar</option>
                        <option value="estudiante">Estudiante</option>
                        <option value="apoderado">Apoderado</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
function resetForm() {
    document.getElementById('action').value = 'crear';
    document.getElementById('userId').value = '';
    document.getElementById('nombre').value = '';
    document.getElementById('email').value = '';
    document.getElementById('password').value = '';
    document.getElementById('password').required = true;
    document.getElementById('rol').value = 'estudiante';
}
function editarUsuario(user) {
    document.getElementById('action').value = 'editar';
    document.getElementById('userId').value = user.id;
    document.getElementById('nombre').value = user.nombre;
    document.getElementById('email').value = user.email;
    document.getElementById('password').value = '';
    document.getElementById('password').required = false;
    document.getElementById('rol').value = user.rol;
}
</script>
<?php include '../includes/footer.php'; ?>