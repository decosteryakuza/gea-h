<?php
require_once __DIR__.'/../core.php';
require_user_login($_SERVER['REQUEST_URI'] ?? '/modules/visite.php');
require_user_login($_SERVER['REQUEST_URI'] ?? '/modules/visite.php');
if(function_exists('public_blocked') && public_blocked()){ header('Location:/'); exit; }
$id=(int)($_GET['id']??0); $mode=(($_GET['mode']??'visite')==='reservation')?'reservation':'visite';
$props=read_json('properties.json',[]); $item=null; foreach($props as $p){ if(($p['id']??0)==$id){ $item=$p; break; } }
$done=false; $err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $name=trim($_POST['name']??''); $phone=trim($_POST['phone']??''); $msg=trim($_POST['message']??''); $date=trim($_POST['date']??'');
  if($name===''||$phone===''){ $err='Veuillez indiquer votre nom et votre téléphone.'; }
  else {
    $v=read_json('visits.json',[]);
    $v[]=['id'=>$id,'property'=>$item['title']??'','mode'=>$mode,'name'=>$name,'phone'=>$phone,'message'=>$msg,'date_pref'=>$date,'status'=>'Nouveau','created'=>now()];
    write_json('visits.json',$v);
    if(function_exists('notify_super')) notify_super($mode==='reservation'?'Nouvelle réservation':'Nouvelle demande de visite', ($item['title']??'Bien').' — '.$name.' ('.$phone.')'.($date?' · '.$date:''));
    $done=true;
  }
}
$pageTitle = $mode==='reservation'?'Réserver ce bien':'Demander une visite';
$imgs = $item?geah_property_images($item):[];
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=e($pageTitle)?> — GEA-H</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><header class="header"><div class="wrap nav"><a class="brand" href="/">GEA-HOLDING.SAU</a><nav class="menu"><a href="/">Accueil</a><a href="/modules/properties.php">Biens</a><?php echo account_link(); ?></nav></div></header>
<section class="section"><div class="wrap" style="max-width:620px">
  <a href="/modules/properties.php" style="color:var(--gold);text-decoration:none">← Retour aux biens</a>
  <h1 style="margin-top:8px"><?=$mode==='reservation'?'🔑':'📅'?> <?=e($pageTitle)?></h1>
  <?php if($item): ?>
  <div class="card" style="display:flex;gap:12px;align-items:center">
    <?php if($imgs): ?><img src="<?=e(media_src($imgs[0]))?>" style="width:96px;height:72px;object-fit:cover;border-radius:8px" alt=""><?php endif; ?>
    <div><b><?=e($item['title']??'')?></b><br><span style="color:var(--muted);font-size:14px"><?=e($item['city']??'')?><?php if(!empty($item['price'])): ?> · <?=e($item['price'])?><?php endif; ?></span></div>
  </div>
  <?php endif; ?>
  <?php if($item): ?>
  <div class="card" style="border-left:4px solid var(--gold);margin-top:12px">
    <h2 style="margin-top:0">Réservation & paiement en ligne</h2>
    <p style="color:var(--muted);margin:0 0 10px">Après la demande de visite, le client peut aussi réserver le bien et payer l'acompte en ligne par carte ou Mobile Money.</p>
    <a class="btn btn-violet" href="/modules/reserver.php?type=property&id=<?=e($id)?>">🔑 Réserver / Payer en ligne</a>
    <a class="btn btn-light" href="/modules/properties.php#bien-<?=e($id)?>">Voir le bien</a>
  </div>
  <?php endif; ?>
  <?php if($done): ?>
    <div class="card" style="border-left:4px solid #16a34a">
      <h2>✅ Demande envoyée !</h2>
      <p><?=$mode==='reservation'?'Votre demande de réservation a bien été reçue.':'Votre demande de visite a bien été reçue.'?> Un conseiller GEA-H va vous contacter rapidement<?=$mode==='reservation'?' pour finaliser la réservation et le paiement.':' pour convenir d\'un rendez-vous.'?></p>
      <a class="btn btn-gold" href="/modules/properties.php">Voir d'autres biens</a> <?php if($item): ?><a class="btn btn-violet" href="/modules/reserver.php?type=property&id=<?=e($id)?>">Réserver / Payer</a><?php endif; ?>
    </div>
  <?php else: ?>
    <?php if($err): ?><p class="status warn"><?=e($err)?></p><?php endif; ?>
    <form class="card" method="post">
      <h2><?=$mode==='reservation'?'Vos informations':'Planifier votre visite'?></h2>
      <div class="form-grid">
        <label class="full">Nom complet<input name="name" required></label>
        <label>Téléphone<input name="phone" required placeholder="+225..."></label>
        <?php if($mode==='visite'): ?><label>Date souhaitée<input name="date" type="text" placeholder="Ex: samedi matin"></label><?php endif; ?>
        <label class="full">Message (optionnel)<textarea name="message" rows="3" placeholder="<?=$mode==='reservation'?'Précisez vente, location, durée...':'Une question ?'?>"></textarea></label>
      </div>
      <button class="btn <?=$mode==='reservation'?'btn-violet':'btn-gold'?>" style="margin-top:10px"><?=$mode==='reservation'?'🔑 Envoyer ma réservation':'📅 Envoyer ma demande'?></button>
      <p style="color:var(--muted);font-size:13px;margin-top:8px">Gratuit et sans engagement. Vos coordonnées restent confidentielles.</p>
    </form>
  <?php endif; ?>
</div></section><?php echo geah_footer(); ?></body></html>
