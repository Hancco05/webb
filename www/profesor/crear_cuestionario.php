<?php
require_once '../includes/auth.php';
verificarSesion('profesor');
require_once '../includes/db.php';
$titulo_pagina = 'Crear Cuestionario';
include '../includes/header.php';

$profesor_id = $_SESSION['user_id'];
$cursos = obtenerCursosPorProfesor($profesor_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];
    $asignatura_id = $_POST['asignatura_id'];
    $curso_id = $_POST['curso_id'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $tiempo_limite = $_POST['tiempo_limite'] ?: null;
    $intentos_permitidos = $_POST['intentos_permitidos'] ?: 1;
    
    if (crearCuestionario($titulo, $descripcion, $asignatura_id, $curso_id, $profesor_id, $fecha_inicio, $fecha_fin, $tiempo_limite, $intentos_permitidos)) {
        $id = $pdo->lastInsertId();
        header("Location: editar_preguntas.php?id=$id");
        exit;
    } else {
        $error = "Error al crear cuestionario";
    }
}
?>
<div class="card">
    <div class="card-header">Datos del cuestionario</div>
    <div class="card-body">
        <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <form method="POST">
            <div class="mb-2"><label>Título</label><input type="text" name="titulo" class="form-control" required></div>
            <div class="mb-2"><label>Descripción</label><textarea name="descripcion" class="form-control"></textarea></div>
            <div class="row">
                <div class="col-md-6"><label>Curso</label><select name="curso_id" id="curso_id" class="form-select" required>
                    <option value="">Seleccione</option>
                    <?php foreach($cursos as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                    <?php endforeach; ?>
                </select></div>
                <div class="col-md-6"><label>Asignatura</label><select name="asignatura_id" id="asignatura_id" class="form-select" required></select></div>
            </div>
            <div class="row">
                <div class="col-md-3"><label>Fecha inicio</label><input type="datetime-local" name="fecha_inicio" class="form-control" required></div>
                <div class="col-md-3"><label>Fecha fin</label><input type="datetime-local" name="fecha_fin" class="form-control" required></div>
                <div class="col-md-3"><label>Tiempo límite (minutos)</label><input type="number" name="tiempo_limite" class="form-control" step="1" min="0"></div>
                <div class="col-md-3"><label>Intentos permitidos</label><input type="number" name="intentos_permitidos" class="form-control" value="1" step="1" min="1"></div>
            </div>
            <button type="submit" class="btn btn-primary mt-2">Crear y agregar preguntas</button>
        </form>
    </div>
</div>
<script>
document.getElementById('curso_id').addEventListener('change', function() {
    let curso_id = this.value;
    fetch('../ajax/asignaturas_por_curso.php?curso_id=' + curso_id)
        .then(res => res.json())
        .then(data => {
            let select = document.getElementById('asignatura_id');
            select.innerHTML = '<option value="">Seleccione asignatura</option>';
            data.forEach(a => select.innerHTML += `<option value="${a.id}">${a.nombre}</option>`);
        });
});
</script>
<?php include '../includes/footer.php'; ?>