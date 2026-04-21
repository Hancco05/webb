<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/header.php';
auth_required('director');
$u = current_user();
$conn = getDB();

$stats = [
    'estudiantes' => $conn->query("SELECT COUNT(*) c FROM usuarios WHERE rol='estudiante' AND activo=1")->fetch_assoc()['c'],
    'profesores'  => $conn->query("SELECT COUNT(*) c FROM usuarios WHERE rol='profesor' AND activo=1")->fetch_assoc()['c'],
    'auxiliares'  => $conn->query("SELECT COUNT(*) c FROM usuarios WHERE rol='auxiliar' AND activo=1")->fetch_assoc()['c'],
    'apoderados'  => $conn->query("SELECT COUNT(*) c FROM usuarios WHERE rol='apoderado' AND activo=1")->fetch_assoc()['c'],
    'cursos'      => $conn->query("SELECT COUNT(*) c FROM cursos WHERE año=2025")->fetch_assoc()['c'],
    'noticias'    => $conn->query("SELECT COUNT(*) c FROM noticias WHERE activo=1")->fetch_assoc()['c'],
    'notas_hoy'   => $conn->query("SELECT COUNT(*) c FROM notas WHERE DATE(fecha)=CURDATE()")->fetch_assoc()['c'],
    'asist_hoy'   => $conn->query("SELECT COUNT(*) c FROM asistencia WHERE fecha=CURDATE()")->fetch_assoc()['c'],
];

// Asistencia global hoy
$asist_hoy = $conn->query("SELECT estado, COUNT(*) c FROM asistencia WHERE fecha=CURDATE() GROUP BY estado")->fetch_all(MYSQLI_ASSOC);
$asist_map = ['presente'=>0,'ausente'=>0,'justificado'=>0,'tardanza'=>0];
foreach ($asist_hoy as $a) $asist_map[$a['estado']] = $a['c'];
$total_asist = array_sum($asist_map);
$pct_asist   = $total_asist > 0 ? round($asist_map['presente']/$total_asist*100) : 0;

// Promedio general del colegio
$prom_colegio = $conn->query("SELECT ROUND(AVG(nota),1) p FROM notas")->fetch_assoc()['p'] ?? 0;

// Cursos con más ausencias
$cursos_ausencias = $conn->query("
    SELECT c.nombre, COUNT(*) ausencias
    FROM asistencia a JOIN cursos c ON a.curso_id=c.id
    WHERE a.estado='ausente' AND a.fecha >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY c.id ORDER BY ausencias DESC LIMIT 5
");

// Últimas noticias
$noticias = $conn->query("SELECT n.*,u.nombre autor FROM noticias n JOIN usuarios u ON n.autor_id=u.id WHERE n.activo=1 ORDER BY n.created_at DESC LIMIT 4");

// Actividad reciente
$logs = $conn->query("SELECT al.*,u.nombre,u.rol FROM actividad_log al JOIN usuarios u ON al.usuario_id=u.id ORDER BY al.fecha DESC LIMIT 10");

// Distribución de roles
$roles_dist = $conn->query("SELECT rol, COUNT(*) c FROM usuarios WHERE activo=1 GROUP BY rol")->fetch_all(MYSQLI_ASSOC);

$conn->close();

$hora = (int)date('H');
$saludo = $hora < 12 ? 'Buenos días' : ($hora < 19 ? 'Buenas tardes' : 'Buenas noches');
$tipos_color = ['general'=>'#6b7280','urgente'=>'#ef4444','evento'=>'#3b82f6','comunicado'=>'#f59e0b'];
$log_icons   = ['login'=>'🔐','editar_perfil'=>'👤','cambio_password'=>'🔑','crear_usuario'=>'➕','editar_usuario'=>'✏️','eliminar_usuario'=>'🗑️','crear_curso'=>'📚','editar_curso'=>'✏️','asistencia'=>'✅','registrar_nota'=>'📊','noticia'=>'📰'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Dashboard — Dirección</title>
</head>
<body>
<?php include_sidebar('director','dashboard'); global_css("
.welcome-banner{background:linear-gradient(135deg,rgba(30,64,175,.4),rgba(29,78,216,.2));border:1px solid rgba(59,130,246,.3);border-radius:16px;padding:22px 28px;margin-bottom:22px;display:flex;justify-content:space-between;align-items:center;}
.welcome-banner h2{font-size:20px;color:#fff;margin-bottom:4px;}
.welcome-banner p{font-size:13px;color:rgba(255,255,255,.5);}
.welcome-banner .date-box{text-align:right;color:rgba(255,255,255,.5);font-size:13px;}
.welcome-banner .date-box strong{display:block;font-size:22px;color:#93c5fd;}
.quick-actions{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:22px;}
.qa-btn{display:flex;align-items:center;gap:8px;padding:10px 16px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:10px;color:rgba(255,255,255,.75);text-decoration:none;font-size:13px;transition:all .2s;}
.qa-btn:hover{background:rgba(255,255,255,.12);color:#fff;transform:translateY(-1px);}
"); ?>
<div class="main">

    <!-- Banner bienvenida -->
    <div class="welcome-banner">
        <div>
            <h2><?=$saludo?>, <?=htmlspecialchars($u['nombre'])?> <?=$u['avatar']?></h2>
            <p>Panel de Dirección · Año Escolar 2025 · Colegio Sistema</p>
        </div>
        <div class="date-box">
            <strong><?=date('d')?></strong>
            <?=strftime('%B %Y')?>
            <br><?=date('l')?>
        </div>
    </div>

    <!-- Acciones rápidas -->
    <div class="quick-actions">
        <a href="usuarios.php" class="qa-btn">➕ Nuevo Usuario</a>
        <a href="cursos.php" class="qa-btn">📚 Nuevo Curso</a>
        <a href="noticias.php" class="qa-btn">📢 Publicar Noticia</a>
        <a href="reportes.php" class="qa-btn">📄 Generar Reporte</a>
        <a href="../logs/actividad.php" class="qa-btn">📋 Ver Actividad</a>
    </div>

    <!-- Stats principales -->
    <div class="grid4" style="margin-bottom:16px">
        <?php foreach ([
            ['📚','estudiantes','Estudiantes','#0369a1'],
            ['👨‍🏫','profesores','Profesores','#047857'],
            ['🔧','auxiliares','Auxiliares','#6b7280'],
            ['👨‍👧','apoderados','Apoderados','#7c3aed'],
        ] as [$ico,$key,$label,$color]): ?>
        <div class="stat-card" style="border-top:3px solid <?=$color?>">
            <div class="s-icon"><?=$ico?></div>
            <div class="s-num"><?=$stats[$key]?></div>
            <div class="s-label"><?=$label?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="grid4" style="margin-bottom:22px">
        <div class="stat-card" style="border-top:3px solid #f59e0b">
            <div class="s-icon">🏫</div><div class="s-num"><?=$stats['cursos']?></div><div class="s-label">Cursos activos</div>
        </div>
        <div class="stat-card" style="border-top:3px solid #10b981">
            <div class="s-icon">📰</div><div class="s-num"><?=$stats['noticias']?></div><div class="s-label">Noticias activas</div>
        </div>
        <div class="stat-card" style="border-top:3px solid #3b82f6">
            <div class="s-icon">📊</div><div class="s-num"><?=$stats['notas_hoy']?></div><div class="s-label">Notas ingresadas hoy</div>
        </div>
        <div class="stat-card" style="border-top:3px solid #a855f7">
            <div class="s-icon">✅</div><div class="s-num"><?=$stats['asist_hoy']?></div><div class="s-label">Registros asistencia hoy</div>
        </div>
    </div>

    <!-- Asistencia hoy + Promedio colegio -->
    <div class="grid2" style="margin-bottom:22px">
        <div class="card">
            <div class="card-title">✅ Asistencia global hoy</div>
            <?php if ($total_asist > 0): ?>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                <span style="font-size:13px;color:rgba(255,255,255,.5)">Porcentaje de presencia</span>
                <span style="font-size:24px;font-weight:700;color:<?=$pct_asist>=85?'#10b981':($pct_asist>=75?'#f59e0b':'#ef4444')?>"><?=$pct_asist?>%</span>
            </div>
            <div style="background:rgba(255,255,255,.08);border-radius:20px;height:10px;overflow:hidden;margin-bottom:16px">
                <div style="height:100%;width:<?=$pct_asist?>%;background:<?=$pct_asist>=85?'#10b981':($pct_asist>=75?'#f59e0b':'#ef4444')?>;border-radius:20px"></div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
                <?php foreach ([['presente','🟢','#10b981'],['ausente','🔴','#ef4444'],['justificado','🟡','#f59e0b'],['tardanza','🟠','#f97316']] as [$es,$ico,$ec]): ?>
                <div style="background:<?=$ec?>14;border:1px solid <?=$ec?>33;border-radius:9px;padding:8px 12px;display:flex;justify-content:space-between;align-items:center">
                    <span style="font-size:12px;color:rgba(255,255,255,.5)"><?=$ico?> <?=ucfirst($es)?></span>
                    <span style="font-weight:700;color:<?=$ec?>"><?=$asist_map[$es]?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p style="color:rgba(255,255,255,.3);font-size:13px">Sin registros de asistencia hoy.</p>
            <?php endif; ?>
        </div>

        <div class="card">
            <div class="card-title">📊 Indicadores académicos</div>
            <div style="text-align:center;padding:16px 0;border-bottom:1px solid rgba(255,255,255,.07);margin-bottom:16px">
                <div style="font-size:13px;color:rgba(255,255,255,.45);margin-bottom:6px">Promedio general del colegio</div>
                <div style="font-size:48px;font-weight:800;color:<?=nota_color($prom_colegio)?>"><?=$prom_colegio?:'-'?></div>
                <div style="font-size:12px;color:rgba(255,255,255,.3);margin-top:4px">Sobre todas las evaluaciones registradas</div>
            </div>
            <div>
                <div style="font-size:12px;color:rgba(255,255,255,.35);margin-bottom:10px">DISTRIBUCIÓN DE USUARIOS</div>
                <?php
                $rol_labels = ['director'=>['🎓','#1e40af'],'profesor'=>['👨‍🏫','#047857'],'auxiliar'=>['🔧','#6b7280'],'estudiante'=>['📚','#0369a1'],'apoderado'=>['👨‍👧','#7c3aed']];
                $total_users = array_sum(array_column($roles_dist,'c'));
                foreach ($roles_dist as $rd):
                    [$ico,$color] = $rol_labels[$rd['rol']] ?? ['👤','#888'];
                    $pct_r = $total_users > 0 ? round($rd['c']/$total_users*100) : 0;
                ?>
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px">
                    <span style="font-size:14px;width:20px"><?=$ico?></span>
                    <span style="font-size:12px;color:rgba(255,255,255,.55);width:80px"><?=ucfirst($rd['rol'])?></span>
                    <div style="flex:1;background:rgba(255,255,255,.07);border-radius:20px;height:7px;overflow:hidden">
                        <div style="height:100%;width:<?=$pct_r?>%;background:<?=$color?>;border-radius:20px"></div>
                    </div>
                    <span style="font-size:12px;font-weight:700;color:#fff;width:20px;text-align:right"><?=$rd['c']?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Cursos con más ausencias + Noticias -->
    <div class="grid2" style="margin-bottom:22px">
        <div class="card">
            <div class="card-title">⚠️ Cursos con más ausencias (últimos 30 días)</div>
            <?php $cnt=0; while ($ca = $cursos_ausencias->fetch_assoc()): $cnt++; ?>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid rgba(255,255,255,.05)">
                <span style="font-size:14px;color:#fff">📚 <?=htmlspecialchars($ca['nombre'])?></span>
                <span style="background:rgba(239,68,68,.15);color:#f87171;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700"><?=$ca['ausencias']?> ausencias</span>
            </div>
            <?php endwhile;
            if (!$cnt): ?><p style="color:rgba(255,255,255,.3);font-size:13px">Sin ausencias registradas.</p><?php endif; ?>
            <a href="../profesor/asistencia.php" class="btn btn-gray btn-sm" style="margin-top:14px">Ver asistencia completa</a>
        </div>

        <div class="card">
            <div class="card-title">📰 Últimas noticias publicadas</div>
            <?php while ($n = $noticias->fetch_assoc()):
                $tc = $tipos_color[$n['tipo']] ?? '#888';
            ?>
            <div style="padding:10px 0;border-bottom:1px solid rgba(255,255,255,.06)">
                <div style="display:flex;align-items:center;gap:7px;margin-bottom:4px">
                    <span style="background:<?=$tc?>22;color:<?=$tc?>;padding:2px 8px;border-radius:20px;font-size:11px"><?=ucfirst($n['tipo'])?></span>
                    <span style="font-size:11px;color:rgba(255,255,255,.3)"><?=date('d/m/Y',strtotime($n['created_at']))?></span>
                </div>
                <div style="font-size:13.5px;color:#fff"><?=htmlspecialchars($n['titulo'])?></div>
                <div style="font-size:11px;color:rgba(255,255,255,.35)">Por <?=htmlspecialchars($n['autor'])?></div>
            </div>
            <?php endwhile; ?>
            <a href="noticias.php" class="btn btn-primary btn-sm" style="margin-top:14px">Gestionar noticias</a>
        </div>
    </div>

    <!-- Actividad reciente -->
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
            <div class="card-title" style="margin:0">📋 Actividad reciente del sistema</div>
            <a href="../logs/actividad.php" class="btn btn-sm btn-gray">Ver todo</a>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
            <?php while ($l = $logs->fetch_assoc()):
                $ic = $log_icons[$l['accion']] ?? '📌';
                $rc_map = ['director'=>'#1e40af','profesor'=>'#047857','auxiliar'=>'#6b7280','estudiante'=>'#0369a1','apoderado'=>'#7c3aed'];
                $rc = $rc_map[$l['rol']] ?? '#888';
            ?>
            <div style="display:flex;gap:10px;align-items:flex-start;padding:10px;background:rgba(255,255,255,.03);border-radius:9px;border:1px solid rgba(255,255,255,.05)">
                <span style="font-size:18px;flex-shrink:0"><?=$ic?></span>
                <div style="overflow:hidden">
                    <div style="font-size:13px;color:#fff;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?=htmlspecialchars(str_replace('_',' ',ucfirst($l['accion'])))?></div>
                    <div style="font-size:11px;color:rgba(255,255,255,.35)"><?=htmlspecialchars($l['nombre'])?> · <span style="color:<?=$rc?>"><?=ucfirst($l['rol'])?></span></div>
                    <div style="font-size:11px;color:rgba(255,255,255,.25)"><?=date('d/m H:i',strtotime($l['fecha']))?></div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>
</body>
</html>
