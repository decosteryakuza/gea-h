<?php require_once __DIR__.'/../core.php'; require_once __DIR__.'/auth.php'; require_role('lotissement');
$reqs=read_json('lotissement_requests.json',[]);
$msg='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $act=$_POST['action']??''; $id=(int)($_POST['id']??0);
    if($act==='status'){ foreach($reqs as &$r){ if(($r['id']??0)==$id) $r['status']=$_POST['status']??'Nouveau'; } unset($r); write_json('lotissement_requests.json',$reqs); $msg='✅ Statut mis à jour.'; }
    elseif($act==='del'){ $reqs=array_values(array_filter($reqs,fn($r)=>($r['id']??0)!=$id)); write_json('lotissement_requests.json',$reqs); $msg='✅ Demande supprimée.'; }
}
$statuses=['Nouveau','Étudié','Devis envoyé','En cours','Terminé','Refusé'];
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Demandes Lotissement</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main">
<h1>🏗️ Lotissement & Urbanisation — Demandes clients</h1>
<p style="color:var(--muted);margin-top:-6px">Demandes reçues depuis la page publique <b>/modules/lotissement-demande.php</b>.</p>
<?php if($msg) echo '<p class="status '.(strpos($msg,'✅')===0?'ok':'warn').'">'.e($msg).'</p>'; ?>
<?php if(!$reqs): ?><div class="card"><p>Aucune demande pour le moment.</p></div><?php endif; ?>
<?php foreach(array_reverse($reqs) as $r): ?>
  <div class="card">
    <div style="display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap">
      <div><h3 style="margin:0"><?=e($r['service']??'Projet')?> — <?=e($r['name']??'')?></h3>
      <p style="margin:4px 0;color:var(--muted)"><?=e($r['phone']??'')?> <?=e($r['email']?'· '.$r['email']:'')?> <?=e($r['location']?'· '.$r['location']:'')?> · <?=e(substr($r['date']??'',0,10))?></p></div>
      <form method="post"><input type="hidden" name="action" value="status"><input type="hidden" name="id" value="<?=e($r['id'])?>">
      <select name="status" onchange="this.form.submit()"><?php foreach($statuses as $st) echo '<option'.(($r['status']??'')===$st?' selected':'').'>'.e($st).'</option>'; ?></select></form>
    </div>
    <p><?=nl2br(e($r['description']??''))?></p>
    <?php if(!empty($r['files'])): ?><p style="font-size:13px;color:var(--muted)">📎 <?php foreach($r['files'] as $f): ?><a href="<?=e(media_src($f))?>" target="_blank" style="color:var(--gold)"><?=e(basename($f))?></a> <?php endforeach; ?></p><?php endif; ?>
    <div style="display:flex;gap:8px;margin-top:8px">
      <form method="post" style="display:inline" onsubmit="return confirm('Supprimer ?')"><input type="hidden" name="action" value="del"><button class="btn danger" name="id" value="<?=e($r['id'])?>">✕</button></form>
    </div>
  </div>
<?php endforeach; ?>
</main></div></body></html>
