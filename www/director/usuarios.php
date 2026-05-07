<?php
require_once '../includes/auth.php';
verificarSesion('director');
require_once '../includes/db.php';
$titulo_pagina = 'Gestión de Usuarios';
include '../includes/header.php';

// Procesar acciones (crear, editar, eliminar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verificarTokenCSRF($_POST['csrf_token'])) { die("CSRF inválido"); }
    
    $action = $_POST['action'] ?? '';
    if ($action === 'crear') {
        $nombre = $_POST['nombre'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $rol = $_POST['rol'];
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password_hash, rol) VALUES (?,?,?,?)");
        if ($stmt->execute([$nombre, $email, $password, $rol])) {
            // Enviar correo de bienvenida (opcional)
            $cuerpo = "<h1>Bienvenido/a</h1><p>Su cuenta ha sido creada. Usuario: $email, Contraseña: {$_POST['password']}</p>";
            enviarCorreo($email, 'Bienvenido al sistema', $cuerpo);
            $_SESSION['mensaje'] = "Usuario creado y notificado";
        } else {
            $_SESSION['mensaje'] = "Error al crear usuario";
        }
        header("Location: usuarios.php");
        exit;
    } elseif ($action === 'editar') {
        $id = $_POST['id'];
        $nombre = $_POST['nombre'];
        $email = $_POST['email'];
        $rol = $_POST['rol'];
        $stmt = $pdo->prepare("UPDATE usuarios SET nombre=?, email=?, rol=? WHERE id=?");
        $stmt->execute([$nombre, $email, $rol, $id]);
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE usuarios SET password_hash=? WHERE id=?");
            $stmt->execute([$password, $id]);
        }
        $_SESSION['mensaje'] = "Usuario actualizado";
        header("Location: usuarios.php");
        exit;
    } elseif ($action === 'eliminar') {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id=?");
        $stmt->execute([$id]);
        $_SESSION['mensaje'] = "Usuario eliminado";
        header("Location: usuarios.php");
        exit;
    }
}

// Después de crear usuario
if ($stmt->execute([$nombre, $email, $password, $rol])) {
    $nuevo_id = $pdo->lastInsertId();
    registrarLog($_SESSION['user_id'], 'crear', 'usuarios', $nuevo_id, "Usuario: $nombre, email: $email, rol: $rol");
    $_SESSION['mensaje'] = "Usuario creado y notificado por correo.";
}

// Después de editar
if ($stmt->execute([$nombre, $email, $rol, $id])) {
    registrarLog($_SESSION['user_id'], 'editar', 'usuarios', $id, "Usuario ID $id: nuevos datos nombre=$nombre, email=$email, rol=$rol");
    $_SESSION['mensaje'] = "Usuario actualizado";
}

// Después de eliminar
if ($stmt->execute([$id])) {
    registrarLog($_SESSION['user_id'], 'eliminar', 'usuarios', $id, "Usuario ID $id eliminado");
    $_SESSION['mensaje'] = "Usuario eliminado";
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
            <table class="table table-bordered table-striped">
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
        <!-- Paginación -->
        <nav>
            <ul class="pagination justify-content-center">
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

<!-- Modal para crear/editar usuario (igual que antes) -->
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
                    <small class="text-muted">Dejar en blanco para no cambiar</small>
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
    document.getElementById('rol').value = 'estudiante';
}
function editarUsuario(user) {
    document.getElementById('action').value = 'editar';
    document.getElementById('userId').value = user.id;
    document.getElementById('nombre').value = user.nombre;
    document.getElementById('email').value = user.email;
    document.getElementById('password').value = '';
    document.getElementById('rol').value = user.rol;
}

// ========== FUNCIONES PARA LOGS ==========
function registrarLog($usuario_id, $accion, $tabla_afectada = null, $registro_id = null, $detalles = null) {
    global $pdo;
    // Obtener datos del usuario si no se pasan
    $usuario = obtenerDatosUsuario($usuario_id);
    $nombre = $usuario['nombre'];
    $rol = $usuario['rol'];
    
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    
    $stmt = $pdo->prepare("INSERT INTO logs (usuario_id, usuario_nombre, usuario_rol, accion, tabla_afectada, registro_id, detalles, ip_address, user_agent) VALUES (?,?,?,?,?,?,?,?,?)");
    return $stmt->execute([$usuario_id, $nombre, $rol, $accion, $tabla_afectada, $registro_id, $detalles, $ip, $user_agent]);
}

function obtenerLogs($limite = 50, $offset = 0, $filtro_usuario = null, $filtro_accion = null) {
    global $pdo;
    $sql = "SELECT * FROM logs WHERE 1=1";
    $params = [];
    if ($filtro_usuario) {
        $sql .= " AND usuario_id = ?";
        $params[] = $filtro_usuario;
    }
    if ($filtro_accion) {
        $sql .= " AND accion = ?";
        $params[] = $filtro_accion;
    }
    $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limite;
    $params[] = $offset;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function contarLogs($filtro_usuario = null, $filtro_accion = null) {
    global $pdo;
    $sql = "SELECT COUNT(*) FROM logs WHERE 1=1";
    $params = [];
    if ($filtro_usuario) {
        $sql .= " AND usuario_id = ?";
        $params[] = $filtro_usuario;
    }
    if ($filtro_accion) {
        $sql .= " AND accion = ?";
        $params[] = $filtro_accion;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}

</script>
<?php include '../includes/footer.php'; ?>