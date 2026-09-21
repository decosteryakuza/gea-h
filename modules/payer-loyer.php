<?php require_once __DIR__.'/../core.php';
require_user_login('/modules/payer-loyer.php');
$u=current_user() ?: [];
$ref=trim($_GET['ref']??$_POST['ref']??'');
$lease=$ref?geah_find_lease($ref):null;
$err=''; $done=false; $receiptRef='';

if($_SERVER['REQUEST_METHOD']==='POST' && $lease){
    $amount=(int)($lease['monthly_rent']??0);
    $action=$_POST['action']??'';
    if($amount<=0){ $err="Le montant du loyer n'est pas configuré pour ce bail. Contactez l'administration."; }
    elseif($action==='momo' && trim($_POST['momo_ref']??'')===''){ $err='Indiquez la référence de votre paiement Mobile Money.'; }
    else {
        $receiptRef=geah_receipt_reference('LOYER');
        $receipts=data_list('receipts');
        $receipts[]=[
            'id'=>time(),'reference'=>$receiptRef,'type'=>'Loyer',
            'client_name'=>$lease['tenant_name']??'','client_phone'=>$lease['tenant_phone']??'','client_email'=>$lease['tenant_email']??'',
            'item_type'=>$lease['item_type']??'','item_title'=>$lease['item_title']??'',
            'amount'=>$amount,'currency'=>'FCFA',
            'payment_method'=>$action==='momo'?('Mobile Money — Réf: '.trim($_POST['momo_ref']??'')):'Sur place / à confirmer',
            'status'=>$action==='momo'?'À vérifier':'En attente',
            'note'=>'Bail '.$ref,'created_by'=>'client','date'=>now()
        ];
        data_save('receipts',$receipts);
        if(function_exists('notify_super')) notify_super('Paiement de loyer', ($lease['tenant_name']??'').' — '.($lease['item_title']??'').' — '.number_format($amount,0,',',' ').' FCFA — réf reçu '.$receiptRef);
        $done=true;
    }
}
$cfg=payments_config();
$momoNums=array_filter(['Orange Money'=>$cfg['momo_orange']??'','MTN MoMo'=>$cfg['momo_mtn']??'','Moov Money'=>$cfg['momo_moov']??'','Wave'=>$cfg['momo_wave']??'']);
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Payer mon loyer — GEA-H</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><header class="header"><div class="wrap nav"><a class="brand" href="/">GEA-HOLDING.SAU</a><nav class="menu"><a href="/">Accueil</a><?php echo account_link(); ?></nav></div></header>
<section class="hero"><div class="wrap"><h1>🔑 Payer mon loyer</h1><p>Entrez votre référence de bail (remise lors du paiement de la caution) pour régler votre loyer et obtenir votre reçu immédiatement.</p></div></section>
<section class="section"><div class="wrap" style="max-width:640px">

<form class="card" method="get">
  <label>Référence de bail<input name="ref" value="<?=e($ref)?>" placeholder="Ex : GEAH-BAIL-20260702-ABC123" required></label>
  <button class="btn btn-primary">Vérifier</button>
</form>

<?php if($ref && !$lease): ?>
  <div class="card" style="border-left:4px solid #dc2626"><p>⚠️ Référence de bail introuvable. Vérifiez la référence reçue lors de votre réservation, ou contactez l'administration.</p></div>
<?php endif; ?>

<?php if($lease && $done): ?>
  <div class="card" style="border-left:4px solid #16a34a">
    <h2>✅ Paiement enregistré !</h2>
    <p>Votre reçu a été généré : <b style="color:var(--gold)"><?=e($receiptRef)?></b></p>
    <p><a class="btn btn-gold" href="/modules/verify-receipt.php?ref=<?=urlencode($receiptRef)?>">📄 Voir / vérifier mon reçu</a></p>
    <p style="margin-top:10px"><a href="/modules/payer-loyer.php?ref=<?=urlencode($ref)?>" style="color:var(--gold)">← Retour</a></p>
  </div>
<?php elseif($lease): ?>
  <div class="card">
    <h2><?=e($lease['item_title']??'')?></h2>
    <p style="color:var(--muted)">Locataire : <b><?=e($lease['tenant_name']??'')?></b></p>
    <p>Loyer mensuel : <b style="color:var(--gold);font-size:20px"><?=number_format((int)($lease['monthly_rent']??0),0,',',' ')?> FCFA</b></p>
  </div>
  <?php if($err): ?><p class="status warn"><?=e($err)?></p><?php endif; ?>
  <form class="card" method="post">
    <input type="hidden" name="ref" value="<?=e($ref)?>">
    <?php if($momoNums): ?>
    <h3 style="margin:0 0 6px">📱 Mobile Money direct</h3>
    <p style="color:var(--muted);font-size:13px;margin:0 0 6px">Envoyez le montant du loyer à l'un de ces comptes, puis indiquez la référence :</p>
    <div class="card" style="background:#0a1620;margin:0 0 8px"><?php foreach($momoNums as $lab=>$num): ?><div style="display:flex;justify-content:space-between;padding:4px 0"><span><?=e($lab)?></span><b style="color:var(--gold)"><?=e($num)?></b></div><?php endforeach; ?></div>
    <label>Référence / ID de la transaction<input name="momo_ref" value="<?=e($_POST['momo_ref']??'')?>"></label>
    <button class="btn btn-gold" name="action" value="momo" style="width:100%;margin-top:8px">J'ai payé — confirmer et obtenir mon reçu</button>
    <?php endif; ?>
    <h3 style="margin:18px 0 6px">🕒 Ou déclarer un paiement effectué sur place</h3>
    <button class="btn btn-light" name="action" value="declare" style="width:100%">Déclarer mon paiement</button>
  </form>
<?php endif; ?>

</div></section><?php echo geah_footer(); ?></body></html>
