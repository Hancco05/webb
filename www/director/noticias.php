<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/header.php';
auth_required('director');
$u = current_user();
$conn = getDB();

if (isset($_GET['del'])) {
    $conn->query("UPDATE noticias SET activo=0 WHERE id=".(int)$_GET['del']);
    header("Location: noticias.php?ok=1"); exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo    = trim($_POST['titulo']);
    $contenido = trim($_POST['contenido']);
    $tipo      = $_POST['tipo'];
    $para      = implode(',', $_POST['visible_para'] ?? ['director','profesor','auxiliar','estudiante','apoderado']);
    $eid       = (int)($_POST['edit_id'] ?? 0);

    if ($eid) {
        $s = $conn->prepare("UPDATE noticias SET titulo=?,contenido=?,tipo=?,visible_para=? WHERE id=?");
        $s->bind_param("ssssi",$titulo,$contenido,$tipo,$para,$eid);
    } else {
        $aid = $u['id'];
        $s = $conn->prepare("INSERT INTO noticias (titulo,contenido,autor_id,tipo,visible_para) VALUES (?,?,?,?,?)");
        $s->bind_param("ssiss",$titulo,$contenido,$aid,$tipo,$para);
    }
    $s->execute(); $s->close();
    log_actividad($conn,$u['id'],'noticia',"Publicó/editó noticia: $titulo");
    header("Location: noticias.php?ok=1"); exit();
}

$noticias = $conn->query("SELECT n.*,u.nombre autor FROM noticias n JOIN usuarios u ON n.autor_id=u.id WHERE n.activo=1 ORDER BY n.created_at DESC");
$editar = null;
if (isset($_GET['edit'])) $editar = $conn->query("SELECT * FROM noticias WHERE id=".(int)$_GET['edit'])->fetch_assoc();
$conn->close();

$tipos = ['general'=>['#6b7280','📢'],'urgente'=>['#ef4444','⚠️'],'evento'=>['#3b82f6','📅'],'comunicado'=>['#f59e0b','📋']];
$roles_vis = ['director'=>'🎓 Director','profesor'=>'👨‍🏫 Profesor','auxiliar'=>'🔧 Auxiliar','estudiante'=>'📚 Estudiante','apoderado'=>'👨‍👧 Apoderado'];
?>
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Noticias</title></head>
<body>
<?php include_sidebar('director','noticias'); global_css(".form-modal{position:fixed;inset:0;background:rgba(0,0,0,.65);display:flex;align-items:center;justify-content:center;z-index:999}.modal-box{background:#1a1f2e;border:1px solid rgba(255,255,255,.12);border-radius:16px;padding:28px;width:560px;max-height:90vh;overflow-y:auto}"); ?>
<div class="main">
    <div class="topbar">
        <div>
            <div class="page-title">📰 Noticias y Comunicados</div>
            <div class="page-sub">Gestiona la comunicación con toda la comunidad escolar</div>
        </div>
        <button class="btn btn-primary" onclick="document.getElementById('m-crear').style.display='flex'">+ Nueva Noticia</button>
    </div>
    <?php if (isset($_GET['ok'])): ?><div class="alert alert-success">Operación realizada.</div><?php endif; ?>

    <div style="display:flex;flex-direction:column;gap:14px">
        <?php while ($n = $noticias->fetch_assoc()):
            [$tc,$tico] = $tipos[$n['tipo']] ?? ['#888','📄'];
        ?>
        <div class="card" style="border-left:4px solid <?=$tc?>">
            <div style="display:flex;justify-content:space-between;align-items:flex-start">
                <div style="flex:1">
                    <div style="display:flex;gap:8px;align-items:center;margin-bottom:8px">
                        <span style="background:<?=$tc?>22;color:<?=$tc?>;padding:3px 10px;border-radius:20px;font-size:12px"><?=$tico?> <?=ucfirst($n['tipo'])?></span>
                        <span style="font-size:12px;color:rgba(255,255,255,.35)"><?=date('d/m/Y H:i',strtotime($n['created_at']))?></span>
                        <span style="font-size:12px;color:rgba(255,255,255,.35)">por <?=htmlspecialchars($n['autor'])?></span>
                    </div>
                    <div style="font-size:16px;color:#fff;font-weight:600;margin-bottom:6px"><?=htmlspecialchars($n['titulo'])?></div>
                    <div style="font-size:13px;color:rgba(255,255,255,.55);line-height:1.6"><?=nl2br(htmlspecialchars(substr($n['contenido'],0,200)))?>...</div>
                    <div style="margin-top:8px;font-size:11px;color:rgba(255,255,255,.3)">Visible para: <?=$n['visible_para']?></div>
                </div>
                <div style="display:flex;flex-direction:column;gap:6px;margin-left:14px">
                    <a href="?edit=<?=$n['id']?>" class="btn btn-sm btn-gray">✏️ Editar</a>
                    <a href="?del=<?=$n['id']?>" class="btn btn-sm btn-red" onclick="return confirm('¿Archivar noticia?')">🗑️</a>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- MODAL CREAR -->
<div id="m-crear" class="form-modal" style="display:none" onclick="if(event.target===this)this.style.display='none'">
<div class="modal-box">
    <h3 style="color:#fff;margin-bottom:18px">📢 Nueva Noticia</h3>
    <form method="POST">
        <input type="hidden" name="edit_id" value="0">
        <div class="form-group"><label>Título *</label><input name="titulo" required></div>
        <div class="form-group"><label>Tipo</label>
            <select name="tipo">
                <?php foreach ($tipos as $tv=>$_): ?><option value="<?=$tv?>"><?=ucfirst($tv)?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="form-group"><label>Contenido *</label><textarea name="contenido" style="min-height:120px" required></textarea></div>
        <div class="form-group"><label>Visible para</label>
            <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:6px">
            <?php foreach ($roles_vis as $rv=>$rl): ?>
                <label style="display:flex;align-items:center;gap:6px;font-size:13px;color:rgba(255,255,255,.6);cursor:pointer">
                    <input type="checkbox" name="visible_para[]" value="<?=$rv?>" checked> <?=$rl?>
                </label>
            <?php endforeach; ?>
            </div>
        </div>
        <div style="display:flex;gap:10px">
            <button class="btn btn-primary">📢 Publicar</button>
            <button type="button" class="btn btn-gray" onclick="document.getElementById('m-crear').style.display='none'">Cancelar</button>
        </div>
    </form>
</div>
</div>

<?php if ($editar): ?>
<div class="form-modal" style="display:flex" onclick="if(event.target===this)location.href='noticias.php'">
<div class="modal-box">
    <h3 style="color:#fff;margin-bottom:18px">✏️ Editar Noticia</h3>
    <form method="POST">
        <input type="hidden" name="edit_id" value="<?=$editar['id']?>">
        <div class="form-group"><label>Título</label><input name="titulo" value="<?=htmlspecialchars($editar['titulo'])?>" required></div>
        <div class="form-group"><label>Tipo</label>
            <select name="tipo">
                <?php foreach ($tipos as $tv=>$_): ?><option value="<?=$tv?>" <?=$editar['tipo']===$tv?'selected':''?>><?=ucfirst($tv)?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="form-group"><label>Contenido</label><textarea name="contenido" style="min-height:120px"><?=htmlspecialchars($editar['contenido'])?></textarea></div>
        <div style="display:flex;gap:10px">
            <button class="btn btn-primary">💾 Guardar</button>
            <a href="noticias.php" class="btn btn-gray">Cancelar</a>
        </div>
    </form>
</div>
</div>
<?php endif; ?>
</body></html>
