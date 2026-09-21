<?php require_once __DIR__.'/../core.php'; require_once __DIR__.'/auth.php'; require_role('settings');
$s=settings();
if($_SERVER['REQUEST_METHOD']==='POST'){
    if(!empty($_FILES['launch_video_file']['name'])){
        $dir=__DIR__.'/../uploads/prelaunch';
        if(!is_dir($dir)) mkdir($dir,0755,true);
        $ext=strtolower(pathinfo($_FILES['launch_video_file']['name'],PATHINFO_EXTENSION));
        if(in_array($ext,['mp4','webm','ogg','mov'],true)){
            $name='launch-video-'.time().'.'.$ext;
            if(move_uploaded_file($_FILES['launch_video_file']['tmp_name'],$dir.'/'.$name)){
                $s['launch_video']='/uploads/prelaunch/'.$name;
            }
        }
    }
    $s['site_mode']=$_POST['site_mode']; $s['site_title']=$_POST['site_title']; $s['site_slogan']=$_POST['site_slogan'];
    $s['public_message']=$_POST['public_message']; $s['launch_date']=$_POST['launch_date']; $s['anti_index']=isset($_POST['anti_index']);
    $s['about_text']=$_POST['about_text']??($s['about_text']??'');
    $s['launch_video']=$_POST['launch_video'] ?? ($s['launch_video'] ?? '');
    $s['prelaunch_voice_enabled']=isset($_POST['prelaunch_voice_enabled']);
    $s['prelaunch_voice_text']=$_POST['prelaunch_voice_text'] ?? ($s['prelaunch_voice_text'] ?? '');
    $s['auto_public_after_launch']=isset($_POST['auto_public_after_launch']);
    $s['launch_fireworks_enabled']=isset($_POST['launch_fireworks_enabled']);
    $s['company_email']=$_POST['company_email']??''; $s['company_phone']=$_POST['company_phone']??'';
    $s['company_whatsapp']=$_POST['company_whatsapp']??''; $s['company_address']=$_POST['company_address']??''; $s['company_city']=$_POST['company_city']??'';
    foreach(['commercial','rh','support'] as $svc){
        $s['contact_'.$svc.'_phone']=$_POST['contact_'.$svc.'_phone']??'';
        $s['contact_'.$svc.'_whatsapp']=$_POST['contact_'.$svc.'_whatsapp']??'';
        $s['contact_'.$svc.'_email']=$_POST['contact_'.$svc.'_email']??'';
    }
    save_settings($s);
    $__check = read_json('settings.json', null);
    if(is_array($__check) && ($__check['launch_date'] ?? null) === $s['launch_date']){
        header('Location:/admin/settings.php?saved=1'); exit;
    }
    header('Location:/admin/settings.php?saved=0'); exit;
}
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Réglages</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main">
<h1>Réglages du site</h1>

<?php $__siteUrl='https://gea-holding.net/'; $__qrDataUri='data:image/png;base64,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'; ?>
<div class="card" style="display:flex;gap:20px;flex-wrap:wrap;align-items:center">
  <img src="<?=$__qrDataUri?>" alt="QR code du site" width="180" height="180" style="border-radius:12px;background:#fff;padding:6px">
  <div>
    <h3 style="margin:0 0 6px">📱 Code QR du site</h3>
    <p style="margin:0 0 10px;color:var(--muted)">Vos clients scannent ce code pour arriver directement sur <b><?=e($__siteUrl)?></b>. Utile sur vos flyers, cartes de visite, panneaux de biens.</p>
    <a class="btn btn-gold" href="<?=$__qrDataUri?>" download="qr-gea-holding.png">⬇️ Télécharger l'image</a>
  </div>
</div>

<?php if(isset($_GET['saved']) && $_GET['saved']==='1') echo '<p class="status ok">✅ Enregistré</p>'; ?>
<?php if(isset($_GET['saved']) && $_GET['saved']==='0') echo '<p class="status warn">❌ Échec de l\'enregistrement — le fichier de réglages n\'a pas pu être écrit sur le serveur. Vérifiez les permissions du dossier <code>data/</code> (il doit être accessible en écriture par l\'utilisateur PHP/Plesk).</p>'; ?>
<?php $__wtest = is_dir(DATA_DIR) ? is_writable(DATA_DIR) : @mkdir(DATA_DIR,0775,true); if(!$__wtest) echo '<p class="status warn">⚠️ Le dossier data/ n\'est actuellement pas accessible en écriture. Les modifications ne seront pas sauvegardées tant que ce n\'est pas corrigé.</p>'; ?>
<form class="card" method="post" enctype="multipart/form-data">
  <h2>Mode & présentation</h2>
  <div class="form-grid">
    <label>Mode<select name="site_mode"><option value="prelaunch" <?=$s['site_mode']==='prelaunch'?'selected':''?>>Pré-lancement</option><option value="public" <?=$s['site_mode']==='public'?'selected':''?>>Public</option><option value="maintenance" <?=$s['site_mode']==='maintenance'?'selected':''?>>Maintenance</option></select></label>
    <label>Titre<input name="site_title" value="<?=e($s['site_title'])?>"></label>
    <label>Slogan<input name="site_slogan" value="<?=e($s['site_slogan'])?>"></label>
    <label>Date lancement<input type="datetime-local" name="launch_date" value="<?=e($s['launch_date'])?>"></label>
    <label class="full"><input type="checkbox" name="anti_index" <?=$s['anti_index']?'checked':''?>> Anti-indexation (cacher des moteurs)</label>
    <label class="full">Message pré-lancement<textarea name="public_message"><?=e($s['public_message'])?></textarea></label>
    <label class="full">🏢 Présentation "Qui sommes-nous" (affichée sur la page publique)<textarea name="about_text" rows="6"><?=e($s['about_text']??'')?></textarea></label>
    <label class="full">Vidéo de lancement déjà hébergée / URL vidéo<input name="launch_video" value="<?=e($s['launch_video']??'')?>" placeholder="/uploads/prelaunch/launch-video.mp4 ou https://..."></label>
    <label class="full">Importer une vidéo de présentation<input type="file" name="launch_video_file" accept="video/mp4,video/webm,video/ogg,video/quicktime"></label>
    <label class="full"><input type="checkbox" name="prelaunch_voice_enabled" <?=!empty($s['prelaunch_voice_enabled'])?'checked':''?>> Activer la présentation vocale IA sur la page verrouillée</label>
    <label class="full"><input type="checkbox" name="auto_public_after_launch" <?=!empty($s['auto_public_after_launch'])?'checked':''?>> Après la vidéo de lancement, ouvrir automatiquement le site en mode public</label>
    <label class="full"><input type="checkbox" name="launch_fireworks_enabled" <?=!empty($s['launch_fireworks_enabled'])?'checked':''?>> Activer les artifices / confettis au lancement</label>
    <label class="full">Texte lu par l’assistant IA<textarea name="prelaunch_voice_text" rows="6"><?=e($s['prelaunch_voice_text']??'')?></textarea></label>
  </div>
  <h2 style="margin-top:18px">Contact administration / principal</h2>
  <div class="form-grid">
    <label>Email administration<input name="company_email" value="<?=e($s['company_email']??'')?>" placeholder="administration@gea-holding.net"></label>
    <label>Téléphone administration<input name="company_phone" value="<?=e($s['company_phone']??'')?>" placeholder="+225 07 00 00 00 00"></label>
    <label>WhatsApp administration<input name="company_whatsapp" value="<?=e($s['company_whatsapp']??'')?>" placeholder="+225 07 00 00 00 00"></label>
    <label>Adresse<input name="company_address" value="<?=e($s['company_address']??'')?>" placeholder="Rue, quartier..."></label><label>Ville<input name="company_city" value="<?=e($s['company_city']??'')?>" placeholder="Abidjan"></label>
  </div>

  <h2 style="margin-top:18px">Contacts par service</h2>
  <p style="color:#64748b;margin-top:-6px">Ces contacts seront affichés dans le pied de page et sur la page Contact.</p>
  <div class="contact-admin-grid">
    <?php $services=[
      'commercial'=>'Service commercial',
      'rh'=>'Directeur ressources humaines',
      'support'=>'Support'
    ]; foreach($services as $key=>$label): ?>
      <div class="contact-admin-card">
        <h3><?=e($label)?></h3>
        <label>Téléphone<input name="contact_<?=$key?>_phone" value="<?=e($s['contact_'.$key.'_phone']??'')?>" placeholder="+225..."></label>
        <label>WhatsApp<input name="contact_<?=$key?>_whatsapp" value="<?=e($s['contact_'.$key.'_whatsapp']??'')?>" placeholder="+225..."></label>
        <label>Email<input name="contact_<?=$key?>_email" value="<?=e($s['contact_'.$key.'_email']??'')?>" placeholder="service@gea-holding.net"></label>
      </div>
    <?php endforeach; ?>
  </div>
  <button class="btn btn-primary">Enregistrer</button>
</form>
</main></div></body></html>
