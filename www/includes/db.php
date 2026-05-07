<?php
// Cargar el autoload de Composer (para PHPMailer)
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$host = 'mysql';
$user = 'webb_user';
$pass = 'webb_pass';
$dbname = 'webb_db';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

function obtenerDatosUsuario($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

function obtenerCursos() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM cursos ORDER BY anio, nombre");
    return $stmt->fetchAll();
}

function obtenerAsignaturasPorCurso($curso_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM asignaturas WHERE curso_id = ?");
    $stmt->execute([$curso_id]);
    return $stmt->fetchAll();
}

function obtenerEstudiantesPorCurso($curso_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT u.id, u.nombre, u.email 
        FROM usuarios u 
        JOIN estudiantes e ON u.id = e.user_id 
        WHERE e.curso_id = ? AND u.rol = 'estudiante'
    ");
    $stmt->execute([$curso_id]);
    return $stmt->fetchAll();
}

function obtenerProfesores() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE rol = 'profesor'");
    $stmt->execute();
    return $stmt->fetchAll();
}

function obtenerCursosPorProfesor($profesor_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT DISTINCT c.* FROM cursos c
        JOIN profesor_asignatura_curso pac ON c.id = pac.curso_id
        WHERE pac.profesor_id = ?
    ");
    $stmt->execute([$profesor_id]);
    return $stmt->fetchAll();
}

function obtenerAsignaturasPorProfesorCurso($profesor_id, $curso_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT a.* FROM asignaturas a
        JOIN profesor_asignatura_curso pac ON a.id = pac.asignatura_id
        WHERE pac.profesor_id = ? AND pac.curso_id = ?
    ");
    $stmt->execute([$profesor_id, $curso_id]);
    return $stmt->fetchAll();
}

function obtenerHijos($apoderado_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT u.*, e.curso_id, c.nombre as curso_nombre 
        FROM usuarios u
        JOIN apoderado_estudiante ae ON u.id = ae.estudiante_id
        JOIN estudiantes e ON u.id = e.user_id
        JOIN cursos c ON e.curso_id = c.id
        WHERE ae.apoderado_id = ?
    ");
    $stmt->execute([$apoderado_id]);
    return $stmt->fetchAll();
}

// ========== FUNCIONES PARA HORARIOS ==========
function obtenerHorariosPorCurso($curso_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT h.*, a.nombre as asignatura_nombre 
        FROM horarios h
        JOIN asignaturas a ON h.asignatura_id = a.id
        WHERE h.curso_id = ?
        ORDER BY FIELD(h.dia_semana, 'Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'), h.hora_inicio
    ");
    $stmt->execute([$curso_id]);
    return $stmt->fetchAll();
}

function guardarHorario($curso_id, $dia, $hora_inicio, $hora_fin, $asignatura_id) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO horarios (curso_id, dia_semana, hora_inicio, hora_fin, asignatura_id) VALUES (?,?,?,?,?)");
    return $stmt->execute([$curso_id, $dia, $hora_inicio, $hora_fin, $asignatura_id]);
}

function eliminarHorario($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM horarios WHERE id = ?");
    return $stmt->execute([$id]);
}

// ========== FUNCIONES CSRF ==========
function generarTokenCSRF() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verificarTokenCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// ========== FUNCIONES PARA TAREAS ==========
function obtenerTareasPorCurso($curso_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT t.*, a.nombre as asignatura_nombre, u.nombre as profesor_nombre
        FROM tareas t
        JOIN asignaturas a ON t.asignatura_id = a.id
        JOIN usuarios u ON t.creado_por = u.id
        WHERE t.curso_id = ?
        ORDER BY t.fecha_entrega ASC
    ");
    $stmt->execute([$curso_id]);
    return $stmt->fetchAll();
}

function obtenerTareasPorProfesor($profesor_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT t.*, c.nombre as curso_nombre, a.nombre as asignatura_nombre
        FROM tareas t
        JOIN cursos c ON t.curso_id = c.id
        JOIN asignaturas a ON t.asignatura_id = a.id
        WHERE t.creado_por = ?
        ORDER BY t.fecha_entrega ASC
    ");
    $stmt->execute([$profesor_id]);
    return $stmt->fetchAll();
}

function crearTarea($titulo, $descripcion, $curso_id, $asignatura_id, $fecha_entrega, $creado_por) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO tareas (titulo, descripcion, curso_id, asignatura_id, fecha_entrega, creado_por) VALUES (?,?,?,?,?,?)");
    return $stmt->execute([$titulo, $descripcion, $curso_id, $asignatura_id, $fecha_entrega, $creado_por]);
}

function actualizarTarea($id, $titulo, $descripcion, $fecha_entrega) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE tareas SET titulo=?, descripcion=?, fecha_entrega=? WHERE id=?");
    return $stmt->execute([$titulo, $descripcion, $fecha_entrega, $id]);
}

function eliminarTarea($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM tareas WHERE id=?");
    return $stmt->execute([$id]);
}

// ========== FUNCIÓN DE ENVÍO DE CORREOS ==========
function enviarCorreo($destinatario, $asunto, $cuerpoHtml, $cuerpoTexto = '') {
    $mail = new PHPMailer(true);
    try {
        // Configuración desde variables de entorno (definidas en docker-compose.yml)
        $mail->isSMTP();
        $mail->Host       = getenv('SMTP_HOST') ?: 'smtp.mailtrap.io';
        $mail->SMTPAuth   = true;
        $mail->Username   = getenv('SMTP_USER') ?: '';
        $mail->Password   = getenv('SMTP_PASS') ?: '';
        $mail->SMTPSecure = getenv('SMTP_SECURE') ?: 'tls';
        $mail->Port       = getenv('SMTP_PORT') ?: 2525;

        $mail->setFrom(getenv('MAIL_FROM') ?: 'no-reply@colegio.com', getenv('MAIL_FROM_NAME') ?: 'Sistema Educativo');
        $mail->addAddress($destinatario);
        
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body    = $cuerpoHtml;
        $mail->AltBody = $cuerpoTexto ?: strip_tags($cuerpoHtml);
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error enviando correo a $destinatario: " . $mail->ErrorInfo);
        return false;
    }
}
?>