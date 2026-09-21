<?php require_once __DIR__.'/../core.php'; require_once __DIR__.'/auth.php';
$c=payments_config();
if($_SERVER['REQUEST_METHOD']==='POST'){
    foreach(['cinetpay_apikey','cinetpay_site_id','cinetpay_mode','bank_name','rib','momo_orange','momo_mtn','momo_moov','momo_wave','plan_price','transfer_pwd'] as $k) $c[$k]=trim($_POST[$k]??''); $c['auto_transfer']=isset($_POST['auto_transfer'])?1:0;
    save_payments_config($c); header('Location:/admin/payments.php?saved=1'); exit;
}
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Paiements</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main">
<h1>Paiements & Recharge</h1>
<div class="card" style="border-left:4px solid #d4a23a"><h2>Diagnostic paiement</h2><p><b>CinetPay :</b> <?=($c['cinetpay_apikey']&&$c['cinetpay_site_id'])?'✅ configuré':'⚠️ non configuré'?> — <b>Mode :</b> <?=e($c['cinetpay_mode']??'PROD')?></p><p><b>Mobile Money direct :</b> <?=($c['momo_orange']||$c['momo_mtn']||$c['momo_moov']||$c['momo_wave'])?'✅ numéros renseignés':'⚠️ aucun numéro renseigné'?></p><p><a class="btn btn-gold" href="/admin/payment-test.php">Tester le paiement</a></p></div>
<?php if(isset($_GET['saved'])) echo '<p class="status ok">✅ Enregistré</p>'; ?>
<form class="card" method="post">
  <h2>CinetPay (Mobile Money + Carte bancaire 3D Secure)</h2>
  <p style="color:#6b7280;font-size:13px">Crée un compte sur cinetpay.com, récupère ton <b>API Key</b> et ton <b>Site ID</b>. CinetPay gère Orange/MTN/Moov/Wave et Visa/Mastercard avec 3D Secure.</p>
  <div class="form-grid">
    <label>API Key<input name="cinetpay_apikey" value="<?=e($c['cinetpay_apikey'])?>"></label>
    <label>Site ID<input name="cinetpay_site_id" value="<?=e($c['cinetpay_site_id'])?>"></label>
    <label>Mode<select name="cinetpay_mode"><option value="PROD" <?=$c['cinetpay_mode']==='PROD'?'selected':''?>>Production (vrais paiements)</option><option value="TEST" <?=$c['cinetpay_mode']==='TEST'?'selected':''?>>Test</option></select></label>
  </div>
  <h2 style="margin-top:18px">Virement / dépôt manuel (affiché au client)</h2>
  <div class="form-grid">
    <label>Banque<input name="bank_name" value="<?=e($c['bank_name'])?>" placeholder="Ex: ECOBANK"></label>
    <label>RIB / IBAN<input name="rib" value="<?=e($c['rib'])?>"></label>
    <label>Orange Money<input name="momo_orange" value="<?=e($c['momo_orange'])?>" placeholder="+225 07..."></label>
    <label>MTN MoMo<input name="momo_mtn" value="<?=e($c['momo_mtn'])?>"></label>
    <label>Moov Money<input name="momo_moov" value="<?=e($c['momo_moov'])?>"></label>
    <label>Wave<input name="momo_wave" value="<?=e($c['momo_wave'])?>"></label>
  </div>
  <h2 style="margin-top:18px">💸 Transfert automatique au vendeur (CinetPay Transfert)</h2><p style="color:var(--muted);font-size:13px;margin-top:-4px">Permet de reverser automatiquement la part du vendeur sur son Mobile Money. Nécessite l'activation du service « Transfert » sur ton compte CinetPay + le mot de passe transfert + un solde suffisant.</p><div class="form-grid"><label>Mot de passe transfert CinetPay<input name="transfer_pwd" type="password" value="<?=e($c['transfer_pwd']??'')?>"></label><label style="display:flex;align-items:center;gap:8px;margin-top:22px"><input type="checkbox" name="auto_transfer" value="1" <?=!empty($c['auto_transfer'])?'checked':''?>> Transférer automatiquement à la confirmation du paiement</label></div><h2 style="margin-top:18px">Plan maison 3D</h2><div class="form-grid"><label>Prix du plan PDF (FCFA)<input name="plan_price" value="<?=e($c['plan_price']??5000)?>"></label></div><button class="btn btn-primary">Enregistrer</button>
</form>
<p><a class="btn btn-gold" href="/recharge.php" target="_blank">Ouvrir la page de recharge client →</a></p>
</main></div></body></html>
