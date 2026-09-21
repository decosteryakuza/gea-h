<?php require_once __DIR__.'/../core.php'; require_once __DIR__.'/auth.php';
$visits=read_json('visits.json',[]); $msg='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $i=(int)($_POST['i']??-1); $act=$_POST['action']??'';
  $rev=array_reverse($visits,true);
  if($act==='status' && isset($visits[$i])){ $visits[$i]['status']=$_POST['status']??'Nouveau'; write_json('visits.json',$visits); $msg='✅ Statut mis à jour.'; }
  elseif($act==='del' && isset($visits[$i])){ array_splice($visits,$i,1); write_json('visits.json',$visits); $msg='✅ Demande supprimée.'; }
}
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Visites & Réservations</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main">
<h1>📅 Visites &amp; Réservations</h1>
<?php if($msg) echo '<p class="status ok">'.e($msg).'</p>'; ?>
<?php if(!$visits): ?><div class="card"><p>Aucune demande pour le moment.</p></div><?php endif; ?>
<?php for($i=count($visits)-1;$i>=0;$i--): $v=$visits[$i]; $isRes=($v['mode']??'')==='reservation'; ?>
  <div class="card" style="border-left:4px solid <?=$isRes?'#7c3aed':'#d4a23a'?>">
    <div style="display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap">
      <h3 style="margin:0"><?=$isRes?'🔑 Réservation':'📅 Visite'?> — <?=e($v['property']??'Bien')?>
        <span class="status <?=($v['status']??'')==='Traité'?'ok':'warn'?>" style="font-size:12px"><?=e($v['status']??'Nouveau')?></span></h3>
      <form method="post" onsubmit="return confirm('Supprimer ?')"><input type="hidden" name="action" value="del"><input type="hidden" name="i" value="<?=$i?>"><button class="btn danger">✕</button></form>
    </div>
    <p style="margin:6px 0;color:var(--muted);font-size:13px"><?=e($v['created']??'')?></p>
    <p style="margin:2px 0"><b>👤 <?=e($v['name']??'')?></b> · 📞 <?=e($v['phone']??'')?><?php if(!empty($v['date_pref'])): ?> · 🗓️ <?=e($v['date_pref'])?><?php endif; ?></p>
    <?php if(!empty($v['message'])): ?><p style="margin:2px 0">💬 <?=e($v['message'])?></p><?php endif; ?>
    <form method="post" style="display:flex;gap:6px;align-items:center;margin-top:8px">
      <input type="hidden" name="action" value="status"><input type="hidden" name="i" value="<?=$i?>">
      <select name="status"><?php foreach(['Nouveau','En cours','Traité'] as $st): ?><option <?=($v['status']??'')===$st?'selected':''?>><?=$st?></option><?php endforeach; ?></select>
      <button class="btn btn-light">Mettre à jour</button>
    </form>
  </div>
<?php endfor; ?>
</main></div></body></html>
