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

// ========== FUNCIONES PARA ENTREGAS ==========
function obtenerEntregasPorTarea($tarea_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT e.*, u.nombre as estudiante_nombre 
        FROM entregas e
        JOIN usuarios u ON e.estudiante_id = u.id
        WHERE e.tarea_id = ?
        ORDER BY e.fecha_entrega DESC
    ");
    $stmt->execute([$tarea_id]);
    return $stmt->fetchAll();
}

function obtenerEntregaEstudiante($tarea_id, $estudiante_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM entregas WHERE tarea_id = ? AND estudiante_id = ?");
    $stmt->execute([$tarea_id, $estudiante_id]);
    return $stmt->fetch();
}

function registrarEntrega($tarea_id, $estudiante_id, $archivo_nombre, $archivo_ruta, $comentario = null) {
    global $pdo;
    // Verificar si ya existe entrega
    $existe = obtenerEntregaEstudiante($tarea_id, $estudiante_id);
    if ($existe) {
        // Actualizar archivo y comentario
        $stmt = $pdo->prepare("UPDATE entregas SET archivo_nombre=?, archivo_ruta=?, comentario=?, fecha_entrega=NOW() WHERE tarea_id=? AND estudiante_id=?");
        return $stmt->execute([$archivo_nombre, $archivo_ruta, $comentario, $tarea_id, $estudiante_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO entregas (tarea_id, estudiante_id, archivo_nombre, archivo_ruta, comentario) VALUES (?,?,?,?,?)");
        return $stmt->execute([$tarea_id, $estudiante_id, $archivo_nombre, $archivo_ruta, $comentario]);
    }
}

function calificarEntrega($entrega_id, $calificacion, $comentario_profesor = null) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE entregas SET calificacion = ?, comentario_profesor = ? WHERE id = ?");
    return $stmt->execute([$calificacion, $comentario_profesor, $entrega_id]);
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

// ========== FUNCIONES PARA ENTREGAS DE TAREAS ==========
function obtenerEntregasPorTarea($tarea_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT e.*, u.nombre as estudiante_nombre 
        FROM entregas e
        JOIN usuarios u ON e.estudiante_id = u.id
        WHERE e.tarea_id = ?
        ORDER BY e.fecha_entrega DESC
    ");
    $stmt->execute([$tarea_id]);
    return $stmt->fetchAll();
}

function obtenerEntregaPorEstudianteTarea($estudiante_id, $tarea_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM entregas WHERE estudiante_id = ? AND tarea_id = ?");
    $stmt->execute([$estudiante_id, $tarea_id]);
    return $stmt->fetch();
}

function guardarEntrega($tarea_id, $estudiante_id, $archivo_nombre, $archivo_ruta, $comentario) {
    global $pdo;
    // Verificar si ya existe entrega
    $stmt = $pdo->prepare("SELECT id FROM entregas WHERE tarea_id = ? AND estudiante_id = ?");
    $stmt->execute([$tarea_id, $estudiante_id]);
    if ($stmt->fetch()) {
        // Actualizar
        $stmt = $pdo->prepare("UPDATE entregas SET archivo_nombre=?, archivo_ruta=?, comentario=?, fecha_entrega=NOW() WHERE tarea_id=? AND estudiante_id=?");
        return $stmt->execute([$archivo_nombre, $archivo_ruta, $comentario, $tarea_id, $estudiante_id]);
    } else {
        // Insertar
        $stmt = $pdo->prepare("INSERT INTO entregas (tarea_id, estudiante_id, archivo_nombre, archivo_ruta, comentario) VALUES (?,?,?,?,?)");
        return $stmt->execute([$tarea_id, $estudiante_id, $archivo_nombre, $archivo_ruta, $comentario]);
    }
}

function calificarEntrega($entrega_id, $calificacion, $comentario_profesor) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE entregas SET calificacion=?, comentario_profesor=? WHERE id=?");
    return $stmt->execute([$calificacion, $comentario_profesor, $entrega_id]);
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

// ========== MENSAJERÍA ==========
function enviarMensaje($remitente_id, $destinatario_id, $asunto, $mensaje) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO mensajes (remitente_id, destinatario_id, asunto, mensaje) VALUES (?,?,?,?)");
    $result = $stmt->execute([$remitente_id, $destinatario_id, $asunto, $mensaje]);
    if ($result) {
        // Crear notificación para el destinatario
        crearNotificacion($destinatario_id, "Nuevo mensaje de " . obtenerNombreUsuario($remitente_id), "Asunto: $asunto", "mensajes.php");
    }
    return $result;
}

function obtenerNombreUsuario($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT nombre FROM usuarios WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn();
}

function obtenerMensajesRecibidos($user_id, $limite = 20, $offset = 0) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT m.*, u.nombre as remitente_nombre 
        FROM mensajes m
        JOIN usuarios u ON m.remitente_id = u.id
        WHERE m.destinatario_id = ?
        ORDER BY m.fecha_envio DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
    $stmt->bindValue(2, $limite, PDO::PARAM_INT);
    $stmt->bindValue(3, $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function obtenerMensajesEnviados($user_id, $limite = 20, $offset = 0) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT m.*, u.nombre as destinatario_nombre 
        FROM mensajes m
        JOIN usuarios u ON m.destinatario_id = u.id
        WHERE m.remitente_id = ?
        ORDER BY m.fecha_envio DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
    $stmt->bindValue(2, $limite, PDO::PARAM_INT);
    $stmt->bindValue(3, $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function marcarMensajeLeido($mensaje_id, $user_id) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE mensajes SET leido = 1 WHERE id = ? AND destinatario_id = ?");
    return $stmt->execute([$mensaje_id, $user_id]);
}

function obtenerMensaje($mensaje_id, $user_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT m.*, u.nombre as remitente_nombre 
        FROM mensajes m
        JOIN usuarios u ON m.remitente_id = u.id
        WHERE m.id = ? AND (m.remitente_id = ? OR m.destinatario_id = ?)
    ");
    $stmt->execute([$mensaje_id, $user_id, $user_id]);
    return $stmt->fetch();
}

function contarMensajesNoLeidos($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM mensajes WHERE destinatario_id = ? AND leido = 0");
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn();
}

function crearNotificacion($usuario_id, $titulo, $mensaje, $link = null) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO notificaciones (usuario_id, titulo, mensaje, link) VALUES (?,?,?,?)");
    return $stmt->execute([$usuario_id, $titulo, $mensaje, $link]);
}

function obtenerNotificaciones($usuario_id, $limite = 10) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM notificaciones WHERE usuario_id = ? ORDER BY created_at DESC LIMIT ?");
    $stmt->bindValue(1, $usuario_id, PDO::PARAM_INT);
    $stmt->bindValue(2, $limite, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function marcarNotificacionLeida($notificacion_id, $usuario_id) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE notificaciones SET leido = 1 WHERE id = ? AND usuario_id = ?");
    return $stmt->execute([$notificacion_id, $usuario_id]);
}

// ========== CALENDARIO ==========
function obtenerEventos($fecha_inicio = null, $fecha_fin = null, $curso_id = null) {
    global $pdo;
    $sql = "SELECT e.*, c.nombre as curso_nombre, a.nombre as asignatura_nombre 
            FROM eventos e
            LEFT JOIN cursos c ON e.curso_id = c.id
            LEFT JOIN asignaturas a ON e.asignatura_id = a.id
            WHERE 1=1";
    $params = [];
    if ($fecha_inicio && $fecha_fin) {
        $sql .= " AND e.fecha_inicio >= ? AND e.fecha_fin <= ?";
        $params[] = $fecha_inicio;
        $params[] = $fecha_fin;
    }
    if ($curso_id) {
        $sql .= " AND (e.curso_id = ? OR e.curso_id IS NULL)";
        $params[] = $curso_id;
    }
    $sql .= " ORDER BY e.fecha_inicio ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function crearEvento($titulo, $descripcion, $fecha_inicio, $fecha_fin, $hora_inicio, $hora_fin, $tipo, $curso_id, $asignatura_id, $creado_por) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO eventos (titulo, descripcion, fecha_inicio, fecha_fin, hora_inicio, hora_fin, tipo, curso_id, asignatura_id, creado_por) VALUES (?,?,?,?,?,?,?,?,?,?)");
    return $stmt->execute([$titulo, $descripcion, $fecha_inicio, $fecha_fin, $hora_inicio, $hora_fin, $tipo, $curso_id, $asignatura_id, $creado_por]);
}

function actualizarEvento($id, $titulo, $descripcion, $fecha_inicio, $fecha_fin, $hora_inicio, $hora_fin, $tipo, $curso_id, $asignatura_id) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE eventos SET titulo=?, descripcion=?, fecha_inicio=?, fecha_fin=?, hora_inicio=?, hora_fin=?, tipo=?, curso_id=?, asignatura_id=? WHERE id=?");
    return $stmt->execute([$titulo, $descripcion, $fecha_inicio, $fecha_fin, $hora_inicio, $hora_fin, $tipo, $curso_id, $asignatura_id, $id]);
}

function eliminarEvento($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM eventos WHERE id=?");
    return $stmt->execute([$id]);
}

// ========== EVALUACIONES (CUESTIONARIOS) ==========
function obtenerCuestionariosPorCurso($curso_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT c.*, a.nombre as asignatura_nombre, u.nombre as profesor_nombre
        FROM cuestionarios c
        JOIN asignaturas a ON c.asignatura_id = a.id
        JOIN usuarios u ON c.profesor_id = u.id
        WHERE c.curso_id = ? AND c.fecha_fin >= NOW()
        ORDER BY c.fecha_inicio
    ");
    $stmt->execute([$curso_id]);
    return $stmt->fetchAll();
}

function obtenerCuestionariosPorProfesor($profesor_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT c.*, a.nombre as asignatura_nombre, cur.nombre as curso_nombre
        FROM cuestionarios c
        JOIN asignaturas a ON c.asignatura_id = a.id
        JOIN cursos cur ON c.curso_id = cur.id
        WHERE c.profesor_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$profesor_id]);
    return $stmt->fetchAll();
}

function crearCuestionario($titulo, $descripcion, $asignatura_id, $curso_id, $profesor_id, $fecha_inicio, $fecha_fin, $tiempo_limite, $intentos_permitidos) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO cuestionarios (titulo, descripcion, asignatura_id, curso_id, profesor_id, fecha_inicio, fecha_fin, tiempo_limite, intentos_permitidos) VALUES (?,?,?,?,?,?,?,?,?)");
    return $stmt->execute([$titulo, $descripcion, $asignatura_id, $curso_id, $profesor_id, $fecha_inicio, $fecha_fin, $tiempo_limite, $intentos_permitidos]);
}
// Funciones para preguntas y opciones, etc. (las iremos agregando según necesitemos)

// ========== EVALUACIONES (CUESTIONARIOS) ==========
function obtenerCuestionariosPorCurso($curso_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT c.*, a.nombre as asignatura_nombre, u.nombre as profesor_nombre
        FROM cuestionarios c
        JOIN asignaturas a ON c.asignatura_id = a.id
        JOIN usuarios u ON c.profesor_id = u.id
        WHERE c.curso_id = ? AND c.fecha_fin >= NOW()
        ORDER BY c.fecha_inicio
    ");
    $stmt->execute([$curso_id]);
    return $stmt->fetchAll();
}

function obtenerCuestionariosPorProfesor($profesor_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT c.*, a.nombre as asignatura_nombre, cur.nombre as curso_nombre
        FROM cuestionarios c
        JOIN asignaturas a ON c.asignatura_id = a.id
        JOIN cursos cur ON c.curso_id = cur.id
        WHERE c.profesor_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$profesor_id]);
    return $stmt->fetchAll();
}

function obtenerCuestionario($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM cuestionarios WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function crearCuestionario($titulo, $descripcion, $asignatura_id, $curso_id, $profesor_id, $fecha_inicio, $fecha_fin, $tiempo_limite, $intentos_permitidos) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO cuestionarios (titulo, descripcion, asignatura_id, curso_id, profesor_id, fecha_inicio, fecha_fin, tiempo_limite, intentos_permitidos) VALUES (?,?,?,?,?,?,?,?,?)");
    return $stmt->execute([$titulo, $descripcion, $asignatura_id, $curso_id, $profesor_id, $fecha_inicio, $fecha_fin, $tiempo_limite, $intentos_permitidos]);
}

function actualizarCuestionario($id, $titulo, $descripcion, $fecha_inicio, $fecha_fin, $tiempo_limite, $intentos_permitidos) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE cuestionarios SET titulo=?, descripcion=?, fecha_inicio=?, fecha_fin=?, tiempo_limite=?, intentos_permitidos=? WHERE id=?");
    return $stmt->execute([$titulo, $descripcion, $fecha_inicio, $fecha_fin, $tiempo_limite, $intentos_permitidos, $id]);
}

function eliminarCuestionario($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM cuestionarios WHERE id=?");
    return $stmt->execute([$id]);
}

// ========== PREGUNTAS Y OPCIONES ==========
function obtenerPreguntas($cuestionario_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM preguntas WHERE cuestionario_id = ? ORDER BY id");
    $stmt->execute([$cuestionario_id]);
    return $stmt->fetchAll();
}

function obtenerPregunta($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM preguntas WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function crearPregunta($cuestionario_id, $enunciado, $tipo, $puntos) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO preguntas (cuestionario_id, enunciado, tipo, puntos) VALUES (?,?,?,?)");
    return $stmt->execute([$cuestionario_id, $enunciado, $tipo, $puntos]);
}

function actualizarPregunta($id, $enunciado, $tipo, $puntos) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE preguntas SET enunciado=?, tipo=?, puntos=? WHERE id=?");
    return $stmt->execute([$enunciado, $tipo, $puntos, $id]);
}

function eliminarPregunta($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM preguntas WHERE id=?");
    return $stmt->execute([$id]);
}

// Opciones
function obtenerOpciones($pregunta_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM opciones WHERE pregunta_id = ? ORDER BY id");
    $stmt->execute([$pregunta_id]);
    return $stmt->fetchAll();
}

function crearOpcion($pregunta_id, $texto, $es_correcta) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO opciones (pregunta_id, texto, es_correcta) VALUES (?,?,?)");
    return $stmt->execute([$pregunta_id, $texto, $es_correcta]);
}

function actualizarOpcion($id, $texto, $es_correcta) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE opciones SET texto=?, es_correcta=? WHERE id=?");
    return $stmt->execute([$texto, $es_correcta, $id]);
}

function eliminarOpcion($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM opciones WHERE id=?");
    return $stmt->execute([$id]);
}

// ========== RESPUESTAS Y CALIFICACIONES ==========
function guardarRespuesta($cuestionario_id, $pregunta_id, $estudiante_id, $opcion_id, $respuesta_texto, $es_correcta) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO respuestas (cuestionario_id, pregunta_id, estudiante_id, opcion_id, respuesta_texto, es_correcta) VALUES (?,?,?,?,?,?) ON DUPLICATE KEY UPDATE opcion_id=?, respuesta_texto=?, es_correcta=?");
    return $stmt->execute([$cuestionario_id, $pregunta_id, $estudiante_id, $opcion_id, $respuesta_texto, $es_correcta, $opcion_id, $respuesta_texto, $es_correcta]);
}

function calcularCalificacion($cuestionario_id, $estudiante_id) {
    global $pdo;
    // Obtener todas las preguntas y respuestas
    $stmt = $pdo->prepare("
        SELECT p.puntos, r.es_correcta
        FROM preguntas p
        LEFT JOIN respuestas r ON p.id = r.pregunta_id AND r.estudiante_id = ? AND r.cuestionario_id = ?
        WHERE p.cuestionario_id = ?
    ");
    $stmt->execute([$estudiante_id, $cuestionario_id, $cuestionario_id]);
    $rows = $stmt->fetchAll();
    $puntaje_obtenido = 0;
    $puntaje_total = 0;
    foreach ($rows as $row) {
        $puntaje_total += $row['puntos'];
        if ($row['es_correcta']) {
            $puntaje_obtenido += $row['puntos'];
        }
    }
    // Guardar calificación
    $stmt2 = $pdo->prepare("INSERT INTO calificaciones (cuestionario_id, estudiante_id, puntaje_obtenido, puntaje_total) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE puntaje_obtenido=?, puntaje_total=?, intentos=intentos+1, fecha_calificacion=NOW()");
    $stmt2->execute([$cuestionario_id, $estudiante_id, $puntaje_obtenido, $puntaje_total, $puntaje_obtenido, $puntaje_total]);
    return ['obtenido' => $puntaje_obtenido, 'total' => $puntaje_total, 'porcentaje' => ($puntaje_total > 0) ? ($puntaje_obtenido / $puntaje_total) * 100 : 0];
}

function obtenerCalificacionEstudiante($cuestionario_id, $estudiante_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM calificaciones WHERE cuestionario_id = ? AND estudiante_id = ?");
    $stmt->execute([$cuestionario_id, $estudiante_id]);
    return $stmt->fetch();
}

function obtenerRespuestasEstudiante($cuestionario_id, $estudiante_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT r.*, p.enunciado, p.puntos, o.texto as opcion_texto FROM respuestas r JOIN preguntas p ON r.pregunta_id = p.id LEFT JOIN opciones o ON r.opcion_id = o.id WHERE r.cuestionario_id = ? AND r.estudiante_id = ?");
    $stmt->execute([$cuestionario_id, $estudiante_id]);
    return $stmt->fetchAll();
}

function registrarIntentoLogin($email) {
    global $pdo;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $stmt = $pdo->prepare("INSERT INTO login_attempts (email, ip_address) VALUES (?, ?)");
    $stmt->execute([$email, $ip]);
    // Eliminar intentos viejos (más de 15 minutos)
    $pdo->prepare("DELETE FROM login_attempts WHERE attempt_time < DATE_SUB(NOW(), INTERVAL 15 MINUTE)")->execute();
}

function verificarBloqueo($email) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE email = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
    $stmt->execute([$email]);
    $intentos = $stmt->fetchColumn();
    return $intentos >= 5; // Bloquear después de 5 intentos fallidos en 15 min
}

function limpiarIntentosLogin($email) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM login_attempts WHERE email = ?");
    $stmt->execute([$email]);
}

// ========== SEGURIDAD: INTENTOS DE LOGIN ==========
function verificarIntentosFallidos($email, $ip, $limite = 5, $tiempo_bloqueo_minutos = 15) {
    global $pdo;
    $tiempo_limite = date('Y-m-d H:i:s', strtotime("-$tiempo_bloqueo_minutos minutes"));
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM intentos_login WHERE email = ? AND intento_time > ?");
    $stmt->execute([$email, $tiempo_limite]);
    $intentos = $stmt->fetchColumn();
    if ($intentos >= $limite) {
        return false; // Bloqueado
    }
    return true;
}

function registrarIntentoFallido($email, $ip) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO intentos_login (email, ip_address) VALUES (?, ?)");
    return $stmt->execute([$email, $ip]);
}

function limpiarIntentosExitosos($email) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM intentos_login WHERE email = ?");
    return $stmt->execute([$email]);
}

function validarPassword($password) {
    // Mínimo 8 caracteres, al menos una mayúscula, una minúscula, un número
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password);
}

// ========== MENSAJERÍA INTERNA ==========
function enviarMensaje($from_id, $to_id, $asunto, $mensaje, $parent_id = null) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO mensajes (from_user_id, to_user_id, asunto, mensaje, parent_id) VALUES (?,?,?,?,?)");
    return $stmt->execute([$from_id, $to_id, $asunto, $mensaje, $parent_id]);
}

function obtenerMensajesRecibidos($user_id, $limite = 20, $offset = 0) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT m.*, u.nombre as remitente_nombre 
        FROM mensajes m
        JOIN usuarios u ON m.from_user_id = u.id
        WHERE m.to_user_id = ?
        ORDER BY m.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
    $stmt->bindValue(2, $limite, PDO::PARAM_INT);
    $stmt->bindValue(3, $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function obtenerMensajesEnviados($user_id, $limite = 20, $offset = 0) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT m.*, u.nombre as destinatario_nombre 
        FROM mensajes m
        JOIN usuarios u ON m.to_user_id = u.id
        WHERE m.from_user_id = ?
        ORDER BY m.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
    $stmt->bindValue(2, $limite, PDO::PARAM_INT);
    $stmt->bindValue(3, $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function contarMensajesNoLeidos($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM mensajes WHERE to_user_id = ? AND is_read = 0");
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn();
}

function marcarMensajeComoLeido($mensaje_id, $user_id) {
    global $pdo;
    // Solo marcar si el destinatario es el usuario
    $stmt = $pdo->prepare("UPDATE mensajes SET is_read = 1 WHERE id = ? AND to_user_id = ?");
    return $stmt->execute([$mensaje_id, $user_id]);
}

function obtenerMensaje($id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT m.*, 
               u1.nombre as remitente_nombre, u1.rol as remitente_rol,
               u2.nombre as destinatario_nombre, u2.rol as destinatario_rol
        FROM mensajes m
        JOIN usuarios u1 ON m.from_user_id = u1.id
        JOIN usuarios u2 ON m.to_user_id = u2.id
        WHERE m.id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function obtenerRespuestas($parent_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT m.*, u.nombre as remitente_nombre 
        FROM mensajes m
        JOIN usuarios u ON m.from_user_id = u.id
        WHERE m.parent_id = ?
        ORDER BY m.created_at ASC
    ");
    $stmt->execute([$parent_id]);
    return $stmt->fetchAll();
}

function buscarUsuarios($termino, $excluir_id = null) {
    global $pdo;
    $sql = "SELECT id, nombre, email, rol FROM usuarios WHERE nombre LIKE ? OR email LIKE ?";
    $params = ["%$termino%", "%$termino%"];
    if ($excluir_id) {
        $sql .= " AND id != ?";
        $params[] = $excluir_id;
    }
    $sql .= " LIMIT 10";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}
?>