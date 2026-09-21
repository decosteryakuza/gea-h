<?php require_once __DIR__.'/../core.php'; require_once __DIR__.'/auth.php';
$allowedRoles=['super','pdg','dg','rh','commercial','dir_commercial','comptable'];
if(!in_array(auth_role(),$allowedRoles,true)){ http_response_code(403); die('Accès réservé à la direction, aux RH, au commercial et à la comptabilité.'); }

$msg='';
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='create_lease'){
    $name=trim($_POST['tenant_name']??''); $phone=trim($_POST['tenant_phone']??''); $email=trim($_POST['tenant_email']??'');
    $title=trim($_POST['item_title']??''); $rent=(int)($_POST['monthly_rent']??0);
    if($name==='' || $phone==='' || $title==='' || $rent<=0){
        $msg='error:Merci de remplir au minimum le nom, le téléphone, le bien et le loyer mensuel.';
    } else {
        $acc=geah_ensure_client_account($name,$email,$phone);
        $lease=['id'=>time(),'reference'=>geah_lease_reference(),'item_type'=>'hors_catalogue','item_id'=>0,'item_title'=>$title,'tenant_name'=>$name,'tenant_phone'=>$phone,'tenant_email'=>$email,'client_ref'=>$acc['ref'],'monthly_rent'=>$rent,'status'=>'Actif','date'=>now(),'created_by'=>$_SESSION['admin']['email']??'admin'];
        $leases0=data_list('leases'); $leases0[]=$lease; data_save('leases',$leases0);
        log_action('Bail créé manuellement : '.$lease['reference']);
        $msg='ok:Bail créé — Référence bail : '.$lease['reference'].' — Référence client : '.$acc['ref'].($acc['created']?' (nouveau compte créé, mot de passe temporaire : '.$acc['temp_password'].')':' (compte existant relié)');
    }
}

$q=trim($_GET['q']??'');
if($q!=='' && stripos($q,'GEAH-CLI-')===0){ header('Location:/admin/dossier-client.php?ref='.urlencode($q)); exit; }
$leases=data_list('leases');
$receipts=data_list('receipts');
$found=null; $foundReceipts=[];
if($q!==''){
    foreach($leases as $l){ if(strcasecmp($l['reference']??'',$q)===0){ $found=$l; break; } }
    if(!$found){ foreach($receipts as $r){ if(strcasecmp($r['reference']??'',$q)===0){ $found=['is_receipt'=>true]+$r; break; } } }
    if($found && empty($found['is_receipt'])){
        foreach($receipts as $r){ if(strpos($r['note']??'',$found['reference'])!==false) $foundReceipts[]=$r; }
    }
}
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Vérification des références</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main">
<h1>🔎 Vérification des références</h1>
<p style="color:var(--muted);margin-top:-6px">Accès réservé : Super Admin, PDG, DG, RH, Commercial, Comptabilité. Recherchez une référence de bail (GEAH-BAIL-...), client (GEAH-CLI-...) ou de reçu (GEAH-...) donnée par un client.</p>

<?php if($msg): [$mtype,$mtext]=explode(':',$msg,2); ?><p class="status <?=$mtype==='ok'?'ok':'warn'?>"><?=e($mtext)?></p><?php endif; ?>

<details class="card" style="margin-bottom:14px">
  <summary style="cursor:pointer;font-weight:700;color:var(--gold)">➕ Créer un bail pour un locataire hors catalogue (bien non listé sur le site)</summary>
  <form method="post" style="margin-top:12px">
    <input type="hidden" name="action" value="create_lease">
    <div class="form-grid">
      <label>Nom du locataire<input name="tenant_name" required></label>
      <label>Téléphone<input name="tenant_phone" placeholder="+225..." required></label>
      <label>Email (optionnel)<input type="email" name="tenant_email"></label>
      <label>Désignation du bien<input name="item_title" placeholder="Ex : Villa 4 pièces, Bonoua" required></label>
      <label>Loyer mensuel (FCFA)<input type="number" name="monthly_rent" min="0" required></label>
    </div>
    <button class="btn btn-primary" style="margin-top:8px">Créer le bail et la référence</button>
    <p style="font-size:13px;color:var(--muted);margin-top:6px">Si le locataire n'a pas encore de compte GEA-H (email/téléphone inconnu), un compte est créé automatiquement avec une référence client à lui communiquer.</p>
  </form>
</details>

<form class="card" method="get">
  <label>Référence<input name="q" value="<?=e($q)?>" placeholder="Ex : GEAH-BAIL-20260702-ABC123"></label>
  <button class="btn btn-primary">Rechercher</button>
</form>


<?php if($q!=='' && !$found): ?>
  <div class="card" style="border-left:4px solid #dc2626"><p>Aucune référence correspondante trouvée.</p></div>
<?php endif; ?>

<?php if($found && !empty($found['is_receipt'])): ?>
  <div class="card" style="border-left:4px solid #16a34a">
    <h2>🧾 Reçu trouvé</h2>
    <p>Référence : <b><?=e($found['reference'])?></b></p>
    <p>Client : <b><?=e($found['client_name']??'')?></b> · <?=e($found['client_phone']??'')?></p>
    <p>Type : <?=e($found['type']??'')?> — <?=e($found['item_title']??'')?></p>
    <p>Montant : <b style="color:var(--gold)"><?=money((float)($found['amount']??0))?></b></p>
    <p>Statut : <span class="status <?=($found['status']??'')==='Payé'?'ok':'warn'?>"><?=e($found['status']??'')?></span></p>
    <p>Date : <?=e($found['date']??'')?></p>
  </div>
<?php elseif($found): ?>
  <div class="card" style="border-left:4px solid #16a34a">
    <h2>🔑 Bail trouvé</h2>
    <p>Référence : <b><?=e($found['reference'])?></b></p>
    <p>Locataire : <b><?=e($found['tenant_name']??'')?></b> · <?=e($found['tenant_phone']??'')?> <?=e($found['tenant_email']?'· '.$found['tenant_email']:'')?></p>
    <p>Bien : <?=e($found['item_title']??'')?> (<?=e($found['item_type']??'')?>)</p>
    <p>Loyer mensuel : <b style="color:var(--gold)"><?=money((float)($found['monthly_rent']??0))?></b></p>
    <p>Statut : <span class="status ok"><?=e($found['status']??'Actif')?></span></p>
    <p>Créé le : <?=e($found['date']??'')?></p>
    <?php if(!empty($found['client_ref'])): ?><p><a class="btn btn-gold" href="/admin/dossier-client.php?ref=<?=urlencode($found['client_ref'])?>">📁 Voir le dossier client complet (<?=e($found['client_ref'])?>)</a></p><?php endif; ?>
  </div>
  <h3>Historique des paiements de loyer</h3>
  <?php if(!$foundReceipts): ?><div class="card"><p>Aucun paiement de loyer enregistré pour ce bail pour le moment.</p></div><?php endif; ?>
  <?php foreach(array_reverse($foundReceipts) as $r): ?>
    <div class="card">
      <p><b><?=money((float)($r['amount']??0))?></b> — <?=e($r['payment_method']??'')?> — <span class="status <?=($r['status']??'')==='Payé'?'ok':'warn'?>"><?=e($r['status']??'')?></span></p>
      <p style="color:var(--muted);font-size:13px">Réf reçu : <?=e($r['reference']??'')?> · <?=e($r['date']??'')?></p>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<h2 style="margin-top:26px">Tous les baux actifs (<?=count($leases)?>)</h2>
<?php foreach(array_reverse($leases) as $l): ?>
  <div class="card" style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;align-items:center">
    <div><b><?=e($l['reference'])?></b><br><span style="color:var(--muted);font-size:13px"><?=e($l['tenant_name']??'')?> · <?=e($l['item_title']??'')?> · <?=money((float)($l['monthly_rent']??0))?>/mois</span></div>
    <a class="btn btn-light" href="/admin/leases.php?q=<?=urlencode($l['reference'])?>">Voir</a>
  </div>
<?php endforeach; ?>

<h2 style="margin-top:26px">💰 Historique des paiements de loyer (comptabilité)</h2>
<?php
$rentReceipts=array_values(array_filter($receipts,fn($r)=>($r['type']??'')==='Loyer'));
usort($rentReceipts,fn($a,$b)=>strcmp($b['date']??'',$a['date']??''));
if(!$rentReceipts): ?><div class="card"><p>Aucun paiement de loyer enregistré pour le moment.</p></div>
<?php else: foreach(array_slice($rentReceipts,0,50) as $r): ?>
  <div class="card" style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;align-items:center">
    <div>
      <b><?=e($r['client_name']??'')?></b> — <?=e($r['item_title']??'')?><br>
      <span style="color:var(--muted);font-size:13px">Réf : <?=e($r['reference']??'')?> · <?=e($r['note']??'')?> · <?=e($r['date']??'')?></span>
    </div>
    <div style="text-align:right">
      <b style="color:var(--gold)"><?=money((float)($r['amount']??0))?></b><br>
      <span class="status <?=($r['status']??'')==='Payé'?'ok':'warn'?>"><?=e($r['status']??'')?></span>
    </div>
  </div>
<?php endforeach; endif; ?>
</main></div></body></html>
