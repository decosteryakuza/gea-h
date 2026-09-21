<?php
require_once __DIR__.'/../core.php';
$done=''; $err=''; $info='';

if($_SERVER['REQUEST_METHOD']==='POST' || isset($_GET['resend'])){
    // Renvoyer le lien de validation
    $email = strtolower(trim($_POST['email'] ?? $_GET['resend'] ?? ''));
    if($email===''){ $err="Indiquez votre adresse email."; }
    else {
        $vr = geah_start_account_verification($email);
        if(!empty($vr['sent'])){
            $canaux=implode(' et ', array_map(function($x){ return $x==='email'?'email':'SMS'; }, $vr['sent']));
            $info="Un nouveau lien de validation vient d'être envoyé par ".$canaux.". Pensez à vérifier vos spams.";
        } else {
            $info="Si ce compte existe, un lien lui a été envoyé. (Si rien n'arrive, l'email/SMS n'est peut-être pas encore configuré dans l'administration.)";
        }
    }
}
elseif(isset($_GET['t'], $_GET['e'])){
    // Valider via le lien reçu
    $email=strtolower(trim($_GET['e'])); $token=trim($_GET['t']);
    if(geah_verify_account($email,$token)){ $done='ok'; }
    else { $err="Lien invalide ou expiré. Demandez un nouveau lien ci-dessous."; }
}
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Validation de l'inscription — GEA-HOLDING</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><header class="header"><div class="wrap nav"><a class="brand" href="/">GEA-HOLDING.SAU</a></div></header>
<section class="section"><div class="wrap" style="max-width:520px">
  <h1>🔐 Validation de votre inscription</h1>
  <?php if($done==='ok'): ?>
    <div class="card" style="border-left:4px solid #16a34a">
      <h2>✅ Compte activé !</h2>
      <p>Votre inscription est validée. Vous pouvez maintenant vous connecter à votre espace GEA-HOLDING.</p>
      <a class="btn btn-gold" href="/modules/connexion.php">Se connecter</a>
    </div>
  <?php else: ?>
    <?php if($err): ?><p class="status warn"><?=e($err)?></p><?php endif; ?>
    <?php if($info): ?><p class="status ok"><?=e($info)?></p><?php endif; ?>
    <div class="card">
      <h2>Renvoyer le lien de validation</h2>
      <p style="color:var(--muted)">Entrez l'email utilisé lors de votre inscription. Nous renverrons le lien par email et par SMS (si un numéro est enregistré).</p>
      <form method="post">
        <label>Adresse email<input type="email" name="email" required autocapitalize="none" spellcheck="false" value="<?=e($_POST['email']??($_GET['resend']??''))?>" style="width:100%;padding:12px;border-radius:10px;border:1px solid var(--border);margin:8px 0"></label>
        <button class="btn btn-gold" type="submit" style="width:100%">Renvoyer le lien</button>
      </form>
      <p style="margin-top:12px"><a href="/modules/connexion.php" style="color:var(--gold)">← Retour à la connexion</a></p>
    </div>
  <?php endif; ?>
</div></section><?php echo geah_footer(); ?></body></html>
