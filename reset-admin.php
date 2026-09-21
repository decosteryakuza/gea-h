<?php
/* =====================================================================
   reset-admin.php — Réinitialiser l'accès SUPER ADMIN de GEA-HOLDING
   ⚠️ A SUPPRIMER IMMEDIATEMENT après usage (sécurité).
   Utilisation : déposer ce fichier dans httpdocs, puis ouvrir
   https://gea-holding.net/reset-admin.php
   ===================================================================== */
require_once __DIR__.'/core.php';

$done=''; $err='';
$existing=[];
foreach(read_json('admin_users.json',[]) as $u){
    $existing[]=['email'=>$u['email']??'', 'role'=>$u['role']??'', 'name'=>$u['name']??''];
}

if($_SERVER['REQUEST_METHOD']==='POST'){
    $email=strtolower(trim($_POST['email']??''));
    $pass=$_POST['pass']??'';
    $pass2=$_POST['pass2']??'';
    if($email===''){ $err="Indiquez un email."; }
    elseif(strlen($pass)<6){ $err="Le mot de passe doit faire au moins 6 caractères."; }
    elseif($pass!==$pass2){ $err="Les deux mots de passe ne sont pas identiques."; }
    else{
        $users=read_json('admin_users.json',[]);
        $found=false;
        foreach($users as &$u){
            if(strtolower($u['email']??'')===$email){
                $u['pass']=password_hash($pass,PASSWORD_DEFAULT);
                $u['role']='super';
                $u['must_change_password']=false;
                if(function_exists('geah_caps_labels')) $u['permissions']=array_keys(geah_caps_labels());
                $found=true;
            }
        }
        unset($u);
        if(!$found){
            $users[]=[
                'id'=>time(),
                'name'=>'Super Administrateur',
                'email'=>$email,
                'role'=>'super',
                'pass'=>password_hash($pass,PASSWORD_DEFAULT),
                'must_change_password'=>false,
                'permissions'=>function_exists('geah_caps_labels')?array_keys(geah_caps_labels()):[],
                'date'=>now()
            ];
        }
        write_json('admin_users.json',$users);
        $done = $found ? 'reset' : 'created';
    }
}
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Réinitialiser l'accès admin — GEA-HOLDING</title>
<style>
body{font-family:system-ui,Arial,sans-serif;background:#0a1420;color:#eaf2f8;margin:0;padding:24px}
.box{max-width:480px;margin:auto;background:#0f1f30;border:1px solid #1d3346;border-radius:16px;padding:22px}
h1{font-size:20px;margin:0 0 6px;color:#d4a23a}
label{display:block;margin:12px 0 4px;font-size:14px}
input{width:100%;box-sizing:border-box;padding:12px;border-radius:10px;border:1px solid #2a3f4d;background:#07111d;color:#fff;font-size:15px}
button{margin-top:16px;width:100%;padding:13px;border:0;border-radius:10px;background:#d4a23a;color:#1a1205;font-weight:bold;font-size:16px;cursor:pointer}
.ok{background:#073b22;border:1px solid #20b26b;color:#dcffe9;padding:12px;border-radius:12px;margin:10px 0}
.err{background:#421010;border:1px solid #d44;color:#fff;padding:12px;border-radius:12px;margin:10px 0}
.warn{background:#3a2a06;border:1px solid #d4a23a;color:#ffe9b8;padding:12px;border-radius:12px;margin:10px 0;font-size:14px}
.mini{opacity:.8;font-size:13px}
code{background:#07111d;padding:2px 6px;border-radius:6px}
</style></head><body><div class="box">
<h1>🔑 Réinitialiser l'accès Super Admin</h1>
<p class="mini">Définissez un email + un nouveau mot de passe pour votre compte super administrateur.</p>

<?php if($done): ?>
  <div class="ok">
    <b>✅ <?= $done==='reset' ? 'Mot de passe réinitialisé !' : 'Compte super admin créé !' ?></b><br>
    Vous pouvez maintenant vous connecter sur <code>/admin/login.php</code> avec cet email et ce nouveau mot de passe.
  </div>
  <div class="warn">⚠️ <b>TRÈS IMPORTANT :</b> supprimez maintenant le fichier <code>reset-admin.php</code> de votre serveur (via Plesk → Gestionnaire de fichiers), pour que personne d'autre ne puisse l'utiliser.</div>
  <p><a href="/admin/login.php" style="color:#d4a23a">→ Aller à la page de connexion</a></p>
<?php else: ?>
  <?php if($err): ?><div class="err"><?=htmlspecialchars($err)?></div><?php endif; ?>
  <?php if($existing): ?>
    <div class="mini" style="margin:8px 0">Comptes admin déjà existants (utilisez le même email pour réinitialiser, ou un nouveau pour en créer un) :
      <ul><?php foreach($existing as $x): ?><li><b><?=htmlspecialchars($x['email'])?></b> — <?=htmlspecialchars($x['role'])?></li><?php endforeach; ?></ul>
    </div>
  <?php endif; ?>
  <form method="post">
    <label>Email du super admin</label>
    <input type="email" name="email" required autocapitalize="none" spellcheck="false" placeholder="admin@gea-holding.net" value="<?=htmlspecialchars($_POST['email']??'')?>">
    <label>Nouveau mot de passe</label>
    <input type="password" name="pass" required minlength="6" placeholder="au moins 6 caractères">
    <label>Confirmez le mot de passe</label>
    <input type="password" name="pass2" required minlength="6">
    <button type="submit">Réinitialiser mon accès</button>
  </form>
  <div class="warn" style="margin-top:14px">⚠️ Après vous être connecté, <b>supprimez ce fichier</b> <code>reset-admin.php</code> de votre serveur.</div>
<?php endif; ?>
</div></body></html>
