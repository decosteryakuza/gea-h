<?php require_once __DIR__.'/../core.php'; require_once __DIR__.'/auth.php'; require_role('gea_tv');
$sched=read_json('geah_schedule.json',[]);
if($_SERVER['REQUEST_METHOD']==='POST'){
    if(($_POST['action']??'')==='add'){
        $sched[]=['id'=>time(),'title'=>trim($_POST['title']??''),'url'=>trim($_POST['url']??''),'start'=>$_POST['start']??'','duration_min'=>(int)($_POST['duration_min']??60),'date'=>now()];
    } elseif(($_POST['action']??'')==='del'){
        $sched=array_values(array_filter($sched,fn($x)=>($x['id']??0)!=(int)$_POST['id']));
    }
    write_json('geah_schedule.json',$sched); header('Location:/admin/tv-programme.php?saved=1'); exit;
}
usort($sched,fn($a,$b)=>strtotime($a['start']??'0')-strtotime($b['start']??'0'));
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Programmation TV</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main">
<h1>Programmation des émissions (grille TV)</h1>
<?php if(isset($_GET['saved'])) echo '<p class="status ok">✅ Enregistré</p>'; ?>
<form class="card" method="post">
  <input type="hidden" name="action" value="add">
  <h2>Programmer une émission</h2>
  <div class="form-grid">
    <label>Titre de l'émission<input name="title" required placeholder="Ex: Journal immobilier"></label>
    <label>Lien vidéo (YouTube, Facebook public, MP4...)<input name="url" required placeholder="https://..."></label>
    <label>Date et heure de diffusion<input type="datetime-local" name="start" required></label>
    <label>Durée (minutes)<input type="number" name="duration_min" value="60" min="1"></label>
  </div>
  <button class="btn btn-primary">Programmer</button>
  <p style="color:#6b7280;font-size:13px;margin-top:6px">À l'heure prévue, cette émission passe en priorité sur GEA-H TV (accueil) pendant sa durée. En dehors des programmes, la TV enchaîne les vidéos en boucle.</p>
</form>

<h2>Grille programmée</h2>
<table class="table"><tr><th>Date / heure</th><th>Émission</th><th>Durée</th><th>État</th><th></th></tr>
<?php if(!$sched): ?><tr><td colspan="5">Aucune émission programmée.</td></tr><?php endif; ?>
<?php foreach($sched as $g): $st=strtotime($g['start']??'0'); $end=$st+((int)($g['duration_min']??60))*60; $now=time();
  $etat = ($now>=$st && $now<=$end) ? '🔴 EN COURS' : ($now<$st ? '🕒 À venir' : 'Terminé'); ?>
  <tr><td><?=e($st?date('d/m/Y H:i',$st):'—')?></td><td><?=e($g['title'])?></td><td><?=e($g['duration_min'])?> min</td><td><?=$etat?></td>
  <td><form method="post" onsubmit="return confirm('Supprimer ?')"><input type="hidden" name="action" value="del"><button class="btn danger" name="id" value="<?=e($g['id'])?>">✕</button></form></td></tr>
<?php endforeach; ?>
</table>
</main></div></body></html>
