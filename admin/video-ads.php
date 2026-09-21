<?php require_once __DIR__.'/../core.php'; require_once __DIR__.'/auth.php';
$items=data_list('video_ads');
if($_SERVER['REQUEST_METHOD']==='POST'){
    if(($_POST['action']??'')==='delete'){ $items=array_values(array_filter($items,fn($x)=>($x['id']??0)!=(int)$_POST['id'])); data_save('video_ads',$items); header('Location:/admin/video-ads.php'); exit; }
    $media=''; $up=geah_upload_files('media_file','ads',['jpg','jpeg','png','webp','gif','mp4','webm','mov','ogg']);
    if($up) $media=$up[0]; elseif(trim($_POST['media_url']??'')!=='') $media=trim($_POST['media_url']);
    $items[]=['id'=>time(),'title'=>$_POST['title']??'','type'=>$_POST['type']??'','platforms'=>$_POST['platforms']??[],'brief'=>$_POST['brief']??'','media_url'=>$media,'tv'=>isset($_POST['tv']),'status'=>'préparée','date'=>now()];
    data_save('video_ads',$items); header('Location:/admin/video-ads.php?saved=1'); exit;
}
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Images/Vidéos publicitaires</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main">
<h1>Images / Vidéos publicitaires</h1>
<?php if(isset($_GET['saved'])) echo '<p class="status ok">✅ Enregistré</p>'; ?>
<form class="card" method="post" enctype="multipart/form-data">
  <div class="form-grid">
    <label>Titre<input name="title" required></label>
    <label>Type<select name="type"><option>Vidéo publicitaire</option><option>Image publicitaire</option><option>Script vidéo</option></select></label>
    <label>📎 Média (image ou vidéo)<input type="file" name="media_file" accept="image/*,video/*"></label>
    <label>… ou lien média<input name="media_url" placeholder="YouTube, lien vidéo/image..."></label>
    <label class="full">Brief / texte de la pub<textarea name="brief" rows="3"></textarea></label>
    <label>Plateformes<span><label><input type="checkbox" name="platforms[]" value="facebook"> Facebook</label> <label><input type="checkbox" name="platforms[]" value="instagram"> Instagram</label> <label><input type="checkbox" name="platforms[]" value="youtube"> YouTube</label> <label><input type="checkbox" name="platforms[]" value="tiktok"> TikTok</label></span></label>
    <label><input type="checkbox" name="tv" checked> 📺 Diffuser sur GEA-H TV (accueil)</label>
  </div>
  <button class="btn btn-primary">Enregistrer la publicité</button>
</form>
<table class="table"><tr><th>Date</th><th>Titre</th><th>Type</th><th>Média</th><th>Sur TV</th><th>Plateformes</th><th></th></tr>
<?php foreach(array_reverse($items) as $v): ?>
  <tr><td><?=e($v['date'])?></td><td><?=e($v['title'])?></td><td><?=e($v['type'])?></td>
  <td><?php if(!empty($v['media_url'])){ if(geah_is_image_url($v['media_url'])) echo '<img src="'.e(media_src($v['media_url'])).'" style="height:48px;border-radius:6px">'; else echo '🎬 vidéo'; } else echo '—'; ?></td>
  <td><?=!empty($v['tv'])?'✅':'—'?></td><td><?=e(implode(', ',$v['platforms']??[]))?></td>
  <td><form method="post" onsubmit="return confirm('Supprimer ?')"><input type="hidden" name="action" value="delete"><button class="btn danger" name="id" value="<?=e($v['id'])?>">✕</button></form></td></tr>
<?php endforeach; ?>
</table>
</main></div></body></html>
