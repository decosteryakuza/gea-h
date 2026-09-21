<?php
require_once __DIR__.'/../core.php';
if(session_status()===PHP_SESSION_NONE) session_start();
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    if(function_exists('geah_csrf_check')) geah_csrf_check();
    if(function_exists('geah_login_disabled') && geah_login_disabled('admin')){ $error='Connexion administrateur temporairement désactivée depuis le mode Recovery VPS.'; }
    elseif(function_exists('geah_login_blocked') && geah_login_blocked('admin')){ $error='Trop de tentatives. Réessayez dans 15 minutes.'; } else {
    $email=strtolower(trim($_POST['email']??''));
    $password=trim($_POST['password']??'');
    // Normaliser les tirets que certains claviers mobiles insèrent (– — −) en tiret simple -
    $password=str_replace(["\xE2\x80\x93","\xE2\x80\x94","\xE2\x88\x92"],'-',$password);
    $u=admin_authenticate($email,$password);
    if($u){ if(function_exists('geah_login_record_success')) geah_login_record_success('admin'); $_SESSION['admin']=$u; header('Location:'.geah_admin_landing()); exit; }
    if(function_exists('geah_login_record_fail')) geah_login_record_fail('admin');
    $error='Email ou mot de passe incorrect.';
    }
}
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Administration GEA-H</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body class="login">
<form class="login-card" method="post">
  <h2>Administration GEA-H</h2>
  <?php if($error!==''): ?><p class="error" style="color:#c0392b;font-weight:600"><?=htmlspecialchars($error,ENT_QUOTES)?></p><?php endif; ?>
  <?php if(function_exists("geah_csrf_field")) echo geah_csrf_field(); ?>
  <input type="text" name="email" placeholder="Email ou numéro" required autocapitalize="none" autocorrect="off" spellcheck="false" style="display:block;width:100%;margin:8px 0;padding:12px;border:1px solid #cbd5e1;border-radius:8px">
  <input type="password" name="password" id="pw" placeholder="Mot de passe" required autocapitalize="none" autocorrect="off" spellcheck="false" style="display:block;width:100%;margin:8px 0;padding:12px;border:1px solid #cbd5e1;border-radius:8px">
  <label style="display:block;margin:4px 0 10px;font-size:14px;color:#475569"><input type="checkbox" id="showpw"> Afficher le mot de passe</label>
  <button class="btn btn-primary" style="width:100%;margin-top:6px">Se connecter</button>
</form>
<script>
(function(){try{var s=localStorage.getItem('geah_theme');if(s==='light')document.body.classList.add('light-mode');}catch(e){}
var c=document.getElementById('showpw'),p=document.getElementById('pw');
if(c&&p)c.addEventListener('change',function(){p.type=this.checked?'text':'password';});})();
</script>
</body></html>
