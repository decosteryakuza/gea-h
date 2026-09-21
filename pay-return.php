<?php
require_once __DIR__.'/core.php';
$txid=$_SESSION['last_txid'] ?? ($_POST['transaction_id'] ?? ($_GET['transaction_id'] ?? ''));
$ok=false; $statusTxt='Paiement non confirmé.'; $planTxid=null;
if($txid){
    $chk=cinetpay_check($txid);
    $isRech=false;
    $rech=read_json('bank_recharges.json',[]);
    foreach($rech as &$r){ if($r['txid']===$txid){ $isRech=true;
        if($chk['accepted'] && $r['status']!=='credited'){ bank_credit($r['account_id'],$r['amount'],'Recharge '.$r['method']); $r['status']='credited'; $ok=true; $statusTxt='Paiement réussi, solde crédité !'; }
        elseif($r['status']==='credited'){ $ok=true; $statusTxt='Paiement déjà crédité.'; }
        else { $r['status']=strtolower($chk['status']?:'echec'); $statusTxt='Paiement '.($chk['status']?:'échoué').'.'; }
    } } unset($r);
    if($isRech) write_json('bank_recharges.json',$rech);
    if(!$isRech){
        $orders=read_json('plan_orders.json',[]);
        foreach($orders as &$o){ if($o['txid']===$txid){
            if($chk['accepted'] && $o['status']!=='paid'){ $o['status']='paid'; }
            if($o['status']==='paid'){ $ok=true; $planTxid=$txid; $statusTxt='Paiement réussi ! Votre plan est prêt.'; }
            else { $statusTxt='Paiement '.($chk['status']?:'non confirmé').'.'; }
        } } unset($o);
        write_json('plan_orders.json',$orders);
        // Commandes produits (marketplace)
        $ords=read_json('orders.json',[]); $isOrder=false;
        foreach($ords as &$o){ if(($o['txid']??'')===$txid){ $isOrder=true;
            if($chk['accepted'] && ($o['status']??'')!=='paid'){ $o['status']='paid'; notify_super('Nouvelle commande payee', ($o['item_title']??'').' - '.number_format(($o['total']??0),0,',',' ').' FCFA - Client: '.($o['buyer_name']??'').' ('.($o['buyer_phone']??'').')'); }
            if(($o['status']??'')==='paid'){ $ok=true; $statusTxt='Commande payee ! Vous serez contacte pour la preparation et la livraison.'; }
            else { $statusTxt='Paiement '.($chk['status']?:'non confirme').'.'; }
        } } unset($o);
        if($isOrder) write_json('orders.json',$ords);
        // Reservations (residences / hotels)
        $rss=read_json('reservations.json',[]); $isRes=false;
        foreach($rss as &$rr){ if(($rr['txid']??'')===$txid){ $isRes=true;
            if($chk['accepted'] && ($rr['status']??'')!=='paid'){ $rr['status']='paid'; notify_super('Reservation payee', ($rr['item_title']??'').' - '.($rr['name']??'').' ('.($rr['phone']??'').')'); }
            if(($rr['status']??'')==='paid'){ $ok=true; $statusTxt='Reservation payee ! Vous serez contacte pour finaliser.'; }
            else { $statusTxt='Paiement '.($chk['status']?:'non confirme').'.'; }
        } } unset($rr);
        if($isRes) write_json('reservations.json',$rss);
    }
}
unset($_SESSION['last_txid']);
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Résultat du paiement</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><header class="header"><div class="wrap nav"><a class="brand" href="/">GEA-H.SAU</a></div></header>
<section class="section"><div class="wrap" style="max-width:560px;text-align:center">
<div class="card" style="font-size:48px"><?=$ok?'✅':'❌'?></div>
<h1><?=$ok?'Merci !':'Paiement non abouti'?></h1>
<p><?=e($statusTxt)?></p>
<?php if($planTxid): ?><p><a class="btn btn-primary" href="/plan-download.php?txid=<?=urlencode($planTxid)?>">📄 Télécharger / imprimer mon plan</a></p><?php else: ?><p><a class="btn btn-primary" href="/recharge.php">Retour</a></p><?php endif; ?>
</div></section><?php echo geah_footer(); ?></body></html>
