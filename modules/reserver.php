<?php
require_once __DIR__.'/../core.php';
require_user_login($_SERVER['REQUEST_URI'] ?? '/modules/reserver.php');
$t=$_GET['type']??''; $type=in_array($t,['hotel','property'],true)?$t:'residence';
$listName=$type==='hotel'?'hotels':($type==='property'?'properties':'residences');
$id=(int)($_GET['id']??0);
$items=data_list($listName); $item=null; foreach($items as $it){ if(($it['id']??0)==$id){ $item=$it; break; } }
if(!$item){ http_response_code(404); echo 'Élément introuvable.'; exit; }
function geah_amount_from_item($item){
  $n=(int)($item['price_fcfa']??0);
  if($n>0) return $n;
  $raw=(string)($item['price']??($item['price_night']??0));
  $digits=preg_replace('/[^0-9]/','',$raw);
  return $digits!=='' ? (int)$digits : 0;
}
$isStay=in_array($type,['hotel','residence'],true);
$daily=geah_amount_from_item($item);
$nights=$isStay?max(1,min(365,(int)($_POST['nights']??$_GET['nights']??1))):1;
$offer=strtolower((string)($item['offer']??$item['transaction']??($item['type_offre']??'')));
$isRental=($type==='property')&&(strpos($offer,'loc')!==false||!empty($item['caution'])||!empty($item['loyer'])||!empty($item['rent']));
$monthlyRent=$isRental?geah_amount_from_item($item):0;
$caution=0; if($isRental){ $caution=(int)preg_replace('/[^0-9]/','',(string)($item['caution']??($item['deposit']??''))); if($caution<=0) $caution=$monthlyRent; }
if($isStay){ $base=$daily*$nights; } elseif($isRental){ $base=$caution; } else { $base=geah_amount_from_item($item); }
$sfp=market_service_fee_pct(); $sf=(int)round($base*$sfp/100); $amount=$base+$sf;
$cfg=payments_config();
$momoNums=array_filter(['Orange Money'=>$cfg['momo_orange']??'','MTN MoMo'=>$cfg['momo_mtn']??'','Moov Money'=>$cfg['momo_moov']??'','Wave'=>$cfg['momo_wave']??'']);
$err=''; $done=''; 
if($_SERVER['REQUEST_METHOD']==='POST'){
  $name=trim($_POST['name']??''); $phone=trim($_POST['phone']??''); $arrival=trim($_POST['arrival']??''); $msg=trim($_POST['message']??''); $action=$_POST['action']??'cinetpay';
  if($name===''||$phone===''){ $err='Veuillez indiquer votre nom et votre téléphone.'; }
  elseif($action==='momo' && trim($_POST['ref']??'')===''){ $err='Indiquez la référence (ID) de votre paiement Mobile Money.'; }
  else {
    $txid='GEAH-RES-'.time().'-'.rand(100,999);
    $leaseRef='';
    if($isRental){ $u=current_user()?:[]; $lease=geah_ensure_lease($type,$id,$item,$name,$phone,($u['email']??''),$monthlyRent,($u['client_ref']??'')); $leaseRef=$lease['reference']; }
    $rec=['txid'=>$txid,'type'=>$type,'item_id'=>$id,'item_title'=>$item['title']??($item['name']??''),'name'=>$name,'phone'=>$phone,'arrival'=>$arrival,'message'=>$msg,'nights'=>$nights,'rental'=>$isRental?1:0,'lease_ref'=>$leaseRef,'base'=>$base,'service_fee'=>$sf,'amount'=>$amount,'status'=>'pending','date'=>now()];
    if($action==='reserve_only' || $amount<=0){
      $rec['method']='sur_place'; $rs=read_json('reservations.json',[]); $rs[]=$rec; write_json('reservations.json',$rs);
      if(function_exists('notify_super')) notify_super('Nouvelle réservation', ($type==='hotel'?'Hôtel':'Résidence').' : '.($rec['item_title']).' — '.$name.' ('.$phone.')'.($arrival?' · '.$arrival:''));
      $done='reserved';
    } elseif($action==='momo'){
      $rec['method']='momo_direct'; $rec['momo_ref']=trim($_POST['ref']??''); $rs=read_json('reservations.json',[]); $rs[]=$rec; write_json('reservations.json',$rs);
      if(function_exists('notify_super')) notify_super('Réservation Mobile Money à vérifier', ($rec['item_title']).' — '.$name.' ('.$phone.') · Réf: '.$rec['momo_ref'].' · '.number_format($amount,0,',',' ').' FCFA');
      $done='momo';
    } else {
      $rec['method']='cinetpay'; $rs=read_json('reservations.json',[]); $rs[]=$rec; write_json('reservations.json',$rs);
      $_SESSION['last_txid']=$txid;
      $res=cinetpay_init($txid,$amount,'Réservation GEA-H : '.$rec['item_title'],'ALL',$name);
      if(!empty($res['ok'])&&!empty($res['url'])){ header('Location:'.$res['url']); exit; }
      $err=$res['error']??'Paiement carte indisponible. Utilisez le Mobile Money direct ci-dessous.';
    }
  }
}
$imgs=geah_property_images($item);
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Réserver — <?=e($item['title']??'')?></title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><header class="header"><div class="wrap nav"><a class="brand" href="/">GEA-HOLDING.SAU</a><nav class="menu"><a href="/modules/residences.php">Résidences</a><a href="/modules/hotels.php">Hôtels</a><?php echo account_link(); ?></nav></div></header>
<section class="section"><div class="wrap" style="max-width:640px">
  <a href="/modules/<?=$type==='hotel'?'hotels':($type==='property'?'properties':'residences')?>.php" style="color:var(--gold);text-decoration:none">← Retour</a>
  <h1 style="margin-top:8px">🔑 Réserver</h1>
  <div class="card">
    <?php if($imgs): ?><img src="<?=e(media_src($imgs[0]))?>" style="width:100%;max-height:200px;object-fit:cover;border-radius:10px" alt=""><?php endif; ?>
    <h2 style="margin:8px 0 2px"><?=e($item['title']??($item['name']??''))?></h2>
    <?php if($isStay): ?><p style="margin:2px 0;color:var(--muted);font-size:14px"><b style="color:var(--gold)"><?=number_format($daily,0,',',' ')?> FCFA</b> / nuit &times; <b id="nnights"><?=$nights?></b> nuit(s)</p><?php endif; ?>
    <?php if($isRental): ?><p style="margin:2px 0;color:var(--muted);font-size:15px">🏠 Loyer mensuel : <b style="color:var(--gold)"><?=number_format($monthlyRent,0,',',' ')?> FCFA</b></p><p style="margin:6px 0 2px;color:var(--muted);font-size:13px">🔑 Caution obligatoire à régler en ligne <b>avant la remise des clés</b> (le loyer se paie ensuite au bailleur) :</p><?php endif; ?>
    <?php if($amount>0): ?><?php if($sf>0): ?><p style="margin:2px 0;color:var(--muted);font-size:14px" id="breakdown"><?=number_format($base,0,',',' ')?> FCFA + frais de service <?=number_format($sf,0,',',' ')?> FCFA</p><?php endif; ?>
    <p style="margin:2px 0"><?=$isRental?'Caution à payer':'Montant à payer'?> : <b style="color:var(--gold);font-size:18px" id="totalpay"><?=number_format($amount,0,',',' ')?> FCFA</b></p>
    <?php else: ?><p style="color:var(--muted)">Réservation sans paiement en ligne (un conseiller vous contactera).</p><?php endif; ?>
  </div>
  <?php if($done==='momo'): ?>
    <div class="card" style="border-left:4px solid #16a34a"><h2>✅ Merci !</h2><p>Votre paiement Mobile Money a été déclaré. GEA-H vérifie la réception et confirme votre réservation.</p><?php if($leaseRef): ?><p style="margin-top:10px;padding:10px;background:#0a1620;border-radius:10px">🔑 Votre référence de bail : <b style="color:var(--gold)"><?=e($leaseRef)?></b><br><span style="font-size:13px;color:var(--muted)">Gardez-la précieusement : elle vous sert à payer vos loyers en ligne chaque mois sur <a href="/modules/payer-loyer.php" style="color:var(--gold)">/modules/payer-loyer.php</a> et à obtenir vos reçus.</span></p><?php endif; ?><a class="btn btn-gold" href="/modules/<?=$type==='hotel'?'hotels':($type==='property'?'properties':'residences')?>.php">Retour</a></div>
  <?php elseif($done==='reserved'): ?>
    <div class="card" style="border-left:4px solid #16a34a"><h2>✅ Réservation envoyée !</h2><p>Un conseiller GEA-H va vous contacter rapidement pour finaliser.</p><?php if($leaseRef): ?><p style="margin-top:10px;padding:10px;background:#0a1620;border-radius:10px">🔑 Votre référence de bail : <b style="color:var(--gold)"><?=e($leaseRef)?></b><br><span style="font-size:13px;color:var(--muted)">Gardez-la précieusement : elle vous sert à payer vos loyers en ligne chaque mois sur <a href="/modules/payer-loyer.php" style="color:var(--gold)">/modules/payer-loyer.php</a> et à obtenir vos reçus.</span></p><?php endif; ?><a class="btn btn-gold" href="/modules/<?=$type==='hotel'?'hotels':($type==='property'?'properties':'residences')?>.php">Retour</a></div>
  <?php else: ?>
  <form class="card" method="post">
    <h2>Vos informations</h2>
    <div class="form-grid">
      <label class="full">Nom complet<input name="name" required value="<?=e($_POST['name']??'')?>"></label>
      <label>Téléphone<input name="phone" required placeholder="+225..." value="<?=e($_POST['phone']??'')?>"></label>
      <label>Date d'arrivée<input name="arrival" placeholder="Ex: 30/06" value="<?=e($_POST['arrival']??'')?>"></label>
      <?php if($isStay): ?><label>Nombre de nuits / jours<input type="number" name="nights" id="nightsInput" min="1" max="365" value="<?=$nights?>"></label><?php endif; ?>
      <label class="full">Message (optionnel)<textarea name="message" rows="2"></textarea></label>
    </div>
    <?php if($err): ?><p class="status warn" style="margin-top:8px"><?=e($err)?></p><?php endif; ?>
    <?php if($amount>0): ?>
      <h3 style="margin:16px 0 6px">💳 Carte ou Mobile Money (automatique)</h3>
      <button class="btn btn-violet" name="action" value="cinetpay" style="width:100%">Payer <?=number_format($amount,0,',',' ')?> FCFA</button>
      <?php if($momoNums): ?>
      <h3 style="margin:18px 0 6px">📱 Mobile Money direct</h3>
      <p style="color:var(--muted);font-size:13px;margin:0 0 6px">Envoyez <b><?=number_format($amount,0,',',' ')?> FCFA</b> à un compte GEA-H, puis indiquez la référence :</p>
      <div class="card" style="background:#0a1620;margin:0 0 8px"><?php foreach($momoNums as $lab=>$num): ?><div style="display:flex;justify-content:space-between;padding:4px 0"><span><?=e($lab)?></span><b style="color:var(--gold)"><?=e($num)?></b></div><?php endforeach; ?></div>
      <label>Référence / ID de la transaction<input name="ref" value="<?=e($_POST['ref']??'')?>"></label>
      <button class="btn btn-gold" name="action" value="momo" style="width:100%;margin-top:8px">J'ai payé — confirmer</button>
      <?php endif; ?>
      <h3 style="margin:18px 0 6px">🕒 Ou réserver sans payer maintenant</h3>
      <button class="btn btn-light" name="action" value="reserve_only" style="width:100%">Réserver, payer plus tard</button>
    <?php else: ?>
      <button class="btn btn-gold" name="action" value="reserve_only" style="width:100%;margin-top:8px">Envoyer ma réservation</button>
    <?php endif; ?>
  </form>
  <?php endif; ?>
<?php if($isStay): ?><script>
(function(){ var daily=<?=$daily?>, fee=<?=(float)$sfp?>, inp=document.getElementById('nightsInput'); if(!inp) return;
  function fmt(n){ return Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g,' '); }
  function upd(){ var n=Math.max(1,Math.min(365,parseInt(inp.value)||1)); var base=daily*n, sf=Math.round(base*fee/100), tot=base+sf; var e;
    if(e=document.getElementById('nnights')) e.textContent=n;
    if(e=document.getElementById('breakdown')) e.textContent=fmt(base)+' FCFA + frais de service '+fmt(sf)+' FCFA';
    if(e=document.getElementById('totalpay')) e.textContent=fmt(tot)+' FCFA';
    document.querySelectorAll('button[name=action]').forEach(function(b){ if(/Payer/.test(b.textContent)) b.textContent='Payer '+fmt(tot)+' FCFA'; });
  }
  inp.addEventListener('input',upd); inp.addEventListener('change',upd);
})();
</script><?php endif; ?>
</div></section><?php echo geah_footer(); ?></body></html>
