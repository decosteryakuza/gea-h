<?php require_once __DIR__.'/../core.php'; require_once __DIR__.'/auth.php';
$reqs=read_json('startup_requests.json',[]);
$msg='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $act=$_POST['action']??''; $id=(int)($_POST['id']??0);
    if($act==='analyse'){
        foreach($reqs as &$r){ if(($r['id']??0)==$id){
            $prompt="Tu es chef de projet d'une agence numérique en Côte d'Ivoire (prix en FCFA). Analyse cette demande et propose : 1) une estimation de prix réaliste en FCFA (fourchette), 2) un délai de réalisation, 3) les grandes étapes, 4) un court paragraphe de proposition commerciale. Sois concret et professionnel.\n\nService : ".($r['service']??'')."\nDemande : ".($r['description']??'');
            $res=ai_answer($prompt); $r['ai']=$res['answer']??'(Aucune réponse — vérifiez votre clé IA dans API Manager.)'; $r['status']=($r['status']==='Nouveau'?'Analysé':$r['status']);
        }} unset($r); write_json('startup_requests.json',$reqs); $msg='✅ Analyse IA générée.';
    } elseif($act==='status'){ foreach($reqs as &$r){ if(($r['id']??0)==$id) $r['status']=$_POST['status']??'Nouveau'; } unset($r); write_json('startup_requests.json',$reqs); $msg='✅ Statut mis à jour.'; }
    elseif($act==='del'){ $reqs=array_values(array_filter($reqs,fn($r)=>($r['id']??0)!=$id)); write_json('startup_requests.json',$reqs); $msg='✅ Demande supprimée.'; }
}
$statuses=['Nouveau','Analysé','Devis envoyé','En production','Terminé','Refusé'];
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>GEA-H Startup</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main">
<h1>🚀 GEA-H Startup — Demandes</h1>
<p style="color:var(--muted);margin-top:-6px">Demandes de services numériques. Cliquez « Analyser » pour une estimation, un délai et un devis générés par l'IA. Page publique : <b>/modules/startup.php</b></p>
<?php if($msg) echo '<p class="status '.(strpos($msg,'✅')===0?'ok':'warn').'">'.e($msg).'</p>'; ?>
<?php if(!$reqs): ?><div class="card"><p>Aucune demande pour le moment.</p></div><?php endif; ?>
<?php foreach(array_reverse($reqs) as $r): $imgs=geah_property_images($r); ?>
  <div class="card">
    <div style="display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap">
      <div><h3 style="margin:0"><?=e($r['service']??'Projet')?> — <?=e($r['name']??'')?></h3>
      <p style="margin:4px 0;color:var(--muted)"><?=e($r['phone']??'')?> <?=e($r['email']?'· '.$r['email']:'')?> · <?=e(substr($r['date']??'',0,10))?></p></div>
      <form method="post"><input type="hidden" name="action" value="status"><input type="hidden" name="id" value="<?=e($r['id'])?>">
      <select name="status" onchange="this.form.submit()"><?php foreach($statuses as $st) echo '<option'.(($r['status']??'')===$st?' selected':'').'>'.e($st).'</option>'; ?></select></form>
    </div>
    <p><?=nl2br(e($r['description']??''))?></p>
    <?php if(!empty($r['files'])): ?><p style="font-size:13px;color:var(--muted)">📎 <?php foreach($r['files'] as $f): ?><a href="<?=e(media_src($f))?>" target="_blank" style="color:var(--gold)"><?=e(basename($f))?></a> <?php endforeach; ?></p><?php endif; ?>
    <?php if(!empty($r['ai'])): ?><div class="card" style="background:var(--surface2)"><b>🤖 Analyse IA (estimation / délai / devis)</b><div style="white-space:pre-wrap;margin-top:6px;font-size:14px"><?=e($r['ai'])?></div></div><?php endif; ?>
    <div style="display:flex;gap:8px;margin-top:8px">
      <form method="post" style="display:inline"><input type="hidden" name="action" value="analyse"><button class="btn btn-primary" name="id" value="<?=e($r['id'])?>">🤖 Analyser (IA)</button></form>
      <form method="post" style="display:inline" onsubmit="return confirm('Supprimer ?')"><input type="hidden" name="action" value="del"><button class="btn danger" name="id" value="<?=e($r['id'])?>">✕</button></form>
    </div>
  </div>
<?php endforeach; ?>
</main></div></body></html>
