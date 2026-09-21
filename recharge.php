<?php
require_once __DIR__.'/core.php';
require_user_login($_SERVER['REQUEST_URI'] ?? '/recharge.php');
$accounts=data_list('bank_accounts');
$pc=payments_config();
$err=''; $info=''; $virement=null;

if($_SERVER['REQUEST_METHOD']==='POST'){
    $accId=(int)($_POST['account']??0);
    $amount=(float)($_POST['amount']??0);
    $method=$_POST['method']??'';
    $acc=null; foreach($accounts as $a){ if(($a['id']??0)==$accId){ $acc=$a; break; } }
    if(!$acc) $err='Choisis un compte valide.';
    elseif($amount<100) $err='Montant minimum : 100 FCFA.';
    else {
        if($method==='virement'){
            $rech=read_json('bank_recharges.json',[]);
            $txid='VIR'.time().rand(100,999);
            $rech[]=['txid'=>$txid,'account_id'=>$accId,'amount'=>(int)$amount,'method'=>'Virement','status'=>'pending','date'=>now()];
            write_json('bank_recharges.json',$rech);
            $virement=['ref'=>$txid,'amount'=>(int)$amount,'name'=>$acc['name']];
            $info='Demande enregistrée. Effectue le virement avec la référence ci-dessous ; le solde sera crédité après réception.';
        } else {
            $channels = ($method==='carte') ? 'CREDIT_CARD' : 'MOBILE_MONEY';
            $txid='GEAH'.time().rand(100,999);
            $rech=read_json('bank_recharges.json',[]);
            $rech[]=['txid'=>$txid,'account_id'=>$accId,'amount'=>(int)(round($amount/5)*5),'method'=>($method==='carte'?'Carte':'Mobile Money'),'status'=>'pending','date'=>now()];
            write_json('bank_recharges.json',$rech);
            $res=cinetpay_init($txid,$amount,'Recharge solde GEA-H Bank - '.$acc['name'],$channels,$acc['name']);
            if($res['ok']){ $_SESSION['last_txid']=$txid; header('Location:'.$res['url']); exit; }
            else $err=$res['error'];
        }
    }
}
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Recharger mon solde — GEA-H Bank</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><header class="header"><div class="wrap nav"><a class="brand" href="/">GEA-H BANK</a></div></header>
<section class="section"><div class="wrap" style="max-width:640px">
<h1>Recharger mon solde</h1>
<?php if($err): ?><p class="status warn">⚠️ <?=e($err)?></p><?php endif; ?>
<?php if($info): ?><p class="status ok">✅ <?=e($info)?></p><?php endif; ?>

<?php if($virement): ?>
  <div class="card">
    <h2>Coordonnées pour le virement</h2>
    <p>Montant à envoyer : <b><?=money($virement['amount'])?></b><br>
    Référence à indiquer : <b><?=e($virement['ref'])?></b> (compte : <?=e($virement['name'])?>)</p>
    <ul>
      <?php if($pc['bank_name']||$pc['rib']): ?><li><b>Banque :</b> <?=e($pc['bank_name'])?> — RIB/IBAN : <?=e($pc['rib'])?></li><?php endif; ?>
      <?php if($pc['momo_orange']): ?><li><b>Orange Money :</b> <?=e($pc['momo_orange'])?></li><?php endif; ?>
      <?php if($pc['momo_mtn']): ?><li><b>MTN MoMo :</b> <?=e($pc['momo_mtn'])?></li><?php endif; ?>
      <?php if($pc['momo_moov']): ?><li><b>Moov Money :</b> <?=e($pc['momo_moov'])?></li><?php endif; ?>
      <?php if($pc['momo_wave']): ?><li><b>Wave :</b> <?=e($pc['momo_wave'])?></li><?php endif; ?>
    </ul>
    <p style="color:#6b7280;font-size:13px">Après réception, un administrateur validera la recharge.</p>
  </div>
<?php endif; ?>

<form class="card" method="post">
  <h2>Choisis ton compte et le montant</h2>
  <div class="form-grid">
    <label>Compte<select name="account" required><option value="">— choisir —</option><?php foreach($accounts as $a): ?><option value="<?=e($a['id'])?>"><?=e($a['name'])?><?php if(!empty($a['phone'])) echo ' ('.e($a['phone']).')'; ?></option><?php endforeach; ?></select></label>
    <label>Montant (FCFA)<input type="number" name="amount" min="100" step="5" required></label>
  </div>
  <h3>Moyen de paiement</h3>
  <label style="display:block;margin:6px 0"><input type="radio" name="method" value="momo" checked> 📱 Mobile Money (Orange, MTN, Moov, Wave)</label>
  <label style="display:block;margin:6px 0"><input type="radio" name="method" value="carte"> 💳 Carte bancaire (Visa / Mastercard — 3D Secure)</label>
  <label style="display:block;margin:6px 0"><input type="radio" name="method" value="virement"> 🏦 Virement / dépôt manuel</label>
  <button class="btn btn-primary" style="margin-top:10px">Continuer</button>
  <?php if($pc['cinetpay_apikey']===''): ?><p style="color:#c0392b;font-size:13px;margin-top:8px">⚠️ Paiement en ligne non encore activé (l'administrateur doit configurer CinetPay dans Admin &gt; Paiements). Le virement reste disponible.</p><?php endif; ?>
</form>
<?php echo geah_footer(); ?>
</div></section></body></html>
