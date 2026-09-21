<?php require_once __DIR__.'/../core.php'; require_once __DIR__.'/auth.php';
$u=auth_user(); $msg=''; $first=($_GET['first']??'')==='1';
if($_SERVER['REQUEST_METHOD']==='POST'){
  if(function_exists('geah_csrf_check')) geah_csrf_check();
  $old=trim($_POST['old']??''); $new=trim($_POST['new']??''); $confirm=trim($_POST['confirm']??'');
  if($new!==$confirm) $msg='⚠️ Les nouveaux mots de passe ne correspondent pas.';
  elseif(strlen($new)<8) $msg='⚠️ Le mot de passe doit contenir au moins 8 caractères.';
  else{
    $valid=false; foreach(geah_admin_users() as $x){ if(strtolower($x['email']??'')===strtolower($u['email']??'') && password_verify($old,$x['pass']??'')) $valid=true; }
    if(!$valid) $msg='⚠️ Ancien mot de passe incorrect.';
    elseif(geah_update_admin_password($u['email'],$new,true)){ $_SESSION['admin']['must_change_password']=false; $msg='✅ Mot de passe modifié.'; }
    else $msg='⚠️ Impossible de modifier le mot de passe.';
  }
}
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Modifier mot de passe</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head><body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main"><h1>Modifier mes accès</h1><p>Vous pouvez changer votre mot de passe quand vous le souhaitez. Ce changement n’est pas imposé après chaque mise à jour.</p><?php if($first): ?><p class="status warn">Pour votre sécurité, modifiez votre mot de passe temporaire avant de continuer.</p><?php endif; ?><?php if($msg) echo '<p class="status '.(strpos($msg,'✅')===0?'ok':'warn').'">'.e($msg).'</p>'; ?><form class="card" method="post" style="max-width:520px"><?php if(function_exists('geah_csrf_field')) echo geah_csrf_field(); ?><label>Ancien mot de passe<input type="password" name="old" required></label><label>Nouveau mot de passe<input type="password" name="new" required></label><label>Confirmer<input type="password" name="confirm" required></label><button class="btn btn-primary">Enregistrer</button></form><div class="card"><b>Sécurité</b><p>Les procédures d’urgence VPS sont conservées dans le mode d’emploi administrateur téléchargeable et ne sont pas affichées dans l’interface.</p></div></main></div></body></html>
