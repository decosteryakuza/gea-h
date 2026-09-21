<?php
require_once __DIR__.'/../core.php';
if(is_admin()){ header('Location:/admin/index.php'); exit; }
if(is_user()){ header('Location:/modules/compte.php'); exit; }
$redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? '/modules/compte.php';
if(strpos($redirect, '/') !== 0 || strpos($redirect, '//') === 0) $redirect = '/modules/compte.php';
$tab = (($_GET['tab']??'login')==='register')?'register':'login';
$err=''; $okmsg='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  if(function_exists('geah_csrf_check')) geah_csrf_check();
  if(function_exists('geah_login_disabled') && geah_login_disabled('public')){ $err='Connexion publique temporairement désactivée depuis le mode Recovery VPS.'; $action='blocked'; }
  elseif(function_exists('geah_login_blocked') && geah_login_blocked('public')){ $err='Trop de tentatives. Réessayez dans 15 minutes.'; $action='blocked'; } else {
  $action=$_POST['action']??'login';
  if($action==='forgot'){
    $okmsg="Si cet email existe, un lien de récupération sera envoyé après configuration de l'email/SMS dans l'administration.";
    $tab='login';
  } else {
    $identifier=trim($_POST['identifier']??($_POST['email']??''));
    $email=strtolower(trim($_POST['email']??''));
    $password=trim($_POST['password']??'');
    $password=str_replace(["\xE2\x80\x93","\xE2\x80\x94","\xE2\x88\x92"],'-',$password);
    if($action==='register'){
      list($ok,$res)=user_register($_POST['name']??'',$email,$_POST['phone']??'',$password,$_POST['account_type']??'particulier',$_POST['country']??'',$_POST['city']??'');
      if($ok){
        // Activation IMMEDIATE : on ne bloque plus sur la validation email (souvent non delivree).
        if(function_exists('geah_mark_user_verified')) geah_mark_user_verified($email);
        @geah_start_account_verification($email); // tente d'envoyer un lien de bienvenue si un fournisseur est configure, sans bloquer
        $_SESSION['user']=array_merge((array)$res,['otp_verified'=>true]);
        header('Location:'.$redirect); exit;
      } else { $err=$res; $tab='register'; }
    } else {
      $a=admin_authenticate($identifier,$password);
      if($a){ if(function_exists('geah_login_record_success')) geah_login_record_success('public'); $_SESSION['admin']=$a; header('Location:/admin/index.php'); exit; }
      $u=user_authenticate($identifier,$password);
      if($u){
        {
          if(function_exists('geah_login_record_success')) geah_login_record_success('public');
          $_SESSION['user']=$u; header('Location:'.$redirect); exit;
        }
      }
      if(function_exists('geah_login_record_fail')) geah_login_record_fail('public');
      $err='Email/numéro ou mot de passe incorrect.';
    }
  }
  }
}
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Connexion / Inscription — GEA-HOLDING</title><link rel="stylesheet" href="/assets/css/style.css?v=110">
<style>
.auth-premium{min-height:100vh;background:radial-gradient(circle at 12% 8%,rgba(212,162,58,.24),transparent 28%),linear-gradient(135deg,#061c15,#07111d 55%,#032b20);padding:48px 0}.auth-shell{display:grid;grid-template-columns:1.05fr .95fr;gap:28px;align-items:stretch}.auth-visual{border-radius:30px;padding:34px;position:relative;overflow:hidden;background:linear-gradient(145deg,rgba(255,255,255,.12),rgba(255,255,255,.04));border:1px solid rgba(255,255,255,.16);box-shadow:0 30px 90px rgba(0,0,0,.28)}.auth-visual:after{content:"";position:absolute;width:360px;height:360px;border-radius:50%;right:-90px;bottom:-90px;background:rgba(212,162,58,.16)}.auth-logo{width:96px;height:96px;border-radius:28px;object-fit:cover;background:white;padding:6px;box-shadow:0 16px 34px rgba(0,0,0,.25)}.auth-visual h1{font-size:46px;line-height:1.05;margin:26px 0 12px;color:white}.auth-visual p{font-size:18px;color:#dbeafe;max-width:560px}.auth-points{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:28px}.auth-point{background:rgba(255,255,255,.09);border:1px solid rgba(255,255,255,.12);border-radius:18px;padding:16px;color:white;font-weight:800}.auth-card{background:rgba(255,255,255,.96);color:#101827;border-radius:30px;padding:28px;border:1px solid rgba(255,255,255,.5);box-shadow:0 24px 80px rgba(0,0,0,.25)}.auth-card h2{color:#063d2e;margin:4px 0 6px}.auth-tabs{display:flex;gap:8px;background:#eef4f0;padding:6px;border-radius:18px;margin:18px 0}.auth-tabs a{flex:1;text-align:center;padding:12px;border-radius:14px;text-decoration:none;font-weight:900;color:#315247}.auth-tabs a.on{background:#063d2e;color:white;box-shadow:0 10px 24px rgba(6,61,46,.22)}.social-row{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:15px}.social-btn{border:1px solid #d7e1dc;background:white;border-radius:14px;padding:12px;text-align:center;font-weight:900;color:#24342e;text-decoration:none}.auth-f label{display:block;margin:10px 0 5px;font-size:13px;color:#24443a;font-weight:900}.auth-f input,.auth-f select{width:100%;padding:13px 14px;border-radius:14px;border:1px solid #d7e1dc;background:#f7faf8;color:#0f172a;font-size:15px}.auth-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}.stepbox{background:#f7faf8;border:1px solid #d7e1dc;border-radius:18px;padding:14px;margin:12px 0;color:#49645b}.btn-full{width:100%;margin-top:14px;justify-content:center}.auth-mini{font-size:13px;color:#64748b;text-align:center;margin-top:14px}.forgot-line{display:flex;justify-content:space-between;gap:10px;align-items:center;margin-top:8px}.forgot-line a{color:#0f7a5b;font-weight:900;text-decoration:none}.otp-note{font-size:12px;color:#64748b;margin-top:8px}.status.ok{background:#dcfce7;color:#166534;border-radius:12px;padding:10px}.status.warn{border-radius:12px;padding:10px}@media(max-width:880px){.auth-shell{grid-template-columns:1fr}.auth-visual h1{font-size:34px}.auth-grid,.auth-points,.social-row{grid-template-columns:1fr}.auth-premium{padding:20px 0}}
</style></head><body><header class="header"><div class="wrap nav"><a class="brand" href="/">GEA-HOLDING.SAU</a><nav class="menu"><a href="/">Accueil</a><a href="/modules/properties.php">Biens</a><a href="/modules/connexion.php">Connexion</a><a class="violet" href="/modules/connexion.php?tab=register">Inscription</a></nav></div></header>
<section class="auth-premium"><div class="wrap auth-shell">
  <div class="auth-visual"><img class="auth-logo" src="/assets/img/logo-geah.jpeg" alt="GEA-H"><h1>Votre espace immobilier privé GEA-H</h1><p>Connectez-vous, réservez un bien, suivez vos paiements, recevez vos reçus et préparez vos projets 3D avec l'assistance GEA-H IA.</p><div class="auth-points"><div class="auth-point">🏠 Favoris & réservations</div><div class="auth-point">📄 Reçus numériques</div><div class="auth-point">💳 Paiement sécurisé</div><div class="auth-point">🤖 Assistance IA</div></div></div>
  <div class="auth-card"><h2><?=$tab==='register'?'Créer un compte':'Connexion'?></h2><p style="margin:0;color:#64748b">Particuliers, agences, hôtels, promoteurs et prestataires.</p>
    <div class="auth-tabs"><a href="?tab=login<?= $redirect!=='/modules/compte.php'?'&redirect='.rawurlencode($redirect):'' ?>" class="<?=$tab==='login'?'on':''?>">Connexion</a><a href="?tab=register<?= $redirect!=='/modules/compte.php'?'&redirect='.rawurlencode($redirect):'' ?>" class="<?=$tab==='register'?'on':''?>">Inscription</a></div>
    <?php if($err): ?><p class="status warn"><?=e($err)?></p><?php endif; ?><?php if($okmsg): ?><p class="status ok"><?=e($okmsg)?></p><?php endif; ?>
    <div class="social-row"><a class="social-btn" href="#" onclick="alert('Connexion Google prête à brancher : ajoutez Google Client ID dans Admin > API.');return false;">🌐 Google</a><a class="social-btn" href="#" onclick="alert('Connexion Facebook prête à brancher : ajoutez Meta App ID dans Admin > API.');return false;">📘 Facebook</a></div>
    <?php if($tab==='register'): ?>
    <form class="auth-f" method="post"><?php if(function_exists("geah_csrf_field")) echo geah_csrf_field(); ?><input type="hidden" name="action" value="register"><input type="hidden" name="redirect" value="<?=e($redirect)?>">
      <div class="stepbox"><b>Étape 1/3</b> — Identité du compte</div><label>Type de compte</label><select name="account_type"><option value="particulier">Particulier</option><option value="agence">Agence immobilière</option><option value="hotel">Hôtel / Résidence meublée</option><option value="promoteur">Promoteur</option><option value="prestataire">Artisan / Prestataire</option></select><label>Nom complet ou société</label><input name="name" required value="<?=e($_POST['name']??'')?>">
      <div class="stepbox"><b>Étape 2/3</b> — Contact & localisation</div><div class="auth-grid"><div><label>Téléphone / WhatsApp</label><input name="phone" placeholder="+225..." required value="<?=e($_POST['phone']??'')?>"></div><div><label>Email</label><input type="email" name="email" required autocapitalize="none" spellcheck="false" value="<?=e($_POST['email']??'')?>"></div></div><div class="auth-grid"><div><label>Pays</label><input name="country" placeholder="Côte d'Ivoire" value="<?=e($_POST['country']??'')?>"></div><div><label>Ville</label><input name="city" placeholder="Abidjan, Bonoua..." value="<?=e($_POST['city']??'')?>"></div></div>
      <div class="stepbox"><b>Étape 3/3</b> — Sécurité</div><label>Mot de passe</label><input type="password" name="password" required autocapitalize="none"><div class="otp-note">🔐 Après inscription, vous recevrez un <b>lien de validation par email et/ou SMS</b>. Cliquez dessus pour activer votre compte.</div><button class="btn btn-gold btn-full">Créer mon espace GEA-H</button>
    </form>
    <?php else: ?>
    <form class="auth-f" method="post"><?php if(function_exists("geah_csrf_field")) echo geah_csrf_field(); ?><input type="hidden" name="action" value="login"><input type="hidden" name="redirect" value="<?=e($redirect)?>"><label>Email ou numéro de téléphone</label><input type="text" name="identifier" required autocapitalize="none" spellcheck="false" placeholder="Email ou numéro enregistré" value="<?=e($_POST['identifier']??($_POST['email']??''))?>"><label>Mot de passe</label><input type="password" name="password" required autocapitalize="none"><div class="forgot-line"><span></span><a href="#" onclick="document.getElementById('forgotBox').style.display='block';return false;">Mot de passe oublié ?</a></div><button class="btn btn-gold btn-full">Se connecter</button></form>
    <p class="auth-mini" style="margin-top:8px">Pas reçu le lien de validation ? <a href="/modules/verify-account.php" style="color:#0f7a5b;font-weight:900">Renvoyer le lien</a></p>
    <form id="forgotBox" class="auth-f" method="post" style="display:none;margin-top:14px"><?php if(function_exists("geah_csrf_field")) echo geah_csrf_field(); ?><input type="hidden" name="action" value="forgot"><label>Email de récupération</label><input type="email" name="email" required><button class="btn btn-light btn-full">Envoyer le lien de récupération</button></form>
    <?php endif; ?><p class="auth-mini">Admin masqué du menu public. L'accès administrateur reste disponible uniquement par URL dédiée.</p>
  </div>
</div></section><?php echo geah_footer(); ?></body></html>
