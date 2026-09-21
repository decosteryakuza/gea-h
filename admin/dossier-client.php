<?php require_once __DIR__.'/../core.php'; require_once __DIR__.'/auth.php';
$allowedRoles=['super','pdg','dg','rh','commercial','dir_commercial','comptable'];
if(!in_array(auth_role(),$allowedRoles,true)){ http_response_code(403); die('Accès réservé à la direction, aux RH, au commercial et à la comptabilité.'); }

$msg='';
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='create_client'){
    $name=trim($_POST['name']??''); $phone=trim($_POST['phone']??''); $email=trim($_POST['email']??''); $city=trim($_POST['city']??'');
    if($name==='' || ($phone==='' && $email==='')){
        $msg='error:Indiquez au minimum le nom et un téléphone ou un email.';
    } else {
        $acc=geah_ensure_client_account($name,$email,$phone);
        if($city!==''){ $users0=read_json('users.json',[]); foreach($users0 as &$uu){ if(($uu['client_ref']??'')===$acc['ref']){ $uu['city']=$city; } } unset($uu); write_json('users.json',$users0); }
        log_action('Nouveau dossier client créé : '.$acc['ref']);
        if($acc['created']){ $msg='ok:Dossier client créé — Référence : '.$acc['ref'].' — Mot de passe temporaire à communiquer : '.$acc['temp_password']; }
        else { $msg='ok:Un compte existait déjà pour ces coordonnées — Référence : '.$acc['ref']; }
        header('Location:/admin/dossier-client.php?ref='.urlencode($acc['ref']).'&created=1'); exit;
    }
}

$ref=trim($_GET['ref']??'');
$client=null;
if($ref!==''){
    foreach(read_json('users.json',[]) as $u0){ if(strcasecmp($u0['client_ref']??'',$ref)===0){ $client=$u0; break; } }
}
$reservations=[]; $orders=[]; $receipts=[]; $leases=[]; $submissions=[]; $properties=[];
if($client){
    $em=strtolower(trim($client['email']??'')); $ph=trim($client['phone']??'');
    $sameContact=function($e,$p) use($em,$ph){ return ($em!=='' && strtolower(trim($e))===$em) || ($ph!=='' && $p!=='' && geah_same_phone($p,$ph)); };
    $reservations=array_values(array_filter(read_json('reservations.json',[]),fn($r)=>$sameContact($r['email']??'',$r['phone']??'')));
    $orders=array_values(array_filter(read_json('orders.json',[]),fn($o)=>$sameContact($o['buyer_email']??'',$o['buyer_phone']??'')));
    $receipts=array_values(array_filter(data_list('receipts'),fn($r)=>$sameContact($r['client_email']??'',$r['client_phone']??'')));
    $leases=array_values(array_filter(data_list('leases'),fn($l)=>($l['client_ref']??'')===$ref || $sameContact($l['tenant_email']??'',$l['tenant_phone']??'')));
    $submissions=array_values(array_filter(read_json('submissions.json',[]),fn($s)=>$sameContact($s['owner_email']??($s['user_email']??''),$s['contact_phone']??'')));
    $properties=array_values(array_filter(data_list('properties'),fn($p)=>$sameContact($p['owner_email']??'','')));
}
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Dossier client</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main">
<h1>📁 Dossier client</h1>
<p style="color:var(--muted);margin-top:-6px" class="no-print">Tapez la référence client (GEAH-CLI-...) pour retrouver tout son dossier : réservations, achats, locations, reçus.</p>

<?php if($msg): [$mtype,$mtext]=explode(':',$msg,2); ?><p class="status <?=$mtype==='ok'?'ok':'warn'?>"><?=e($mtext)?></p><?php endif; ?>
<?php if(isset($_GET['created'])): ?><p class="status ok">✅ Dossier client créé avec succès.</p><?php endif; ?>

<details class="card no-print" style="margin-bottom:14px">
  <summary style="cursor:pointer;font-weight:700;color:var(--gold)">➕ Créer un nouveau dossier client</summary>
  <form method="post" style="margin-top:12px">
    <input type="hidden" name="action" value="create_client">
    <div class="form-grid">
      <label>Nom complet<input name="name" required></label>
      <label>Téléphone<input name="phone" placeholder="+225..."></label>
      <label>Email<input type="email" name="email"></label>
      <label>Ville<input name="city"></label>
    </div>
    <button class="btn btn-primary" style="margin-top:8px">Créer le dossier et la référence</button>
    <p style="font-size:13px;color:var(--muted);margin-top:6px">Indique au moins le téléphone ou l'email. Si un compte existe déjà avec ces coordonnées, sa référence sera simplement récupérée.</p>
  </form>
</details>

<form class="card no-print" method="get">
  <label>Référence client<input name="ref" value="<?=e($ref)?>" placeholder="Ex : GEAH-CLI-20260702-ABC123" required></label>
  <button class="btn btn-primary">Rechercher</button>
</form>

<?php if($ref!=='' && !$client): ?>
  <div class="card no-print" style="border-left:4px solid #dc2626"><p>Aucun client ne correspond à cette référence.</p></div>
<?php endif; ?>

<?php if($client): ?>
<div style="display:flex;justify-content:space-between;align-items:center;gap:10px" class="no-print">
  <div></div>
  <button class="btn btn-light" onclick="window.print()">🖨️ Imprimer ce dossier</button>
</div>

<div class="card" style="border-left:4px solid var(--gold)">
  <h2 style="margin:0"><?=e($client['name']??'')?></h2>
  <p style="color:var(--muted);margin:6px 0 0">Référence client : <b style="color:var(--gold)"><?=e($client['client_ref']??'')?></b></p>
  <p style="margin:4px 0 0"><?=e($client['email']??'')?> · <?=e($client['phone']??'')?> · <?=e($client['city']??'')?></p>
  <p style="margin:4px 0 0;color:var(--muted);font-size:13px">Type de compte : <?=e($client['account_type']??'')?> · Inscrit le <?=e(substr($client['created']??'',0,10))?></p>
</div>

<h3 style="margin-top:22px">🔑 Baux / locations (<?=count($leases)?>)</h3>
<?php if(!$leases): ?><div class="card"><p>Aucun bail.</p></div><?php endif; ?>
<?php foreach($leases as $l): ?>
  <div class="card"><b><?=e($l['item_title']??'')?></b> — <?=money((float)($l['monthly_rent']??0))?>/mois<br><span style="color:var(--muted);font-size:13px">Réf bail : <?=e($l['reference']??'')?> · Statut : <?=e($l['status']??'')?> · Depuis le <?=e(substr($l['date']??'',0,10))?></span></div>
<?php endforeach; ?>

<h3 style="margin-top:22px">📅 Réservations / demandes de visite (<?=count($reservations)?>)</h3>
<?php if(!$reservations): ?><div class="card"><p>Aucune réservation.</p></div><?php endif; ?>
<?php foreach($reservations as $r): ?>
  <div class="card"><b><?=e($r['item_title']??'')?></b> — <?=e($r['type']??'')?><br><span style="color:var(--muted);font-size:13px">Réf : <?=e($r['txid']??'')?> · Montant : <?=money((float)($r['amount']??0))?> · Statut : <?=e($r['status']??'')?> · <?=e(substr($r['date']??'',0,10))?></span></div>
<?php endforeach; ?>

<h3 style="margin-top:22px">🛒 Commandes / achats marketplace (<?=count($orders)?>)</h3>
<?php if(!$orders): ?><div class="card"><p>Aucune commande.</p></div><?php endif; ?>
<?php foreach($orders as $o): ?>
  <div class="card"><b><?=e($o['item_title']??($o['product_title']??''))?></b><br><span style="color:var(--muted);font-size:13px">Réf : <?=e($o['txid']??'')?> · Montant : <?=money((float)($o['amount']??0))?> · Statut : <?=e($o['status']??'')?> · <?=e(substr($o['date']??'',0,10))?></span></div>
<?php endforeach; ?>

<h3 style="margin-top:22px">🏠 Biens déposés par ce client (<?=count($properties)?>)</h3>
<?php if(!$properties): ?><div class="card"><p>Aucun bien déposé directement.</p></div><?php endif; ?>
<?php foreach($properties as $p): ?>
  <div class="card"><b><?=e($p['title']??'')?></b> — <?=e($p['city']??'')?><br><span style="color:var(--muted);font-size:13px">Statut : <?=e($p['status']??'')?> · <?=e($p['transaction']??'')?></span></div>
<?php endforeach; ?>

<h3 style="margin-top:22px">📋 Annonces en attente de validation (<?=count($submissions)?>)</h3>
<?php if(!$submissions): ?><div class="card"><p>Aucune annonce en attente.</p></div><?php endif; ?>
<?php foreach($submissions as $s): ?>
  <div class="card"><b><?=e($s['title']??'')?></b> — <?=e($s['status']??'')?><br><span style="color:var(--muted);font-size:13px"><?=e(substr($s['date']??'',0,10))?></span></div>
<?php endforeach; ?>

<h3 style="margin-top:22px">🧾 Tous les reçus (<?=count($receipts)?>)</h3>
<?php if(!$receipts): ?><div class="card"><p>Aucun reçu.</p></div><?php endif; ?>
<?php foreach($receipts as $r): ?>
  <div class="card">
    <b><?=money((float)($r['amount']??0))?></b> — <?=e($r['type']??'')?> — <?=e($r['item_title']??'')?><br>
    <span style="color:var(--muted);font-size:13px">Réf : <?=e($r['reference']??'')?> · <?=e($r['payment_method']??'')?> · Statut : <?=e($r['status']??'')?> · <?=e(substr($r['date']??'',0,10))?></span>
    <span class="no-print"> · <a href="/modules/verify-receipt.php?ref=<?=urlencode($r['reference'])?>" style="color:var(--gold)">Voir le reçu</a></span>
  </div>
<?php endforeach; ?>

<?php endif; ?>
<style>@media print{.sidebar,.no-print,#geah-chat-btn,#geahHomeBtn,#geahNotif{display:none!important}.admin-main{margin:0!important;padding:0!important}body{background:#fff!important;color:#000!important}.card{border:1px solid #ccc!important;background:#fff!important;color:#000!important}}</style>
</main></div></body></html>
