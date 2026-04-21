<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/header.php';
auth_required('director');
$u = current_user();
$conn = getDB();

// Todos los estudiantes
$hijos   = $conn->query("SELECT id,nombre FROM usuarios WHERE rol='estudiante' AND activo=1 ORDER BY nombre")->fetch_all(MYSQLI_ASSOC);
$hijo_sel  = (int)($_GET['hijo']   ?? ($hijos[0]['id'] ?? 0));
$tipo_rep  = $_GET['tipo']   ?? 'completo';
$formato   = $_GET['formato'] ?? '';

$estudiante = $hijo_sel ? $conn->query("SELECT * FROM usuarios WHERE id=$hijo_sel")->fetch_assoc() : null;
$curso = null; $notas_por_asig = []; $asistencias = [];
$resumen_asist = ['presente'=>0,'ausente'=>0,'justificado'=>0,'tardanza'=>0];

if ($estudiante) {
    $sc = $conn->prepare("SELECT c.*,u2.nombre pjefe FROM matriculas m JOIN cursos c ON m.curso_id=c.id LEFT JOIN usuarios u2 ON c.profesor_jefe_id=u2.id WHERE m.estudiante_id=? AND m.año=2025 LIMIT 1");
    $sc->bind_param("i",$hijo_sel); $sc->execute();
    $curso = $sc->get_result()->fetch_assoc();
    if ($curso) {
        $cas = $conn->prepare("SELECT ca.id,a.nombre asig,u3.nombre profesor FROM curso_asignatura ca JOIN asignaturas a ON ca.asignatura_id=a.id JOIN usuarios u3 ON ca.profesor_id=u3.id WHERE ca.curso_id=?");
        $cas->bind_param("i",$curso['id']); $cas->execute();
        foreach ($cas->get_result()->fetch_all(MYSQLI_ASSOC) as $ca) {
            $ns = $conn->prepare("SELECT * FROM notas WHERE estudiante_id=? AND curso_asignatura_id=? ORDER BY fecha");
            $ns->bind_param("ii",$hijo_sel,$ca['id']); $ns->execute();
            $notas = $ns->get_result()->fetch_all(MYSQLI_ASSOC);
            if ($notas) { $prom=round(array_sum(array_column($notas,'nota'))/count($notas),1); $notas_por_asig[]=['asig'=>$ca['asig'],'profesor'=>$ca['profesor'],'notas'=>$notas,'prom'=>$prom]; }
        }
        $sa = $conn->prepare("SELECT * FROM asistencia WHERE estudiante_id=? AND curso_id=? ORDER BY fecha DESC");
        $sa->bind_param("ii",$hijo_sel,$curso['id']); $sa->execute();
        $asistencias = $sa->get_result()->fetch_all(MYSQLI_ASSOC);
        foreach ($asistencias as $a) $resumen_asist[$a['estado']]++;
    }
}
$total_dias   = array_sum($resumen_asist);
$pct_asist    = $total_dias > 0 ? round($resumen_asist['presente']/$total_dias*100) : 0;
$prom_general = $notas_por_asig ? round(array_sum(array_column($notas_por_asig,'prom'))/count($notas_por_asig),1) : 0;
$conn->close();

// ── PDF ──────────────────────────────────────────
if ($formato === 'pdf' && $estudiante) {
    $vendor = '/var/www/html/vendor/autoload.php';
    if (!file_exists($vendor)) die('<p style="font-family:sans-serif;padding:40px;color:red">❌ TCPDF no instalado. Ejecuta: <code>docker-compose exec php composer require tecnickcom/tcpdf phpoffice/phpword</code></p>');
    require_once $vendor;
    $pdf = new TCPDF('P','mm','A4',true,'UTF-8',false);
    $pdf->SetCreator('Sistema Escolar'); $pdf->SetTitle('Reporte '.$estudiante['nombre']);
    $pdf->setPrintHeader(false); $pdf->setPrintFooter(false); $pdf->SetMargins(15,15,15); $pdf->AddPage();
    // Header
    $pdf->SetFillColor(30,64,175); $pdf->Rect(0,0,210,38,'F');
    $pdf->SetFont('helvetica','B',20); $pdf->SetTextColor(255,255,255); $pdf->SetXY(15,7);
    $pdf->Cell(0,12,'🏫 SISTEMA ESCOLAR',0,1,'C');
    $pdf->SetFont('helvetica','B',14); $pdf->SetXY(15,20);
    $pdf->Cell(0,8,'REPORTE '.strtoupper($tipo_rep==='notas'?'DE NOTAS':($tipo_rep==='asistencia'?'DE ASISTENCIA':'COMPLETO')),0,1,'C');
    $pdf->SetFont('helvetica','',10); $pdf->SetXY(15,30);
    $pdf->Cell(0,7,'Generado el '.date('d/m/Y H:i').' · Dirección del Establecimiento',0,1,'C');
    $pdf->SetTextColor(30,30,30); $pdf->SetY(45);
    // Datos estudiante
    $pdf->SetFillColor(235,245,255); $pdf->SetFont('helvetica','B',12);
    $pdf->Cell(0,8,' DATOS DEL ESTUDIANTE',0,1,'L',true); $pdf->SetFont('helvetica','',10);
    foreach ([['Nombre completo:',$estudiante['nombre']],['Email:',$estudiante['email']],['Curso:',$curso?$curso['nombre'].' — '.$curso['nivel']:'-'],['Profesor Jefe:',$curso['pjefe']??'-'],['Año escolar:','2025'],['Promedio general:',$prom_general?:'-'],['% Asistencia:',$pct_asist.'%']] as [$k,$v]) {
        $pdf->SetFont('helvetica','B',10); $pdf->Cell(55,7,$k,0,0); $pdf->SetFont('helvetica','',10); $pdf->Cell(0,7,$v,0,1);
    }
    $pdf->Ln(4);
    // Notas
    if (in_array($tipo_rep,['notas','completo']) && $notas_por_asig) {
        $pdf->SetFillColor(235,245,255); $pdf->SetFont('helvetica','B',12);
        $pdf->Cell(0,8,' LIBRO DE CALIFICACIONES',0,1,'L',true); $pdf->Ln(2);
        foreach ($notas_por_asig as $asig) {
            $pc = $asig['prom']>=6?[16,185,129]:($asig['prom']>=5?[245,158,11]:($asig['prom']>=4?[249,115,22]:[239,68,68]));
            $pdf->SetFillColor(245,248,255); $pdf->SetFont('helvetica','B',11);
            $pdf->Cell(130,7,' '.$asig['asig'].' · Prof. '.$asig['profesor'],0,0,'L',true);
            $pdf->SetFillColor($pc[0],$pc[1],$pc[2]); $pdf->SetTextColor(255,255,255);
            $pdf->Cell(0,7,'  Promedio: '.$asig['prom'],0,1,'C',true); $pdf->SetTextColor(30,30,30);
            $pdf->SetFont('helvetica','',9);
            foreach ($asig['notas'] as $n) {
                $pdf->SetFillColor(250,252,255); $pdf->Cell(90,5.5,'   '.$n['evaluacion'],0,0,'L',true);
                $pdf->Cell(40,5.5,date('d/m/Y',strtotime($n['fecha'])),0,0,'C');
                $pdf->SetFont('helvetica','B',9); $nc=$n['nota']>=6?[16,185,129]:($n['nota']>=4?[30,30,30]:[239,68,68]); $pdf->SetTextColor($nc[0],$nc[1],$nc[2]);
                $pdf->Cell(0,5.5,'  '.$n['nota'],0,1,'R'); $pdf->SetTextColor(30,30,30); $pdf->SetFont('helvetica','',9);
            }
            $pdf->Ln(2);
        }
        $pdf->SetFillColor(30,64,175); $pdf->SetTextColor(255,255,255); $pdf->SetFont('helvetica','B',13);
        $pdf->Cell(0,9,'  PROMEDIO GENERAL: '.$prom_general,0,1,'R',true); $pdf->SetTextColor(30,30,30); $pdf->Ln(5);
    }
    // Asistencia
    if (in_array($tipo_rep,['asistencia','completo']) && $asistencias) {
        $pdf->SetFillColor(235,245,255); $pdf->SetFont('helvetica','B',12);
        $pdf->Cell(0,8,' REGISTRO DE ASISTENCIA',0,1,'L',true); $pdf->Ln(2);
        $pdf->SetFont('helvetica','',10);
        $pdf->Cell(45,7,'Presentes:',0,0); $pdf->Cell(0,7,$resumen_asist['presente'],0,1);
        $pdf->Cell(45,7,'Ausentes:',0,0);  $pdf->Cell(0,7,$resumen_asist['ausente'],0,1);
        $pdf->Cell(45,7,'Justificados:',0,0); $pdf->Cell(0,7,$resumen_asist['justificado'],0,1);
        $pdf->Cell(45,7,'Tardanzas:',0,0); $pdf->Cell(0,7,$resumen_asist['tardanza'],0,1);
        $pdf->SetFont('helvetica','B',10); $pdf->Cell(45,7,'% Asistencia:',0,0);
        $pdf->SetFont('helvetica','B',12); $acolor=$pct_asist>=85?[16,185,129]:($pct_asist>=75?[245,158,11]:[239,68,68]); $pdf->SetTextColor($acolor[0],$acolor[1],$acolor[2]); $pdf->Cell(0,7,$pct_asist.'%',0,1); $pdf->SetTextColor(30,30,30); $pdf->Ln(4);
        $pdf->SetFont('helvetica','B',10); $pdf->SetFillColor(30,64,175); $pdf->SetTextColor(255,255,255);
        $pdf->Cell(45,7,'Fecha',1,0,'C',true); $pdf->Cell(50,7,'Estado',1,0,'C',true); $pdf->Cell(0,7,'Observación',1,1,'C',true);
        $pdf->SetTextColor(30,30,30); $pdf->SetFont('helvetica','',10);
        $cols=['presente'=>[220,252,231],'ausente'=>[254,226,226],'justificado'=>[254,249,195],'tardanza'=>[255,237,213]];
        foreach (array_slice($asistencias,0,50) as $a) {
            [$r,$g,$b]=$cols[$a['estado']]??[255,255,255]; $pdf->SetFillColor($r,$g,$b);
            $pdf->Cell(45,6,date('d/m/Y',strtotime($a['fecha'])),1,0,'C',true);
            $pdf->Cell(50,6,ucfirst($a['estado']),1,0,'C',true);
            $pdf->Cell(0,6,$a['observacion'],1,1,'L',true);
        }
    }
    // Firma
    $pdf->Ln(10);
    $pdf->SetFont('helvetica','',10); $pdf->SetTextColor(100,100,100);
    $pdf->Cell(0,7,'Documento generado automáticamente por el Sistema de Gestión Escolar · '.date('d/m/Y H:i'),0,1,'C');
    $fname='reporte_director_'.preg_replace('/[^a-zA-Z0-9_]','_',$estudiante['nombre']).'_'.date('Ymd').'.pdf';
    $pdf->Output($fname,'D'); exit();
}

// ── WORD ─────────────────────────────────────────
if ($formato === 'word' && $estudiante) {
    $vendor = '/var/www/html/vendor/autoload.php';
    if (!file_exists($vendor)) die('PhpWord no instalado.');
    require_once $vendor;
    use PhpOffice\PhpWord\PhpWord; use PhpOffice\PhpWord\SimpleType\Jc;
    $word = new PhpWord(); $word->setDefaultFontName('Calibri'); $word->setDefaultFontSize(11);
    $section = $word->addSection(['marginTop'=>800,'marginBottom'=>800,'marginLeft'=>1000,'marginRight'=>1000]);
    $section->addText('🏫 SISTEMA ESCOLAR',['bold'=>true,'size'=>20,'color'=>'1E40AF'],['alignment'=>Jc::CENTER]);
    $section->addText('REPORTE '.strtoupper($tipo_rep==='notas'?'DE NOTAS':($tipo_rep==='asistencia'?'DE ASISTENCIA':'COMPLETO')),['bold'=>true,'size'=>15,'color'=>'1E40AF'],['alignment'=>Jc::CENTER]);
    $section->addText('Generado el '.date('d/m/Y H:i').' · Dirección',['size'=>10,'color'=>'888888'],['alignment'=>Jc::CENTER]);
    $section->addTextBreak(1);
    $section->addText('DATOS DEL ESTUDIANTE',['bold'=>true,'size'=>13,'color'=>'1E40AF']);
    $section->addTextBreak(1);
    $t=$section->addTable(['borderColor'=>'cccccc','borderSize'=>6,'cellMargin'=>80]);
    foreach([['Nombre:',$estudiante['nombre']],['Email:',$estudiante['email']],['Curso:',$curso?$curso['nombre'].' — '.$curso['nivel']:'-'],['Profesor Jefe:',$curso['pjefe']??'-'],['Año escolar:','2025'],['Promedio general:',(string)($prom_general?:'-')],['% Asistencia:',$pct_asist.'%']] as [$k,$v]){
        $r=$t->addRow(); $r->addCell(2500)->addText($k,['bold'=>true,'size'=>10]); $r->addCell(5000)->addText($v,['size'=>10]);
    }
    $section->addTextBreak(2);
    if (in_array($tipo_rep,['notas','completo']) && $notas_por_asig) {
        $section->addText('LIBRO DE CALIFICACIONES',['bold'=>true,'size'=>13,'color'=>'1E40AF']); $section->addTextBreak(1);
        foreach ($notas_por_asig as $asig) {
            $section->addText($asig['asig'].' — Prof. '.$asig['profesor'].'   |   Promedio: '.$asig['prom'],['bold'=>true,'size'=>12]);
            $nt=$section->addTable(['borderColor'=>'dddddd','borderSize'=>4,'cellMargin'=>60]);
            $hr=$nt->addRow(); foreach(['Evaluación','Fecha','Nota'] as $h) $hr->addCell(2500)->addText($h,['bold'=>true,'size'=>10,'color'=>'1E40AF']);
            foreach($asig['notas'] as $n){ $nr=$nt->addRow(); $nr->addCell(2500)->addText($n['evaluacion'],['size'=>10]); $nr->addCell(2500)->addText(date('d/m/Y',strtotime($n['fecha'])),['size'=>10]); $nr->addCell(2500)->addText((string)$n['nota'],['size'=>10,'bold'=>true]); }
            $section->addTextBreak(1);
        }
        $section->addText('PROMEDIO GENERAL: '.$prom_general,['bold'=>true,'size'=>14,'color'=>'1E40AF']); $section->addTextBreak(2);
    }
    if (in_array($tipo_rep,['asistencia','completo']) && $asistencias) {
        $section->addText('REGISTRO DE ASISTENCIA',['bold'=>true,'size'=>13,'color'=>'1E40AF']); $section->addTextBreak(1);
        $section->addText("Presentes: {$resumen_asist['presente']} | Ausentes: {$resumen_asist['ausente']} | Justificados: {$resumen_asist['justificado']} | % Asistencia: {$pct_asist}%",['size'=>11]); $section->addTextBreak(1);
        $at=$section->addTable(['borderColor'=>'dddddd','borderSize'=>4,'cellMargin'=>60]);
        $hr=$at->addRow(); foreach(['Fecha','Estado','Observación'] as $h) $hr->addCell(2500)->addText($h,['bold'=>true,'size'=>10,'color'=>'1E40AF']);
        foreach(array_slice($asistencias,0,50) as $a){ $ar=$at->addRow(); $ar->addCell(2500)->addText(date('d/m/Y',strtotime($a['fecha'])),['size'=>10]); $ar->addCell(2500)->addText(ucfirst($a['estado']),['size'=>10]); $ar->addCell(2500)->addText($a['observacion'],['size'=>10]); }
    }
    $fname='reporte_dir_'.preg_replace('/[^a-zA-Z0-9]','_',$estudiante['nombre']).'_'.date('Ymd').'.docx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header("Content-Disposition: attachment; filename=\"$fname\"");
    $word->save('php://output','Word2007'); exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><title>Reportes — Dirección</title></head>
<body>
<?php include_sidebar('director','reportes'); global_css("
.rep-card{border:1px solid rgba(255,255,255,.08);border-radius:14px;padding:22px;background:rgba(255,255,255,.04);cursor:pointer;transition:all .2s;text-decoration:none;display:block;color:#fff;}
.rep-card:hover{background:rgba(255,255,255,.09);transform:translateY(-2px);}
.rep-card.active{border-color:#3b82f6;background:rgba(59,130,246,.1);}
"); ?>
<div class="main">
    <div class="page-title">📄 Generador de Reportes</div>
    <div class="page-sub">Genera reportes PDF o Word para cualquier estudiante del colegio</div>

    <!-- Selector estudiante -->
    <div class="card" style="margin:18px 0">
        <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
            <div class="form-group" style="margin:0;flex:1;min-width:260px">
                <label>Seleccionar Estudiante</label>
                <select name="hijo" onchange="this.form.submit()">
                    <option value="">— Seleccionar —</option>
                    <?php foreach ($hijos as $h): ?><option value="<?=$h['id']?>" <?=$h['id']==$hijo_sel?'selected':''?>><?=htmlspecialchars($h['nombre'])?></option><?php endforeach; ?>
                </select>
            </div>
            <input type="hidden" name="tipo" value="<?=$tipo_rep?>">
        </form>
    </div>

    <?php if ($estudiante): ?>
    <!-- Info estudiante -->
    <div style="background:rgba(30,64,175,.12);border:1px solid rgba(30,64,175,.3);border-radius:14px;padding:18px 22px;margin-bottom:22px">
        <div style="display:flex;gap:16px;align-items:center">
            <span style="font-size:40px">📚</span>
            <div style="flex:1">
                <div style="font-size:17px;color:#fff;font-weight:700"><?=htmlspecialchars($estudiante['nombre'])?></div>
                <div style="font-size:13px;color:rgba(255,255,255,.45);margin-top:3px">
                    <?=$curso?$curso['nombre'].' · '.$curso['nivel']:'Sin curso asignado'?>
                    <?php if($curso&&$curso['pjefe']): ?> · Prof. Jefe: <?=htmlspecialchars($curso['pjefe'])?><?php endif;?>
                </div>
            </div>
            <div style="display:flex;gap:16px">
                <div style="text-align:center">
                    <div style="font-size:24px;font-weight:800;color:<?=nota_color($prom_general)?>"><?=$prom_general?:'-'?></div>
                    <div style="font-size:11px;color:rgba(255,255,255,.35)">Promedio</div>
                </div>
                <div style="text-align:center">
                    <div style="font-size:24px;font-weight:800;color:<?=$pct_asist>=85?'#10b981':($pct_asist>=75?'#f59e0b':'#ef4444')?>"><?=$pct_asist?>%</div>
                    <div style="font-size:11px;color:rgba(255,255,255,.35)">Asistencia</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tipo de reporte -->
    <div style="font-size:13px;color:rgba(255,255,255,.5);margin-bottom:12px;font-weight:600">SELECCIONA EL TIPO DE REPORTE:</div>
    <div class="grid3" style="margin-bottom:24px">
        <?php foreach(['notas'=>['📊','Solo Notas','Todas las calificaciones y promedios por asignatura'],'asistencia'=>['✅','Solo Asistencia','Historial completo de asistencia con porcentaje'],'completo'=>['📋','Reporte Completo','Notas + Asistencia + Datos del estudiante']] as $tv=>[$ico,$tl,$desc]): ?>
        <a href="?hijo=<?=$hijo_sel?>&tipo=<?=$tv?>" class="rep-card <?=$tipo_rep===$tv?'active':''?>">
            <div style="font-size:36px;margin-bottom:10px"><?=$ico?></div>
            <div style="font-size:15px;font-weight:700;margin-bottom:5px"><?=$tl?></div>
            <div style="font-size:12px;color:rgba(255,255,255,.45);line-height:1.5"><?=$desc?></div>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Botones de descarga -->
    <div class="card">
        <div class="card-title">⬇️ Descargar — <span style="color:#93c5fd"><?=$tipo_rep==='notas'?'Solo Notas':($tipo_rep==='asistencia'?'Solo Asistencia':'Reporte Completo')?></span></div>
        <div style="display:flex;gap:16px;flex-wrap:wrap;margin-top:4px">
            <a href="?hijo=<?=$hijo_sel?>&tipo=<?=$tipo_rep?>&formato=pdf" class="btn btn-primary" style="font-size:15px;padding:14px 28px">
                📕 Descargar PDF
            </a>
            <a href="?hijo=<?=$hijo_sel?>&tipo=<?=$tipo_rep?>&formato=word" class="btn btn-green" style="font-size:15px;padding:14px 28px">
                📘 Descargar Word (.docx)
            </a>
        </div>
        <div style="margin-top:14px;padding:12px;background:rgba(255,255,255,.03);border-radius:9px;font-size:12px;color:rgba(255,255,255,.3)">
            💡 Si es la primera vez o hay error al descargar, instala las librerías con:<br>
            <code style="background:rgba(255,255,255,.08);padding:3px 8px;border-radius:5px;display:inline-block;margin-top:4px">docker-compose exec php composer require tecnickcom/tcpdf phpoffice/phpword</code>
        </div>
    </div>
    <?php else: ?>
    <div class="alert alert-info">👆 Selecciona un estudiante para ver las opciones de reporte.</div>
    <?php endif; ?>
</div>
</body>
</html>
