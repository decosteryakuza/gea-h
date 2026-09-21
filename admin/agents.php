<?php require_once __DIR__.'/../core.php'; require_once __DIR__.'/auth.php'; require_role('properties');
$items=data_list('agents'); $msg='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  if(function_exists('geah_csrf_check')) geah_csrf_check();
  if(isset($_POST['delete'])){
    $id=(int)$_POST['delete']; $items=array_values(array_filter($items,fn($x)=>($x['id']??0)!=$id)); $msg='✅ Agent supprimé.';
  } else {
    $email=strtolower(trim($_POST['email']??'')); $phone=trim($_POST['phone']??''); $name=trim($_POST['name']??'');
    $new=['id'=>time().rand(10,99),'date'=>now(),'name'=>$name,'role'=>$_POST['role']??'Agent terrain','phone'=>$phone,'email'=>$email,'mission'=>$_POST['mission']??'','status'=>$_POST['status']??'Actif'];
    $items[]=$new;
    if($email!==''){
      [$ok,$res]=geah_create_or_update_staff_account($name,$email,$phone,'agent',['properties','ged']);
      $msg=$ok?'✅ Agent ajouté. Compte créé automatiquement. Mot de passe temporaire : '.$res:'⚠️ Agent ajouté mais compte non créé : '.$res;
    } else { $msg='✅ Agent ajouté. Ajoutez un email pour créer automatiquement son compte.'; }
  }
  data_save('agents',$items); log_action('Mise à jour agents');
}
?><!doctype html><html><head><meta charset="utf-8"><link rel="stylesheet" href="/assets/css/style.css?v=110"></head><body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main"><h1>Agents et missions</h1><?php if($msg) echo '<p class="status '.(strpos($msg,'✅')===0?'ok':'warn').'">'.e($msg).'</p>'; ?><div class="card"><b>Compte automatique</b><p>Quand vous enregistrez un agent avec son email et son numéro, un compte Agent est créé automatiquement. Mot de passe temporaire : <b>GEAH@</b> + les 4 derniers chiffres du téléphone. L’agent devra le modifier à la première connexion.</p></div><form class="card" method="post"><?php if(function_exists('geah_csrf_field')) echo geah_csrf_field(); ?><div class="form-grid"><label>Nom agent<input type="text" name="name" required></label><label>Rôle<input type="text" name="role" value="Agent terrain"></label><label>Téléphone<input type="text" name="phone" placeholder="0700000000"></label><label>Email<input type="email" name="email" placeholder="agent@..."></label><label class="full">Mission<textarea name="mission" rows="4"></textarea></label><label>Statut<select name="status"><option>Actif</option><option>Suspendu</option><option>En mission</option></select></label></div><button class="btn btn-primary">Ajouter</button></form><table class="table"><tr><th>Nom agent</th><th>Rôle</th><th>Téléphone</th><th>Email</th><th>Mission</th><th>Statut</th><th></th></tr><?php foreach(array_reverse($items) as $item): ?><tr><td><?=e($item['name']??'')?></td><td><?=e($item['role']??'')?></td><td><?=e($item['phone']??'')?></td><td><?=e($item['email']??'')?></td><td><?=e($item['mission']??'')?></td><td><?=e($item['status']??'')?></td><td><form method="post"><?php if(function_exists('geah_csrf_field')) echo geah_csrf_field(); ?><button class="btn danger" name="delete" value="<?=e($item['id'])?>">Supprimer</button></form></td></tr><?php endforeach; ?></table></main></div></body></html>
