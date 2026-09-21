<?php require_once __DIR__.'/../core.php'; require_once __DIR__.'/auth.php'; require_role('gea_tv');
$sched=read_json('geah_schedule.json',[]);
if($_SERVER['REQUEST_METHOD']==='POST'){
  $action=$_POST['action']??'';
  if($action==='add'){
    $sched[]=[
      'id'=>time().random_int(10,99),
      'title'=>trim($_POST['title']??''),
      'category'=>trim($_POST['category']??'Émission'),
      'url'=>trim($_POST['url']??''),
      'image_url'=>trim($_POST['image_url']??''),
      'slide_text'=>trim($_POST['slide_text']??''),
      'start'=>$_POST['start']??'',
      'duration_min'=>(int)($_POST['duration_min']??60),
      'repeat'=>$_POST['repeat']??'once',
      'priority'=>(int)($_POST['priority']??0),
      'voice'=>isset($_POST['voice']),
      'date'=>now()
    ];
  } elseif($action==='del'){
    $sched=array_values(array_filter($sched,fn($x)=>($x['id']??0)!=(int)$_POST['id']));
  }
  write_json('geah_schedule.json',$sched); header('Location:/admin/tv-programme-pro.php?saved=1'); exit;
}
usort($sched,fn($a,$b)=>strtotime($a['start']??'0')-strtotime($b['start']??'0'));
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>GEA-H TV Studio Pro</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head><body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main">
<h1>📺 GEA-H TV — Programmation Pro</h1>
<p class="status ok">Cette page transforme GEA-H TV en chaîne Internet : émissions, interviews, reportages, documentaires, promotions, séries et directs programmés.</p>
<?php if(isset($_GET['saved'])) echo '<p class="status ok">✅ Programmation enregistrée.</p>'; ?>
<div class="grid-3">
  <a class="card" href="/admin/geah-tv.php"><h3>Bibliothèque vidéo</h3><p>Importer vidéo ou ajouter lien.</p></a>
  <a class="card" href="/admin/tv-ai-studio.php"><h3>Studio IA</h3><p>Créer scripts, séries et présentations IA.</p></a>
  <a class="card" href="/admin/tv-live-studio.php"><h3>Direct / Live</h3><p>Configurer reportage en direct.</p></a>
</div>
<form class="card" method="post">
  <input type="hidden" name="action" value="add">
  <h2>Programmer un contenu</h2>
  <div class="form-grid">
    <label>Titre<input name="title" required placeholder="Ex: Interview du DG, Reportage chantier, Série Investissement #1"></label>
    <label>Catégorie<select name="category"><option>Émission</option><option>Interview</option><option>Reportage</option><option>Documentaire</option><option>Promotion</option><option>Publicité</option><option>Série</option><option>Musique</option><option>Live</option><option>Formation</option></select></label>
    <label>Lien vidéo / live / musique<input name="url" placeholder="MP4, YouTube, Facebook, Vimeo, HLS .m3u8, Cloudinary..."></label>
    <label>Image affiche / slide<input name="image_url" placeholder="Image promotionnelle optionnelle"></label>
    <label>Date et heure<input type="datetime-local" name="start" required></label>
    <label>Durée (minutes)<input type="number" name="duration_min" value="30" min="1"></label>
    <label>Répétition<select name="repeat"><option value="once">Une seule fois</option><option value="daily">Tous les jours à cette heure</option><option value="weekly">Chaque semaine même jour/heure</option></select></label>
    <label>Priorité<input type="number" name="priority" value="0"><small>Plus le chiffre est haut, plus le programme passe en priorité.</small></label>
    <label class="full">Texte slide / script IA<textarea name="slide_text" rows="5" placeholder="Si pas de lien vidéo, GEA-H TV affichera ce texte comme émission slide avec voix navigateur si activée."></textarea></label>
    <label class="full"><input type="checkbox" name="voice" checked> Faire lire le texte par la voix du navigateur lorsque c'est un slide</label>
  </div>
  <button class="btn btn-primary">Programmer sur GEA-H TV</button>
</form>
<h2>Grille complète</h2>
<table class="table"><tr><th>Date/heure</th><th>Programme</th><th>Catégorie</th><th>Durée</th><th>Répétition</th><th>État</th><th></th></tr>
<?php if(!$sched): ?><tr><td colspan="7">Aucun programme.</td></tr><?php endif; ?>
<?php foreach($sched as $g): $st=strtotime($g['start']??'0'); $dur=(int)($g['duration_min']??60); $end=$st+$dur*60; $now=time(); $etat=($now>=$st && $now<=$end)?'🔴 EN COURS':($now<$st?'🕒 À venir':'Terminé / répétition'); ?>
<tr><td><?=e($st?date('d/m/Y H:i',$st):'—')?></td><td><?=e($g['title']??'')?></td><td><?=e($g['category']??'')?></td><td><?=e($dur)?> min</td><td><?=e($g['repeat']??'once')?></td><td><?=$etat?></td><td><form method="post" onsubmit="return confirm('Supprimer ce programme ?')"><input type="hidden" name="action" value="del"><button class="btn danger" name="id" value="<?=e($g['id'])?>">Supprimer</button></form></td></tr>
<?php endforeach; ?></table>
</main></div></body></html>
