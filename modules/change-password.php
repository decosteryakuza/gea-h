<?php require_once __DIR__.'/../core.php'; if(!is_user()){ header('Location:/modules/connexion.php?redirect=/modules/change-password.php'); exit; }
$u=current_user(); $msg='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  if(function_exists('geah_csrf_check')) geah_csrf_check();
  $old=trim($_POST['old']??''); $new=trim($_POST['new']??''); $confirm=trim($_POST['confirm']??'');
  if($new!==$confirm) $msg='⚠️ Les nouveaux mots de passe ne correspondent pas.';
  elseif(strlen($new)<8) $msg='⚠️ Le mot de passe doit contenir au moins 8 caractères.';
  elseif(!user_authenticate($u['email'],$old)) $msg='⚠️ Ancien mot de passe incorrect.';
  elseif(geah_update_user_password($u['email'],$new)) $msg='✅ Mot de passe modifié.'; else $msg='⚠️ Impossible de modifier le mot de passe.';
}
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Modifier mes accès</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head><body><header class="header"><div class="wrap nav"><a class="brand" href="/">GEA-HOLDING.SAU</a><nav class="menu"><a href="/modules/compte.php">Mon espace</a><a href="/modules/logout.php">Déconnexion</a></nav></div></header><section class="section"><div class="wrap"><h1>Modifier mes accès</h1><p>Vous pouvez changer votre mot de passe quand vous le souhaitez. Ce changement n’est pas imposé après chaque mise à jour.</p><?php if($msg) echo '<p class="status '.(strpos($msg,'✅')===0?'ok':'warn').'">'.e($msg).'</p>'; ?><form class="card" method="post" style="max-width:520px"><?php if(function_exists('geah_csrf_field')) echo geah_csrf_field(); ?><label>Ancien mot de passe<input type="password" name="old" required></label><label>Nouveau mot de passe<input type="password" name="new" required></label><label>Confirmer<input type="password" name="confirm" required></label><button class="btn btn-primary">Enregistrer</button></form></div></section><?php echo geah_footer(); ?></body></html>
