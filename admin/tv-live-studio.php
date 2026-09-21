<?php require_once __DIR__.'/../core.php'; require_once __DIR__.'/auth.php'; require_role('gea_tv');
$live=read_json('geah_live.json',['active'=>false,'title'=>'','url'=>'','description'=>'','source'=>'']);
if($_SERVER['REQUEST_METHOD']==='POST'){
  $live=['active'=>isset($_POST['active']),'title'=>trim($_POST['title']??''),'url'=>trim($_POST['url']??''),'description'=>trim($_POST['description']??''),'source'=>trim($_POST['source']??''),'updated'=>now()];
  write_json('geah_live.json',$live); header('Location:/admin/tv-live-studio.php?saved=1'); exit;
}
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Live GEA-H TV</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head><body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main">
<h1>🔴 Régie Live — GEA-H TV</h1>
<?php if(isset($_GET['saved'])) echo '<p class="status ok">✅ Live enregistré.</p>'; ?>
<div class="card"><h2>Principe</h2><p>Pour un direct stable, utilisez OBS Studio, une application mobile RTMP, YouTube Live, Facebook Live ou un flux HLS. Collez le lien public ou le flux <code>.m3u8</code> ici. Le direct passe en priorité sur la chaîne.</p><p class="status warn">TikTok ne fournit pas toujours un lien réutilisable directement. Le plus fiable est de diffuser avec OBS / smartphone RTMP vers un service live, puis d'insérer le lien dans GEA-H TV.</p></div>
<form class="card" method="post">
<h2>Activer / programmer un direct</h2><div class="form-grid">
<label class="full"><input type="checkbox" name="active" <?=!empty($live['active'])?'checked':''?>> <b>EN DIRECT maintenant</b></label>
<label>Titre<input name="title" value="<?=e($live['title'])?>" placeholder="Reportage terrain en direct"></label>
<label>Source<select name="source"><option <?=($live['source']??'')==='Caméra/OBS'?'selected':''?>>Caméra/OBS</option><option <?=($live['source']??'')==='YouTube Live'?'selected':''?>>YouTube Live</option><option <?=($live['source']??'')==='Facebook Live'?'selected':''?>>Facebook Live</option><option <?=($live['source']??'')==='TikTok relayé'?'selected':''?>>TikTok relayé</option><option <?=($live['source']??'')==='HLS/RTMP'?'selected':''?>>HLS/RTMP</option></select></label>
<label class="full">Lien live / flux<input name="url" value="<?=e($live['url'])?>" placeholder="https://... ou https://.../live.m3u8"></label>
<label class="full">Description<textarea name="description" rows="3"><?=e($live['description'])?></textarea></label>
</div><button class="btn btn-primary">Enregistrer le direct</button></form>
<div class="card"><h2>Checklist reportage en direct</h2><ol><li>Tester le son et la connexion.</li><li>Activer le direct sur la source choisie.</li><li>Coller le lien dans GEA-H TV.</li><li>Cocher EN DIRECT.</li><li>Ouvrir la page publique GEA-H TV sur l'écran de contrôle.</li></ol></div>
</main></div></body></html>
