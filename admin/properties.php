<?php
require_once __DIR__.'/../core.php';
require_once __DIR__.'/auth.php'; require_role('properties');
$items=data_list('properties');
$PHOTO_EXT=['jpg','jpeg','png','webp','gif'];
$VIDEO_EXT=['mp4','webm','mov','ogg'];

if($_SERVER['REQUEST_METHOD']==='POST'){
    if(empty($_POST) && empty($_FILES) && (int)($_SERVER['CONTENT_LENGTH']??0)>0){
        $_SESSION['flash_upload']=['Donnees trop volumineuses : la taille totale depasse post_max_size du serveur. Reduis les photos ou augmente post_max_size (Plesk > PHP Settings).'];
        header('Location:/admin/properties.php?uploaderr=1'); exit;
    }
    $action=$_POST['action']??'add';

    if($action==='delete'){
        $id=(int)$_POST['delete'];
        $items=array_values(array_filter($items,fn($x)=>($x['id']??0)!=$id));
        data_save('properties',$items);
        header('Location:/admin/properties.php?deleted=1'); exit;
    }

    if($action==='update'){
        $id=(int)($_POST['id']??0);
        foreach($items as &$it){ if(($it['id']??0)==$id){
            $it['title']=trim($_POST['title']??$it['title']);
            $it['type']=trim($_POST['type']??$it['type']);
            $it['transaction']=$_POST['transaction']??($it['transaction']??'vente');
            $it['price']=trim($_POST['price']??$it['price']);
            $it['price_fcfa']=(int)($_POST['price_fcfa']??($it['price_fcfa']??0));
            $it['caution']=(int)($_POST['caution']??($it['caution']??0));
            $it['city']=trim($_POST['city']??$it['city']);
            $it['address']=trim($_POST['address']??$it['address']);
            $it['status']=trim($_POST['status']??($it['status']??'Disponible'));
            $it['description']=trim($_POST['description']??$it['description']);
            $it['updated']=now();
        }} unset($it);
        data_save('properties',$items);
        header('Location:/admin/properties.php?saved=1#bien'.$id); exit;
    }

    if($action==='add'){
        if(auth_role()!=='super'){ header('Location:/admin/properties.php?forbidden=1'); exit; }
        $photos=geah_upload_files('photos','properties',$PHOTO_EXT);
        $vf=geah_upload_files('video_file','properties',$VIDEO_EXT);
        $video = $vf[0] ?? trim($_POST['video_url']??'');
        $items[]=[
            'id'=>time(),'date'=>now(),
            'title'=>$_POST['title']??'','type'=>$_POST['type']??'','transaction'=>($_POST['transaction']??'vente'),'price_fcfa'=>(int)($_POST['price_fcfa']??0),'caution'=>(int)($_POST['caution']??0),'lat'=>trim($_POST['lat']??''),'lng'=>trim($_POST['lng']??''),'price'=>$_POST['price']??'',
            'city'=>$_POST['city']??'','address'=>$_POST['address']??'','status'=>$_POST['status']??'',
            'latitude'=>$_POST['latitude']??'','longitude'=>$_POST['longitude']??'',
            'description'=>$_POST['description']??'','images'=>$photos,'video_url'=>$video,'owner_type'=>$_POST['owner_type']??'gea'
        ];
        data_save('properties',$items);
        $_SESSION['flash_upload']=$GLOBALS['geah_upload_msgs']??[];
        header('Location:/admin/properties.php?saved=1'); exit;
    }

    if($action==='media'){
        $id=(int)($_POST['id']??0);
        $photos=geah_upload_files('photos','properties',$PHOTO_EXT);
        $vf=geah_upload_files('video_file','properties',$VIDEO_EXT);
        $videoUrl=trim($_POST['video_url']??'');
        foreach($items as &$it){ if(($it['id']??0)==$id){
            $cur=$it['images']??[]; if(!is_array($cur)) $cur=[];
            $it['images']=array_merge($cur,$photos);
            if(!empty($vf)) $it['video_url']=$vf[0];
            elseif($videoUrl!=='') $it['video_url']=$videoUrl;
        }} unset($it);
        data_save('properties',$items);
        $_SESSION['flash_upload']=$GLOBALS['geah_upload_msgs']??[];
        header('Location:/admin/properties.php?saved=1#bien'.$id); exit;
    }

    if($action==='delphoto'){
        $id=(int)($_POST['id']??0); $path=$_POST['path']??'';
        foreach($items as &$it){ if(($it['id']??0)==$id){
            if(($it['image']??'')===$path) $it['image']='';
            $it['images']=array_values(array_filter($it['images']??[],fn($p)=>$p!==$path));
        }} unset($it);
        data_save('properties',$items);
        $base=__DIR__.'/..'.$path; if($path && is_file($base)) @unlink($base);
        header('Location:/admin/properties.php?deleted=1#bien'.$id); exit;
    }

    if($action==='delvideo'){
        $id=(int)($_POST['id']??0);
        foreach($items as &$it){ if(($it['id']??0)==$id) $it['video_url']=''; } unset($it);
        data_save('properties',$items);
        header('Location:/admin/properties.php?deleted=1#bien'.$id); exit;
    }
}
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main">
<h1>Biens immobiliers</h1>
<?php if(!empty($_SESSION['flash_upload'])): ?>
  <div class="card" style="border-left:4px solid #c0392b"><b>Rapport d'envoi des fichiers :</b>
  <ul style="margin:8px 0 0"><?php foreach($_SESSION['flash_upload'] as $m): ?><li><?=e($m)?></li><?php endforeach; ?></ul></div>
<?php unset($_SESSION['flash_upload']); endif; ?>
<details class="card"><summary style="cursor:pointer;font-weight:700">🔧 Diagnostic photos (clique si une photo ne s'affiche pas)</summary>
<?php
$__td=__DIR__.'/../uploads/properties'; if(!is_dir($__td)) @mkdir($__td,0755,true);
@file_put_contents($__td.'/_test.gif', base64_decode('R0lGODlhAQABAIAAAP8AAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==')); @chmod($__td.'/_test.gif',0644);
?>
<p><b>Test du serveur d'images :</b> <img src="/uploads/properties/_test.gif?t=<?=time()?>" style="width:18px;height:18px;border:1px solid #ccc;vertical-align:middle" onload="this.insertAdjacentHTML('afterend','<b style=color:green> &nbsp;OK le serveur affiche bien les images</b>')" onerror="this.insertAdjacentHTML('afterend','<b style=color:#c0392b> &nbsp;nginx ne sert pas /uploads (normal sur ce serveur)</b>')"></p>
<p><b>Test mode PHP (celui utilise par le site) :</b> <img src="/media.php?f=properties/_test.gif&t=<?=time()?>" style="width:18px;height:18px;border:1px solid #ccc;vertical-align:middle" onload="this.insertAdjacentHTML('afterend','<b style=color:green> &nbsp;OK les photos s\'affichent (servies par PHP)</b>')" onerror="this.insertAdjacentHTML('afterend','<b style=color:#c0392b> &nbsp;Echec meme via PHP - contacte-moi</b>')"></p>
  <ul style="margin:8px 0 0">
    <li>Taille max par fichier : <b><?=e(ini_get('upload_max_filesize'))?></b></li>
    <li>Taille max du formulaire : <b><?=e(ini_get('post_max_size'))?></b></li>
    <li>Dossier uploads/properties en ecriture : <b><?=is_writable(__DIR__.'/../uploads/properties')?'OUI ✅':'NON ❌ (chmod 775)'?></b></li>
    <li>Appli = racine web ? <b><?=@realpath(__DIR__.'/..')===@realpath($_SERVER['DOCUMENT_ROOT']??'')?'OUI ✅':'⚠️ DIFFERENT'?></b> <small style="color:#6b7280"><?=e(@realpath(__DIR__.'/..'))?> | <?=e($_SERVER['DOCUMENT_ROOT']??'')?></small></li>
  </ul>
  <p style="color:#6b7280;font-size:13px">Si « ecriture = NON » : dans Plesk (gestionnaire de fichiers), mets les permissions du dossier <code>uploads</code> a 775. Si les tailles sont trop petites : Plesk &gt; PHP Settings, augmente-les.</p>
</details>
<?php if(isset($_GET['saved'])): ?><p class="status ok">✅ Enregistré.</p><?php endif; ?>
<?php if(isset($_GET['deleted'])): ?><p class="status warn">🗑️ Supprimé.</p><?php endif; ?>
<?php if(isset($_GET['forbidden'])): ?><p class="status warn">🔒 Seul le Super Administrateur peut ajouter un nouveau bien.</p><?php endif; ?>

<?php if(auth_role()==='super'): ?>
<form class="card" method="post" enctype="multipart/form-data">
  <input type="hidden" name="action" value="add">
  <h2>Ajouter un bien</h2>
  <div class="form-grid">
    <label>Titre<input type="text" name="title" required></label>
    <label>Type<input type="text" name="type" placeholder="Villa, Terrain..."></label>
    <label>Transaction<select name="transaction"><option value="vente">À vendre</option><option value="location">À louer</option></select></label>
    <label>💳 Prix réservation/acompte en ligne (FCFA)<input type="number" name="price_fcfa" min="0" placeholder="Ex: 50000"></label>
    <label>🔑 Caution à payer avant remise des clés (FCFA, si location)<input type="number" name="caution" min="0" placeholder="Ex: 500000"></label>
    <label>📍 Latitude GPS<input name="lat" placeholder="Ex: 5.359900"></label>
    <label>📍 Longitude GPS<input name="lng" placeholder="Ex: -4.008300"></label>
    <label style="align-self:end"><button type="button" class="btn btn-light" onclick="navigator.geolocation.getCurrentPosition(function(p){document.querySelector('[name=lat]').value=p.coords.latitude.toFixed(6);document.querySelector('[name=lng]').value=p.coords.longitude.toFixed(6);},function(){alert('Activez la localisation')})">📍 Utiliser ma position GPS</button></label>
    <label>Prix<input type="text" name="price"></label>
    <label>Ville<input type="text" name="city"></label>
    <label>Adresse<input type="text" name="address"></label>
    <label>Statut<select name="status"><option>Disponible</option><option>Occupé</option><option>Réservé</option><option>Vendu</option><option>Loué</option></select></label>
    <label>Propriétaire (badge)<select name="owner_type"><option value="gea">🏅 Certifié GEA Holding</option><option value="particulier">👤 Particulier</option><option value="agence">🏢 Agence partenaire</option><option value="promoteur">🏗 Promoteur partenaire</option></select></label>
    <label>📷 Photos (plusieurs possibles)<input type="file" name="photos[]" accept="image/*" multiple></label>
    <label>🎬 Vidéo (fichier)<input type="file" name="video_file" accept="video/*"></label>
    <label>… ou lien vidéo<input type="text" name="video_url" placeholder="YouTube, Vimeo..."></label>
    <label>Latitude<input type="text" name="latitude"></label>
    <label>Longitude<input type="text" name="longitude"></label>
    <label class="full">Description<textarea name="description" rows="4"></textarea></label>
  </div>
  <button class="btn btn-primary">Ajouter le bien</button>
  <p style="color:#6b7280;font-size:13px;margin-top:6px">Astuce : maintiens Ctrl (ou ⌘) pour sélectionner plusieurs photos d'un coup.</p>
</form>
<?php else: ?>
<div class="card"><p class="status warn">🔒 Seul le Super Administrateur peut ajouter un nouveau bien. Vous pouvez consulter et gérer les biens existants ci-dessous.</p></div>
<?php endif; ?>

<h2>Biens enregistrés (<?=count($items)?>)</h2>
<?php foreach(array_reverse($items) as $item): $imgs=geah_property_images($item); ?>
  <div class="card" id="bien<?=e($item['id'])?>">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:10px;flex-wrap:wrap">
      <div><h3 style="margin:0"><?=e($item['title']??'')?> <?=geah_owner_badge($item)?></h3><p style="margin:4px 0;color:#6b7280"><?=e($item['type']??'')?> · <?=e($item['city']??'')?> · <?=e($item['status']??'')?> · <?=e($item['price']??'')?></p></div>
      <form method="post" onsubmit="return confirm('Supprimer ce bien ?')"><input type="hidden" name="action" value="delete"><button class="btn danger" name="delete" value="<?=e($item['id'])?>">Supprimer le bien</button></form>
    </div>

    <?php if($imgs || !empty($item['video_url'])): ?>
      <div class="pgrid">
        <?php foreach($imgs as $u): ?>
          <div class="pthumb">
            <a href="<?=e(media_src($u))?>" target="_blank"><img src="<?=e(media_src($u))?>" alt="" onerror="flagBroken(this)"></a>
            <form method="post" class="x" onsubmit="return confirm('Supprimer cette photo ?')"><input type="hidden" name="action" value="delphoto"><input type="hidden" name="id" value="<?=e($item['id'])?>"><input type="hidden" name="path" value="<?=e($u)?>"><button title="Supprimer">✕</button></form>
          </div>
        <?php endforeach; ?>
      </div>
      <?php if(!empty($item['video_url'])): ?>
        <div style="margin-top:8px"><?=geah_player_html($item['video_url'])?>
          <form method="post" style="display:inline"><input type="hidden" name="action" value="delvideo"><input type="hidden" name="id" value="<?=e($item['id'])?>"><button class="btn btn-light">Retirer la vidéo</button></form>
        </div>
      <?php endif; ?>
    <?php else: ?>
      <p style="color:#9ca3af">Aucune photo pour ce bien.</p>
    <?php endif; ?>

    <details style="margin-top:8px">
      <summary style="cursor:pointer;font-weight:700;color:#013328">✏️ Modifier les informations de ce bien</summary>
      <form method="post" style="margin-top:8px">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" value="<?=e($item['id'])?>">
        <div class="form-grid">
          <label>Titre<input name="title" value="<?=e($item['title']??'')?>"></label>
          <label>Type<input name="type" value="<?=e($item['type']??'')?>"></label>
          <label>Transaction<select name="transaction"><option value="vente" <?=($item['transaction']??'')==='vente'?'selected':''?>>À vendre</option><option value="location" <?=($item['transaction']??'')==='location'?'selected':''?>>À louer</option></select></label>
          <label>Statut<input name="status" value="<?=e($item['status']??'')?>" placeholder="Disponible, Réservé, Vendu..."></label>
          <label>Prix affiché<input name="price" value="<?=e($item['price']??'')?>"></label>
          <label>💳 Prix réservation/acompte en ligne (FCFA)<input type="number" name="price_fcfa" min="0" value="<?=e($item['price_fcfa']??0)?>"></label>
          <label>🔑 Caution avant remise des clés (FCFA, si location)<input type="number" name="caution" min="0" value="<?=e($item['caution']??0)?>"></label>
          <label>Ville<input name="city" value="<?=e($item['city']??'')?>"></label>
          <label class="full">Adresse<input name="address" value="<?=e($item['address']??'')?>"></label>
          <label class="full">Description<textarea name="description" rows="3"><?=e($item['description']??'')?></textarea></label>
        </div>
        <button class="btn btn-primary">Enregistrer les modifications</button>
      </form>
    </details>
    <details style="margin-top:8px">
      <summary style="cursor:pointer;font-weight:700;color:#013328">➕ Ajouter des photos / une vidéo</summary>
      <form method="post" enctype="multipart/form-data" style="margin-top:8px">
        <input type="hidden" name="action" value="media">
        <input type="hidden" name="id" value="<?=e($item['id'])?>">
        <div class="form-grid">
          <label>📷 Photos (plusieurs)<input type="file" name="photos[]" accept="image/*" multiple></label>
          <label>🎬 Vidéo (fichier)<input type="file" name="video_file" accept="video/*"></label>
          <label>… ou lien vidéo<input type="text" name="video_url" placeholder="YouTube, Vimeo..."></label>
        </div>
        <button class="btn btn-primary">Ajouter à ce bien</button>
      </form>
    </details>
  </div>
<?php endforeach; ?>
</main></div>
<script src="https://cdn.jsdelivr.net/npm/hls.js@1.5.13/dist/hls.min.js"></script>
<script>function flagBroken(img){var d=document.createElement('div');d.style.cssText='color:#c0392b;font-size:12px;padding:6px;border:1px dashed #c0392b;border-radius:6px;display:inline-block';d.textContent='⚠️ Image non servie (404) : '+img.getAttribute('src');img.replaceWith(d);}document.querySelectorAll('video[data-hls]').forEach(function(vd){var src=vd.getAttribute('data-hls');if(vd.canPlayType('application/vnd.apple.mpegurl')){vd.src=src;}else if(window.Hls&&Hls.isSupported()){var h=new Hls();h.loadSource(src);h.attachMedia(vd);}});</script>
</body></html>
