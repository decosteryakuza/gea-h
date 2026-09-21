<?php
require_once __DIR__.'/../core.php';
require_once __DIR__.'/auth.php'; require_role('gea_tv');
$items = data_list('geah_tv');
$live  = read_json('geah_live.json', ['active'=>false,'title'=>'','url'=>'','description'=>'']);

if($_SERVER['REQUEST_METHOD']==='POST'){
    if(isset($_POST['delete'])){
        $id=(int)$_POST['delete'];
        $items=array_values(array_filter($items,fn($x)=>($x['id']??0)!=$id));
        data_save('geah_tv',$items);
        header('Location:/admin/geah-tv.php?deleted=1'); exit;
    }
    if(($_POST['action']??'')==='savelive'){
        $wasActive = !empty($live['active']);
        $live=['active'=>isset($_POST['live_active']),'title'=>$_POST['live_title']??'','url'=>trim($_POST['live_url']??''),'description'=>$_POST['live_description']??''];
        write_json('geah_live.json',$live);
        log_action('Live TV mis à jour');
        if($live['active'] && $live['url']!=='' && !$wasActive && isset($_POST['fb_announce']) && function_exists('geah_fb_announce')){
            $m='🔴 Nous sommes EN DIRECT sur GEA-H TV'.($live['title']?' — '.strip_tags($live['title']):'').' ! Regardez ici 👇';
            $res=geah_social_announce($m, geah_base_url().'/', geah_announce_image());
            $parts=[]; if(!empty($res['fb'])) $parts[]='Facebook '.($res['fb']['ok']?'✅':('⚠️ '.$res['fb']['error'])); if(!empty($res['ig'])) $parts[]='Instagram '.($res['ig']['ok']?'✅':('⚠️ '.$res['ig']['error']));
            $_SESSION['fb_live_msg']='Annonce du direct → '.implode(' · ',$parts);
        }
        header('Location:/admin/geah-tv.php?livesaved=1'); exit;
    }
    // Ajout d'une vidéo (VOD)
    $uploaded = geah_tv_upload('video_file');
    if(!$uploaded && !empty($_FILES['video_file']['name'])){
        header('Location:/admin/geah-tv.php?uploaderr=1'); exit; // fichier trop lourd ou format refusé
    }
    $videoUrl = $uploaded ?: trim($_POST['video_url'] ?? '');
    if($videoUrl===''){ header('Location:/admin/geah-tv.php?novideo=1'); exit; }
    $items[]=[
        'id'=>time(),'title'=>$_POST['title']??'','type'=>$_POST['type']??'Vidéo','category'=>$_POST['category']??'',
        'video_url'=>$videoUrl,'status'=>$_POST['status']??'Publié','publish_date'=>$_POST['publish_date']??'',
        'description'=>$_POST['description']??'','date'=>now()
    ];
    data_save('geah_tv',$items);
    header('Location:/admin/geah-tv.php?saved=1'); exit;
}
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>GEA-H TV</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main">
<h1>GEA-H TV — Chaîne Internet</h1>
<div class="grid-3"><a class="card" href="/admin/tv-programme-pro.php"><h3>📡 Programmation Pro</h3><p>Heure, jour, émissions, interviews, documentaires, publicités.</p></a><a class="card" href="/admin/tv-ai-studio.php"><h3>🤖 Studio IA TV</h3><p>Scripts, séries, reportages et contenus IA.</p></a><a class="card" href="/admin/tv-live-studio.php"><h3>🔴 Régie Live</h3><p>Direct via OBS, smartphone, YouTube/Facebook/HLS.</p></a></div>
<?php if(isset($_GET['saved'])): ?><p class="status ok">✅ Vidéo ajoutée.</p><?php endif; ?>
<?php if(isset($_GET['livesaved'])): ?><p class="status ok">✅ Réglage du live enregistré.</p><?php endif; ?>
<?php if(!empty($_SESSION['fb_live_msg'])){ $__c=strpos($_SESSION['fb_live_msg'],"✅")!==false?"ok":"warn"; echo '<p class="status '.$__c.'">'.e($_SESSION['fb_live_msg']).'</p>'; unset($_SESSION['fb_live_msg']); } ?>
<?php if(isset($_GET['uploaderr'])): ?><p class="status warn">⚠️ La vidéo n'a pas pu être importée. Cause la plus fréquente : <b>fichier trop lourd</b> pour l'hébergement. Augmente <code>upload_max_filesize</code> et <code>post_max_size</code> dans les réglages PHP (Plesk), ou utilise un <b>lien externe</b> (YouTube/Vimeo).</p><?php endif; ?>
<?php if(isset($_GET['novideo'])): ?><p class="status warn">⚠️ Indique un fichier vidéo OU un lien externe.</p><?php endif; ?>

<!-- ===== DIFFUSION EN DIRECT (LIVE) ===== -->
<form class="card" method="post" style="border-left:4px solid #c0392b">
  <input type="hidden" name="action" value="savelive">
  <h2>🔴 Diffusion en direct (Live)</h2>
  <p>Pour faire un <b>live</b> : lance ton direct sur <b>YouTube Live</b>, <b>Facebook Live</b> (ou un flux <b>HLS .m3u8</b> d'un service de streaming), copie le lien ici, coche « En direct », et enregistre. Le live s'affiche en haut de GEA-H TV.</p>
  <div class="form-grid">
    <label class="full"><input type="checkbox" name="live_active" <?=!empty($live['active'])?'checked':''?>> <b>EN DIRECT maintenant</b> (afficher le live sur le site)</label>
    <label>Titre du live<input name="live_title" value="<?=e($live['title'])?>" placeholder="Émission spéciale immobilier"></label>
    <label>Lien du live<input name="live_url" value="<?=e($live['url'])?>" placeholder="https://www.youtube.com/watch?v=... ou .m3u8"></label>
    <label class="full">Description<textarea name="live_description" rows="2"><?=e($live['description'])?></textarea></label>
    <label class="full"><input type="checkbox" name="fb_announce" checked> 📣 Annoncer automatiquement ce direct sur ma Page Facebook</label>
  </div>
  <button class="btn btn-primary">💾 Enregistrer le live</button>
  <?php if(!empty($live['active']) && !empty($live['url'])): ?>
    <p class="status ok" style="margin-top:10px">Live ACTIF : <?=e($live['title'])?> — <a href="/modules/geah-tv.php" target="_blank">voir sur le site</a></p>
  <?php endif; ?>
</form>

<!-- ===== AJOUT VIDÉO (VOD) ===== -->
<form class="card" id="geahTvForm" method="post" enctype="multipart/form-data">
  <h2>Ajouter une vidéo (rediffusion / émission)</h2>
  <div class="form-grid">
    <label>Titre<input name="title" required></label>
    <label>Type<select name="type"><option>Vidéo</option><option>Émission</option><option>Interview</option><option>Reportage</option><option>Documentaire</option><option>Promotion</option><option>Publicité</option><option>Série</option><option>Musique</option><option>Rediffusion live</option></select></label>
    <label>Catégorie<select name="category"><option>Immobilier</option><option>Résidences meublées</option><option>Hôtels</option><option>Construction</option><option>Enchères</option><option>GEA-H Studio</option><option>Lotissement</option><option>Smart City</option><option>Partenaires</option><option>Formation</option><option>Actualités</option><option>Divertissement</option></select></label>
    <label>Statut<select name="status"><option>Publié</option><option>Programmé</option><option>Brouillon</option><option>Archivé</option></select><small class="video-note">Seules les vidéos « Publié » apparaissent sur le site.</small></label>
    <label>Uploader une vidéo<input type="file" id="tvVideoFile" name="video_file" accept="video/mp4,video/webm,video/quicktime,video/ogg,video/x-m4v"><small class="video-note">MP4 conseillé. Les gros fichiers sont envoyés en petits morceaux pour éviter l’erreur 413.</small></label>
    <label>Lien vidéo externe<input id="tvVideoUrl" name="video_url" placeholder="YouTube, Vimeo, Facebook, Cloudinary, .m3u8..."></label><div class="full" id="tvUploadBox" style="display:none"><div class="status ok" id="tvUploadText">Préparation de l’envoi vidéo...</div><div style="height:10px;background:#e5e7eb;border-radius:999px;overflow:hidden"><div id="tvUploadBar" style="height:10px;width:0%;background:#1f8f4d"></div></div></div>
    <label>Date publication<input type="datetime-local" name="publish_date"></label>
    <label class="full">Description<textarea name="description" rows="4"></textarea></label>
  </div>
  <button class="btn btn-primary">Ajouter à GEA-H TV</button>
</form>

<h2>Vidéos enregistrées</h2>
<table class="table"><tr><th>Date</th><th>Titre</th><th>Catégorie</th><th>Statut</th><th>Aperçu</th><th></th></tr>
<?php foreach(array_reverse($items) as $v): ?>
  <tr>
    <td><?=e($v['date']??'')?></td><td><?=e($v['title']??'')?></td><td><?=e($v['category']??'')?></td>
    <td><?=e($v['status']??'')?></td>
    <td style="max-width:240px"><?php if(!empty($v['video_url'])) echo geah_player_html($v['video_url']); ?></td>
    <td><form method="post" onsubmit="return confirm('Supprimer ?')"><button class="btn danger" name="delete" value="<?=e($v['id'])?>">Supprimer</button></form></td>
  </tr>
<?php endforeach; ?>
</table>

<script>
(function(){
  const form=document.getElementById('geahTvForm');
  const fileInput=document.getElementById('tvVideoFile');
  const urlInput=document.getElementById('tvVideoUrl');
  const box=document.getElementById('tvUploadBox');
  const bar=document.getElementById('tvUploadBar');
  const txt=document.getElementById('tvUploadText');
  if(!form||!fileInput||!urlInput) return;
  form.addEventListener('submit', async function(ev){
    const file=fileInput.files && fileInput.files[0];
    if(!file || urlInput.value.trim()!=='') return;
    ev.preventDefault();
    const chunkSize = 900 * 1024; // petit morceau pour éviter 413 même avec une limite serveur basse
    const total = Math.ceil(file.size / chunkSize);
    const uploadId = Date.now().toString(36)+'_'+Math.random().toString(36).slice(2);
    box.style.display='block';
    try{
      for(let i=0;i<total;i++){
        const fd=new FormData();
        fd.append('upload_id', uploadId);
        fd.append('chunk_index', i);
        fd.append('total_chunks', total);
        fd.append('filename', file.name);
        fd.append('chunk', file.slice(i*chunkSize, Math.min(file.size,(i+1)*chunkSize)), file.name+'.part');
        txt.textContent='Envoi vidéo GEA-H TV : '+(i+1)+' / '+total;
        const res=await fetch('/admin/tv-upload-chunk.php',{method:'POST',body:fd,credentials:'same-origin'});
        const data=await res.json();
        if(!data.ok) throw new Error(data.error || 'Erreur upload');
        bar.style.width=(data.progress||Math.round(((i+1)/total)*100))+'%';
        if(data.done && data.url){
          urlInput.value=data.url;
        }
      }
      if(!urlInput.value.trim()) throw new Error('Vidéo envoyée mais URL finale non reçue');
      fileInput.removeAttribute('name');
      txt.textContent='Vidéo envoyée. Enregistrement dans GEA-H TV...';
      form.submit();
    }catch(e){
      txt.textContent='Erreur : '+e.message;
      box.querySelector('.status').className='status warn';
    }
  });
})();
</script>
<script src="https://cdn.jsdelivr.net/npm/hls.js@1.5.13/dist/hls.min.js"></script>
<script>document.querySelectorAll('video[data-hls]').forEach(function(vd){var src=vd.getAttribute('data-hls');if(vd.canPlayType('application/vnd.apple.mpegurl')){vd.src=src;}else if(window.Hls&&Hls.isSupported()){var h=new Hls();h.loadSource(src);h.attachMedia(vd);}});</script>
</main></div></body></html>
