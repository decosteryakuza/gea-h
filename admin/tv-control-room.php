<?php require_once __DIR__.'/../core.php'; require_once __DIR__.'/auth.php'; require_role('gea_tv');
$live=read_json('geah_live.json',['active'=>false,'title'=>'','url'=>'','slide_text'=>'','image_url'=>'','category'=>'INFO','duration_sec'=>45,'voice'=>true,'expires_at'=>'']);
if($_SERVER['REQUEST_METHOD']==='POST'){
  $action=$_POST['action']??'';
  if($action==='stop'){
    $live['active']=false; $live['stopped_at']=now(); write_json('geah_live.json',$live);
    header('Location:/admin/tv-control-room.php?stopped=1'); exit;
  }
  if($action==='interrupt'){
    $duration=max(15,(int)($_POST['duration_sec']??60));
    $expires=date('Y-m-d H:i:s', time()+$duration);
    $live=[
      'active'=>true,
      'title'=>trim($_POST['title']??'Information GEA-H TV'),
      'category'=>trim($_POST['category']??'INFO'),
      'url'=>trim($_POST['url']??''),
      'slide_text'=>trim($_POST['slide_text']??''),
      'image_url'=>trim($_POST['image_url']??''),
      'duration_sec'=>$duration,
      'voice'=>isset($_POST['voice']),
      'source'=>'Régie immédiate',
      'updated'=>now(),
      'expires_at'=>$expires
    ];
    write_json('geah_live.json',$live);
    header('Location:/admin/tv-control-room.php?saved=1'); exit;
  }
}
$loopCount=count(geah_tv_loop());
$active=geah_tv_takeover();
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Régie TV Pro</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head><body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main">
<h1>🎛️ Régie Pro — GEA-H TV</h1>
<p class="status ok">Ordre de priorité : 1) Intervention admin / live 2) Programme planifié 3) Playlist automatique 4) boucle.</p>
<?php if(isset($_GET['saved'])) echo '<p class="status ok">✅ Intervention lancée. La TV publique bascule automatiquement dans quelques secondes.</p>'; ?>
<?php if(isset($_GET['stopped'])) echo '<p class="status ok">✅ Intervention arrêtée. La playlist ou le programme en cours reprend automatiquement.</p>'; ?>
<div class="grid-3">
  <div class="card"><h3>Playlist normale</h3><p><?=e($loopCount)?> contenu(s) prêts à jouer en continu.</p></div>
  <div class="card"><h3>État antenne</h3><p><?= $active ? '🔴 Un contenu prioritaire est actif' : '✅ Playlist / programmation normale' ?></p></div>
  <a class="card" href="/modules/geah-tv.php" target="_blank"><h3>Ouvrir la chaîne</h3><p>Contrôler ce que voit le public.</p></a>
</div>
<form class="card" method="post">
<input type="hidden" name="action" value="interrupt">
<h2>Couper l’antenne maintenant</h2>
<p>Utilise ce formulaire pour diffuser immédiatement une information, une vidéo, un reportage, une publicité, une musique ou un live. À la fin de la durée, la chaîne reprend automatiquement.</p>
<div class="form-grid">
<label>Titre<input name="title" value="<?=e($live['title']??'')?>" placeholder="Ex: Information importante, Direct terrain, Promotion spéciale"></label>
<label>Type<select name="category"><option>INFO</option><option>DIRECT</option><option>REPORTAGE</option><option>INTERVIEW</option><option>PROMOTION</option><option>PUBLICITÉ</option><option>MUSIQUE</option><option>SÉRIE</option><option>DOCUMENTAIRE</option></select></label>
<label class="full">Lien vidéo / live / musique<input name="url" value="<?=e($live['url']??'')?>" placeholder="MP4, YouTube, Facebook, HLS .m3u8, Cloudinary... Laisser vide pour afficher un slide texte."></label>
<label class="full">Image affiche optionnelle<input name="image_url" value="<?=e($live['image_url']??'')?>" placeholder="Image pour information ou promotion"></label>
<label>Durée de priorité (secondes)<input type="number" name="duration_sec" value="<?=e($live['duration_sec']??60)?>" min="15"></label>
<label><input type="checkbox" name="voice" <?=!empty($live['voice'])?'checked':''?>> Lire le texte par voix navigateur</label>
<label class="full">Texte de l'information / script<textarea name="slide_text" rows="5" placeholder="Ex: GEA-H informe ses clients qu'une nouvelle promotion est disponible..."><?=e($live['slide_text']??'')?></textarea></label>
</div>
<button class="btn btn-primary">Diffuser immédiatement</button>
</form>
<form class="card" method="post" onsubmit="return confirm('Arrêter l’intervention en cours et reprendre la programmation normale ?')">
<input type="hidden" name="action" value="stop">
<h2>Reprendre la programmation normale</h2>
<p>Arrête le direct ou l'information prioritaire. La playlist automatique ou le programme planifié reprend sans recommencer toute la chaîne.</p>
<button class="btn danger">Arrêter l’intervention</button>
</form>
<div class="card"><h2>Fonctionnement attendu</h2><ul><li>Les vidéos publiées jouent les unes après les autres en boucle.</li><li>Quand un programme arrive à son heure, il prend automatiquement l’antenne.</li><li>Quand le programme se termine, la playlist normale reprend.</li><li>L’admin peut couper immédiatement pour une information, une promotion, un live ou une nouvelle programmation.</li></ul></div>
</main></div></body></html>
