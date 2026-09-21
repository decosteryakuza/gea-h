<?php
require_once __DIR__.'/../core.php';
require_user_login('/modules/mes-annonces.php');
if(is_admin()){ header('Location:/admin/properties.php'); exit; }
$u=current_user() ?: [];
$email=strtolower(trim($u['email']??''));
$phone=trim($u['phone']??'');
function owns_announcement($it,$email,$phone){
  $emails=[strtolower(trim($it['owner_email']??'')), strtolower(trim($it['user_email']??''))];
  $phones=[trim($it['owner_phone']??''), trim($it['contact_phone']??'')];
  return ($email!=='' && in_array($email,$emails,true)) || ($phone!=='' && in_array($phone,$phones,true));
}
$msg=''; $err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $scope=$_POST['scope']??''; $id=(int)($_POST['id']??0); $action=$_POST['action']??'';
  if($scope==='submissions'){
    $items=data_list('submissions'); $found=false;
    foreach($items as $k=>&$it){
      if(($it['id']??0)===$id && owns_announcement($it,$email,$phone)){
        $found=true;
        if($action==='delete'){ unset($items[$k]); $msg='Annonce supprimée.'; }
        if($action==='update'){
          $it['title']=trim($_POST['title']??$it['title']); $it['type']=trim($_POST['type']??$it['type']);
          $it['price']=trim($_POST['price']??$it['price']); $it['price_fcfa']=(int)($_POST['price_fcfa']??($it['price_fcfa']??0));
          $it['city']=trim($_POST['city']??$it['city']); $it['address']=trim($_POST['address']??$it['address']);
          $it['description']=trim($_POST['description']??$it['description']); $it['transaction']=$_POST['transaction']??($it['transaction']??'vente'); $it['caution']=(int)($_POST['caution']??($it['caution']??0));
          $it['status']='En attente'; $it['updated']=now(); $msg='Annonce modifiée. Elle repasse en validation admin.';
        }
        break;
      }
    } unset($it);
    data_save('submissions', array_values($items)); if(!$found) $err='Annonce introuvable ou non autorisée.';
  }
  if($scope==='properties'){
    $items=data_list('properties'); $found=false;
    foreach($items as &$it){
      if(($it['id']??0)===$id && owns_announcement($it,$email,$phone)){
        $found=true;
        if($action==='delete'){ $it['status']='Retiré'; $it['validation']='retire'; $it['updated']=now(); $msg='Annonce retirée du site.'; }
        if($action==='update'){
          $it['title']=trim($_POST['title']??$it['title']); $it['type']=trim($_POST['type']??$it['type']);
          $it['price']=trim($_POST['price']??$it['price']); $it['price_fcfa']=(int)($_POST['price_fcfa']??($it['price_fcfa']??0));
          $it['city']=trim($_POST['city']??$it['city']); $it['address']=trim($_POST['address']??$it['address']);
          $it['description']=trim($_POST['description']??$it['description']); $it['transaction']=$_POST['transaction']??($it['transaction']??'vente'); $it['caution']=(int)($_POST['caution']??($it['caution']??0));
          $it['status']='En attente validation'; $it['validation']='pending_edit'; $it['updated']=now(); $msg='Modification envoyée. L’annonce sera réaffichée après validation admin.';
        }
        break;
      }
    } unset($it);
    data_save('properties',$items); if(!$found) $err='Annonce introuvable ou non autorisée.';
  }
}
$subs=array_values(array_filter(data_list('submissions'),fn($x)=>owns_announcement($x,$email,$phone)));
$props=array_values(array_filter(data_list('properties'),fn($x)=>owns_announcement($x,$email,$phone)));
function edit_form($it,$scope){ ob_start(); ?>
  <form method="post" class="card" style="margin-top:10px">
    <input type="hidden" name="scope" value="<?=e($scope)?>"><input type="hidden" name="id" value="<?=e($it['id']??0)?>">
    <div class="form-grid">
      <label>Titre<input name="title" value="<?=e($it['title']??'')?>" required></label>
      <label>Type<input name="type" value="<?=e($it['type']??'')?>"></label>
      <label>Prix affiché<input name="price" value="<?=e($it['price']??'')?>"></label>
      <label>Acompte / réservation en ligne (FCFA)<input type="number" name="price_fcfa" min="0" value="<?=e($it['price_fcfa']??0)?>"></label>
      <label>Transaction<select name="transaction"><option value="vente" <?=($it['transaction']??'')==='vente'?'selected':''?>>À vendre</option><option value="location" <?=($it['transaction']??'')==='location'?'selected':''?>>À louer</option></select></label>
      <label>🔑 Caution avant remise des clés (FCFA, si location)<input type="number" name="caution" min="0" value="<?=e($it['caution']??0)?>"></label>
      <label>Ville<input name="city" value="<?=e($it['city']??'')?>"></label>
      <label class="full">Adresse<input name="address" value="<?=e($it['address']??'')?>"></label>
      <label class="full">Description<textarea name="description" rows="3"><?=e($it['description']??'')?></textarea></label>
    </div>
    <button class="btn btn-violet" name="action" value="update">Modifier l’annonce</button>
    <button class="btn danger" name="action" value="delete" onclick="return confirm('Supprimer/retirer cette annonce ?')">Supprimer / retirer</button>
  </form>
<?php return ob_get_clean(); }
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Mes annonces — GEA-H</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><header class="header"><div class="wrap nav"><a class="brand" href="/">GEA-HOLDING.SAU</a><nav class="menu"><a href="/">Accueil</a><a href="/modules/compte.php">Mon compte</a><a href="/modules/deposer.php">Déposer</a><?=account_link()?></nav></div></header>
<section class="section"><div class="wrap" style="max-width:920px"><h1>📢 Mes annonces</h1><p style="color:var(--muted)">Vous pouvez modifier ou supprimer vos annonces. Toute modification importante repasse en validation admin.</p>
<?php if($msg): ?><p class="status ok">✅ <?=e($msg)?></p><?php endif; ?><?php if($err): ?><p class="status warn">⚠️ <?=e($err)?></p><?php endif; ?>
<a class="btn btn-violet" href="/modules/deposer.php">Déposer une nouvelle annonce</a>
<h2>Annonces publiées / validées</h2>
<?php if(!$props): ?><div class="card"><p>Aucune annonce publiée reliée à votre compte.</p></div><?php endif; ?>
<?php foreach(array_reverse($props) as $it): $imgs=geah_property_images($it); ?><div class="card"><h3><?=e($it['title']??'')?> <span class="status ok" style="font-size:12px"><?=e($it['status']??'Disponible')?></span></h3><p><?=e($it['city']??'')?> · <?=e($it['price']??'')?><?php if(!empty($it['price_fcfa'])): ?> · acompte <?=money($it['price_fcfa'])?><?php endif; ?></p><?php if($imgs): ?><img src="<?=e(media_src($imgs[0]))?>" style="width:140px;height:90px;object-fit:cover;border-radius:10px" alt=""><?php endif; ?><?=edit_form($it,'properties')?></div><?php endforeach; ?>
<h2>Annonces en attente</h2>
<?php if(!$subs): ?><div class="card"><p>Aucune annonce en attente.</p></div><?php endif; ?>
<?php foreach(array_reverse($subs) as $it): $imgs=geah_property_images($it); ?><div class="card"><h3><?=e($it['title']??'')?> <span class="status warn" style="font-size:12px"><?=e($it['status']??'En attente')?></span></h3><p><?=e($it['city']??'')?> · <?=e($it['price']??'')?><?php if(!empty($it['price_fcfa'])): ?> · acompte <?=money($it['price_fcfa'])?><?php endif; ?></p><?php if($imgs): ?><img src="<?=e(media_src($imgs[0]))?>" style="width:140px;height:90px;object-fit:cover;border-radius:10px" alt=""><?php endif; ?><?=edit_form($it,'submissions')?></div><?php endforeach; ?>
</div></section><?=geah_footer()?></body></html>
