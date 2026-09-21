<?php require_once __DIR__.'/../core.php'; require_once __DIR__.'/auth.php'; require_role('users');
$users=geah_admin_users();
$msg='';
$roles=auth_roles_labels();
$caps=geah_caps_labels();
if($_SERVER['REQUEST_METHOD']==='POST'){
    if(function_exists('geah_csrf_check')) geah_csrf_check();
    $action=$_POST['action']??'';
    if($action==='add'){
        $email=strtolower(trim($_POST['email']??'')); $name=trim($_POST['name']??''); $role=trim($_POST['role']??'agent'); $pass=trim($_POST['password']??'');
        if(strtolower($role)==='super'){ $role='agent'; }
        $perms=array_values(array_intersect(array_keys($caps), $_POST['permissions']??[]));
        $exists=false; foreach($users as $u){ if(strtolower($u['email']??'')===$email) $exists=true; }
        if($email==='' || $pass==='' || $name===''){ $msg='⚠️ Remplissez le nom, l\'email et le mot de passe.'; }
        elseif($exists){ $msg='⚠️ Cet email existe déjà.'; }
        elseif($role===''){ $msg='⚠️ Indiquez un poste.'; }
        else { $users[]=['id'=>time().rand(10,99),'name'=>$name,'email'=>$email,'role'=>$role,'phone'=>trim($_POST['phone']??''),'pass'=>password_hash($pass,PASSWORD_DEFAULT),'must_change_password'=>!empty($_POST['force_change']),'permissions'=>$perms,'date'=>now()]; geah_save_admin_users($users); log_action('Utilisateur créé : '.$email.' ('.$role.')'); $msg='✅ Utilisateur créé.'; }
    } elseif($action==='save_perms'){
        $id=(int)($_POST['id']??0); $perms=array_values(array_intersect(array_keys($caps), $_POST['permissions']??[]));
        $newRole=trim($_POST['role']??'agent'); if(strtolower($newRole)==='super'){ $newRole='agent'; }
        foreach($users as &$u){ if((int)($u['id']??0)===$id && ($u['role']??'')!=='super'){ $u['permissions']=$perms; $u['role']=$newRole!==''?$newRole:($u['role']??'agent'); $u['name']=trim($_POST['name']??($u['name']??'')); $u['phone']=trim($_POST['phone']??($u['phone']??'')); }} unset($u);
        geah_save_admin_users($users); $msg='✅ Permissions mises à jour.';
    } elseif($action==='reset_pw'){
        $id=(int)($_POST['id']??0); $new='GEAH@'.rand(1000,9999); $mail='';
        foreach($users as &$u){ if((int)($u['id']??0)===$id){ $u['pass']=password_hash($new,PASSWORD_DEFAULT); $u['must_change_password']=!empty($_POST['force_change']); $mail=$u['email']??''; }} unset($u);
        geah_save_admin_users($users); $msg='✅ Nouveau mot de passe temporaire pour '.$mail.' : '.$new;
    } elseif($action==='del'){
        $id=(int)$_POST['id'];
        $users=array_values(array_filter($users,fn($u)=>((int)($u['id']??0)!==$id) || (($u['role']??'')==='super'))); geah_save_admin_users($users); $msg='✅ Utilisateur supprimé.';
    }
    $users=geah_admin_users();
}
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Utilisateurs & Rôles</title><link rel="stylesheet" href="/assets/css/style.css?v=110"><style>.perm-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:8px}.perm-grid label{background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:8px;font-size:13px;color:#0f172a;display:flex;align-items:center;gap:8px}.user-box{border-left:4px solid #013328}@media(max-width:900px){.perm-grid{grid-template-columns:1fr}}</style></head>
<body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main">
<h1>Utilisateurs, rôles & permissions</h1>
<?php if($msg) echo '<p class="status '.(strpos($msg,'✅')===0?'ok':'warn').'">'.e($msg).'</p>'; ?>
<div class="card user-box"><b>Règle de sécurité</b><p>Le module <b>Lotissement IA / Urbanisation</b> est réservé au Super Admin par défaut. Vous pouvez donner l'autorisation à un DG, agent ou autre personnel depuis cette page. Même si quelqu'un connaît l'URL, l'accès est bloqué côté serveur.</p></div>
<form class="card" method="post" style="max-width:760px">
  <?php if(function_exists('geah_csrf_field')) echo geah_csrf_field(); ?><input type="hidden" name="action" value="add">
  <h2>Créer un compte personnel</h2>
  <div class="form-grid"><label>Nom complet<input name="name" required></label><label>Email<input type="email" name="email" required></label><label>Téléphone<input name="phone"></label><label>Mot de passe temporaire<input name="password" value="GEAH@1234" required></label><label style="display:flex;gap:8px;align-items:center;margin-top:22px"><input type="checkbox" name="force_change" value="1"> Forcer changement à la prochaine connexion</label><label>Poste (saisie libre, ou choisissez une suggestion)<input name="role" list="geahRolesList" placeholder="Ex : Agent, Chef de chantier, Réceptionniste..." required></label><datalist id="geahRolesList"><?php foreach($roles as $k=>$v): if($k==='super') continue; ?><option value="<?=e($k)?>"><?=e($v)?></option><?php endforeach; ?></datalist></div>
  <h3>Permissions supplémentaires</h3><div class="perm-grid"><?php foreach($caps as $k=>$v): ?><label><input type="checkbox" name="permissions[]" value="<?=e($k)?>"> <?=e($v)?></label><?php endforeach; ?></div>
  <button class="btn btn-primary" style="margin-top:10px">Créer</button>
</form>
<h2>Comptes existants</h2>
<?php foreach($users as $u): $isSuper=($u['role']??'')==='super'; ?>
<form class="card" method="post">
  <?php if(function_exists('geah_csrf_field')) echo geah_csrf_field(); ?><input type="hidden" name="id" value="<?=e($u['id']??'')?>">
  <div class="form-grid"><label>Nom<input name="name" value="<?=e($u['name']??'')?>" <?=$isSuper?'readonly':''?>></label><label>Email<input value="<?=e($u['email']??'')?>" readonly></label><label>Téléphone<input name="phone" value="<?=e($u['phone']??'')?>" <?=$isSuper?'readonly':''?>></label><label>Poste (saisie libre)<input name="role" list="geahRolesList" value="<?=e($u['role']??'agent')?>" <?=$isSuper?'readonly':''?>></label></div>
  <?php if(!empty($u['must_change_password'])): ?><p class="status warn">Changement de mot de passe conseillé / forcé uniquement si l'admin l'a activé.</p><?php endif; ?>
  <h3>Permissions</h3><div class="perm-grid"><?php foreach($caps as $k=>$v): ?><label><input type="checkbox" name="permissions[]" value="<?=e($k)?>" <?= $isSuper||in_array($k,$u['permissions']??[],true)?'checked':'' ?> <?=$isSuper?'disabled':''?>> <?=e($v)?></label><?php endforeach; ?></div>
  <div style="margin-top:10px"><?php if(!$isSuper): ?><button class="btn btn-primary" name="action" value="save_perms">Enregistrer</button> <label style="display:inline-flex;gap:6px;align-items:center;margin-left:10px"><input type="checkbox" name="force_change" value="1"> Forcer changement</label> <button class="btn" name="action" value="reset_pw" onclick="return confirm('Réinitialiser le mot de passe ?')">Réinitialiser mot de passe</button> <button class="btn danger" name="action" value="del" onclick="return confirm('Supprimer ce compte ?')">Supprimer</button><?php else: ?><span class="status ok">Super Administrateur</span><?php endif; ?></div>
</form>
<?php endforeach; ?>
</main></div></body></html>
