<?php
require_once __DIR__.'/core.php';
require_user_login($_SERVER['REQUEST_URI'] ?? '/plan-download.php');
$txid=$_GET['txid']??'';
$orders=read_json('plan_orders.json',[]); $order=null;
foreach($orders as $k=>$o){ if($o['txid']===$txid){ $order=$o; $oidx=$k; break; } }
if($order && $order['status']!=='paid'){
    $chk=cinetpay_check($txid);
    if($chk['accepted']){ $orders[$oidx]['status']='paid'; write_json('plan_orders.json',$orders); $order['status']='paid'; }
}
$paid = $order && $order['status']==='paid';
$p = $order['params'] ?? null;
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Mon plan — GEA-H</title><link rel="stylesheet" href="/assets/css/style.css?v=110">
<style>@media print{ .noprint{display:none!important} header,footer{display:none!important} body{background:#fff} .plan-sheet{box-shadow:none;border:0} }
.plan-sheet{background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:18px;max-width:900px;margin:0 auto}
.title-block{display:flex;justify-content:space-between;border-bottom:2px solid #013328;padding-bottom:8px;margin-bottom:12px}</style></head>
<body><header class="header noprint"><div class="wrap nav"><a class="brand" href="/">GEA-HOLDING.SAU</a></div></header>
<section class="section"><div class="wrap" style="max-width:920px">
<?php if(!$order): ?>
  <div class="card"><h1>Commande introuvable</h1><p>Aucun plan ne correspond à cette référence.</p></div>
<?php elseif(!$paid): ?>
  <div class="card"><h1>Paiement requis</h1><p>Votre paiement n'est pas encore confirmé. Si vous venez de payer, patientez quelques secondes et rechargez la page.</p>
  <p><a class="btn btn-primary" href="/plan-download.php?txid=<?=urlencode($txid)?>">Vérifier à nouveau</a></p></div>
<?php else: ?>
  <p class="noprint" style="text-align:right"><button class="btn btn-gold" onclick="window.print()">🖨️ Imprimer / Enregistrer en PDF</button></p>
  <div class="plan-sheet">
    <div class="title-block"><div><b style="font-size:18px;color:#013328">GEA-HOLDING.SAU</b><br><span style="color:#6b7280">Plan schématique — Groupe Amonfon</span></div>
    <div style="text-align:right;font-size:13px;color:#4b5563">Réf : <?=e($txid)?><br><?=e($order['date']??'')?></div></div>
    <?=geah_plan_svg($p['len'],$p['wid'],$p['floors'],$p['chambres'], false)?>
    <p style="color:#6b7280;font-size:12px;margin-top:10px">Plan schématique indicatif (dimensions approximatives). Pour le permis de construire et l'exécution, faites valider/établir les plans par un architecte ou un bureau d'études agréé.</p>
  </div>
<?php endif; ?>
</div></section><?php echo geah_footer(); ?></body></html>
