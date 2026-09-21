<?php
require_once __DIR__.'/../core.php';
require_user_login($_SERVER['REQUEST_URI'] ?? '/modules/commander.php');
$id=(int)($_GET['id']??0);
$items=read_json('market_items.json',[]);
$item=null; foreach($items as $it){ if(($it['id']??0)==$id){ $item=$it; break; } }
if(!$item){ http_response_code(404); echo 'Article introuvable.'; exit; }
$pf=(int)($item['price_fcfa']??0); $df=(int)($item['delivery_fcfa']??0); $sfp=market_service_fee_pct(); $sf=(int)round($pf*$sfp/100); $total=$pf+$df+$sf;
$cfg=payments_config();
$momoNums=array_filter(['Orange Money'=>$cfg['momo_orange']??'','MTN MoMo'=>$cfg['momo_mtn']??'','Moov Money'=>$cfg['momo_moov']??'','Wave'=>$cfg['momo_wave']??'']);
$err=''; $momoDone=false;
if($_SERVER['REQUEST_METHOD']==='POST' && $pf>0){
  $name=trim($_POST['name']??''); $phone=trim($_POST['phone']??''); $addr=trim($_POST['address']??''); $action=$_POST['action']??'cinetpay';
  if($name===''||$phone===''||$addr===''){ $err='Veuillez remplir votre nom, votre téléphone et votre adresse.'; }
  elseif($action==='momo' && trim($_POST['ref']??'')===''){ $err='Indiquez la référence (ID) de votre paiement Mobile Money.'; }
  else {
    $pct=market_commission_pct(); $commission=(int)round($pf*$pct/100); $sellerShare=$pf-$commission;
    $txid='GEAH-CMD-'.time().'-'.rand(100,999);
    $order=['txid'=>$txid,'item_id'=>$id,'item_title'=>$item['title']??'','market'=>$item['market']??'',
      'seller'=>$item['seller']??'','seller_phone'=>$item['phone']??'','seller_momo'=>$item['seller_momo']??'',
      'buyer_name'=>$name,'buyer_phone'=>$phone,'buyer_address'=>$addr,
      'price'=>$pf,'delivery'=>$df,'service_fee'=>$sf,'total'=>$total,'commission'=>$commission,'seller_share'=>$sellerShare,
      'status'=>'pending','seller_paid'=>false,'delivered'=>false,'date'=>now()];
    if($action==='momo'){
      $order['method']='momo_direct'; $order['momo_ref']=trim($_POST['ref']??'');
      $orders=read_json('orders.json',[]); $orders[]=$order; write_json('orders.json',$orders);
      if(function_exists('notify_super')) notify_super('Commande Mobile Money à vérifier', ($item['title']??'').' — '.$name.' ('.$phone.') · Réf: '.$order['momo_ref'].' · '.number_format($total,0,',',' ').' FCFA');
      $momoDone=true;
    } else {
      $order['method']='cinetpay';
      $orders=read_json('orders.json',[]); $orders[]=$order; write_json('orders.json',$orders);
      $_SESSION['last_txid']=$txid;
      $res=cinetpay_init($txid,$total,'Commande GEA-H : '.($item['title']??''),'ALL',$name);
      if(!empty($res['ok']) && !empty($res['url'])){ header('Location:'.$res['url']); exit; }
      $err=$res['error']??'Paiement par carte indisponible pour le moment. Utilisez le Mobile Money direct ci-dessous.';
    }
  }
}
$imgs=geah_property_images($item);
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Commander — <?=e($item['title']??'')?></title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><header class="header"><div class="wrap nav"><a class="brand" href="/">GEA-HOLDING.SAU</a><nav class="menu"><a href="/modules/meubles.php">Meubles</a><a href="/modules/materiaux.php">Matériaux</a><?php echo account_link(); ?></nav></div></header>
<section class="section"><div class="wrap" style="max-width:640px">
  <h1>🛒 Commander</h1>
  <div class="card">
    <?php if($imgs): ?><div class="prop-gallery"><?php foreach(array_slice($imgs,0,3) as $u): ?><img src="<?=e(media_src($u))?>" alt="" style="width:90px;height:68px;object-fit:cover;border-radius:8px"><?php endforeach; ?></div><?php endif; ?>
    <h2 style="margin:8px 0 2px"><?=e($item['title']??'')?></h2>
    <table style="width:100%;margin-top:8px">
      <tr><td>Article</td><td style="text-align:right"><b><?=number_format($pf,0,',',' ')?> FCFA</b></td></tr>
      <tr><td>Livraison</td><td style="text-align:right"><?=number_format($df,0,',',' ')?> FCFA</td></tr>
      <?php if($sf>0): ?><tr><td>Frais de service</td><td style="text-align:right"><?=number_format($sf,0,',',' ')?> FCFA</td></tr><?php endif; ?>
      <tr style="border-top:1px solid var(--border)"><td><b>Total à payer</b></td><td style="text-align:right"><b style="color:var(--gold);font-size:18px"><?=number_format($total,0,',',' ')?> FCFA</b></td></tr>
    </table>
  </div>
  <?php if($momoDone): ?>
    <div class="card" style="border-left:4px solid #16a34a"><h2>✅ Merci !</h2><p>Votre paiement Mobile Money a été déclaré. GEA-H vérifie la réception et confirme votre commande (vous serez contacté pour la livraison).</p><a class="btn btn-gold" href="/modules/meubles.php">Continuer mes achats</a></div>
  <?php elseif($pf>0): ?>
  <form class="card" method="post">
    <h2>Vos informations de livraison</h2>
    <div class="form-grid">
      <label class="full">Nom complet<input name="name" required value="<?=e($_POST['name']??'')?>"></label>
      <label>Téléphone<input name="phone" required placeholder="+225..." value="<?=e($_POST['phone']??'')?>"></label>
      <label>Adresse de livraison<input name="address" required placeholder="Quartier, ville, repère" value="<?=e($_POST['address']??'')?>"></label>
    </div>
    <?php if($err): ?><p class="status warn" style="margin-top:8px"><?=e($err)?></p><?php endif; ?>

    <h3 style="margin:16px 0 6px">💳 Option 1 — Carte ou Mobile Money (automatique)</h3>
    <p style="color:var(--muted);font-size:13px;margin:0 0 8px">Paiement sécurisé par carte bancaire ou Mobile Money (Orange, MTN, Moov, Wave) via CinetPay.</p>
    <button class="btn btn-violet" name="action" value="cinetpay" style="width:100%">Payer <?=number_format($total,0,',',' ')?> FCFA maintenant</button>

    <h3 style="margin:18px 0 6px">📱 Option 2 — Mobile Money direct</h3>
    <?php if($momoNums): ?>
      <p style="color:var(--muted);font-size:13px;margin:0 0 6px">Envoyez <b><?=number_format($total,0,',',' ')?> FCFA</b> à l'un de ces comptes GEA-H, puis indiquez la référence du paiement :</p>
      <div class="card" style="background:#0a1620;margin:0 0 8px">
        <?php foreach($momoNums as $lab=>$num): ?><div style="display:flex;justify-content:space-between;padding:4px 0"><span><?=e($lab)?></span><b style="color:var(--gold)"><?=e($num)?></b></div><?php endforeach; ?>
      </div>
      <label>Référence / ID de la transaction Mobile Money<input name="ref" placeholder="Ex: PP250625.1234.A56789" value="<?=e($_POST['ref']??'')?>"></label>
      <button class="btn btn-gold" name="action" value="momo" style="width:100%;margin-top:8px">J'ai payé — confirmer ma commande</button>
    <?php else: ?>
      <p style="color:#fca5a5;font-size:13px">Aucun numéro Mobile Money configuré. (Admin → Paiements : Orange Money / MTN / Moov / Wave.)</p>
    <?php endif; ?>
  </form>
  <?php else: ?><div class="card"><p>Cet article n'a pas encore de prix en ligne.</p></div><?php endif; ?>
</div></section><?php echo geah_footer(); ?></body></html>
