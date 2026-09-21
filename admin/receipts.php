<?php
require_once __DIR__.'/../core.php';
require_once __DIR__.'/auth.php';

$receipts = data_list('receipts');

if($_SERVER['REQUEST_METHOD']==='POST'){
    if(isset($_POST['delete'])){
        $id = (int)$_POST['delete'];
        $receipts = array_values(array_filter($receipts, fn($r)=>($r['id']??0)!=$id));
        data_save('receipts',$receipts);
        header('Location:/admin/receipts.php?deleted=1');
        exit;
    }

    $type = $_POST['type'] ?? 'Paiement';
    $reference = geah_receipt_reference($type);

    $receipt = [
        'id'=>time(),
        'reference'=>$reference,
        'type'=>$type,
        'client_name'=>$_POST['client_name'] ?? '',
        'client_phone'=>$_POST['client_phone'] ?? '',
        'client_email'=>$_POST['client_email'] ?? '',
        'item_type'=>$_POST['item_type'] ?? '',
        'item_title'=>$_POST['item_title'] ?? '',
        'amount'=>(float)($_POST['amount'] ?? 0),
        'currency'=>$_POST['currency'] ?? 'FCFA',
        'payment_method'=>$_POST['payment_method'] ?? '',
        'status'=>$_POST['status'] ?? 'Payé',
        'note'=>$_POST['note'] ?? '',
        'created_by'=>$_SESSION['admin']['email'] ?? 'admin',
        'date'=>now()
    ];

    $receipts[] = $receipt;
    data_save('receipts',$receipts);
    log_action('Reçu généré : '.$reference);

    header('Location:/admin/receipt-view.php?ref='.urlencode($reference));
    exit;
}
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Reçus & Références</title>
<link rel="stylesheet" href="/assets/css/style.css?v=110">
</head>
<body>
<div class="admin-layout">
<?php include __DIR__.'/sidebar.php'; ?>
<main class="admin-main">

<h1>Reçus & Références</h1>
<p>Génère un reçu avec référence unique pour paiement, souscription, réservation ou cotisation.</p>

<?php if(isset($_GET['deleted'])): ?><p class="status warn">Reçu supprimé.</p><?php endif; ?>

<form class="card" method="post">
<h2>Créer un reçu</h2>
<div class="form-grid">
<label>Type d’opération
<select name="type">
<option value="PAIEMENT">Paiement d’un bien</option>
<option value="SOUSCRIPTION">Souscription</option>
<option value="RESERVATION">Réservation</option>
<option value="COTISATION">Cotisation / GEA-H Bank</option>
<option value="TONTINE">Tontine</option>
<option value="TRANSFERT">Transfert</option>
</select>
</label>

<label>Client
<input name="client_name" required placeholder="Nom du client">
</label>

<label>Téléphone
<input name="client_phone" placeholder="+225...">
</label>

<label>Email
<input name="client_email" type="email">
</label>

<label>Objet
<select name="item_type">
<option>Bien immobilier</option>
<option>Résidence meublée</option>
<option>Hôtel</option>
<option>Enchère</option>
<option>GEA-H Bank</option>
<option>Document</option>
<option>Autre</option>
</select>
</label>

<label>Désignation
<input name="item_title" placeholder="Ex : Villa Riviera, Résidence meublée Cocody...">
</label>

<label>Montant
<input name="amount" type="number" required placeholder="Ex : 50000">
</label>

<label>Devise
<select name="currency">
<option>FCFA</option>
<option>EUR</option>
<option>USD</option>
<option>AED</option>
</select>
</label>

<label>Moyen de paiement
<select name="payment_method">
<option>Mobile Money</option>
<option>Espèces</option>
<option>Virement bancaire</option>
<option>Carte bancaire</option>
<option>CinetPay</option>
<option>PayDunya</option>
<option>Flutterwave</option>
<option>FedaPay</option>
<option>PayPal</option>
</select>
</label>

<label>Statut
<select name="status">
<option>Payé</option>
<option>En attente</option>
<option>Partiel</option>
<option>Réservé</option>
<option>Annulé</option>
<option>Remboursé</option>
</select>
</label>

<label class="full">Note
<textarea name="note" rows="3" placeholder="Conditions, période de réservation, numéro transaction mobile money, etc."></textarea>
</label>
</div>

<button class="btn btn-primary">Générer le reçu</button>
</form>

<h2>Reçus générés</h2>
<table class="table">
<tr>
<th>Date</th>
<th>Référence</th>
<th>Client</th>
<th>Type</th>
<th>Montant</th>
<th>Statut</th>
<th></th>
</tr>
<?php foreach(array_reverse($receipts) as $r): ?>
<tr>
<td><?=e($r['date'] ?? '')?></td>
<td><b><?=e($r['reference'] ?? '')?></b></td>
<td><?=e($r['client_name'] ?? '')?></td>
<td><?=e($r['type'] ?? '')?></td>
<td><?=money($r['amount'] ?? 0)?> <?=e($r['currency'] ?? '')?></td>
<td><?=e($r['status'] ?? '')?></td>
<td>
<a class="btn btn-gold" href="/admin/receipt-view.php?ref=<?=urlencode($r['reference'])?>">Voir</a>
<form method="post" style="display:inline">
<button class="btn danger" name="delete" value="<?=e($r['id'])?>">Supprimer</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</table>

</main>
</div>
<script>
(function(){const saved=localStorage.getItem('geah_theme')||'dark';if(saved==='light')document.body.classList.add('light-mode');function ready(){if(document.querySelector('.theme-toggle'))return;const btn=document.createElement('button');btn.className='theme-toggle';function label(){btn.textContent=document.body.classList.contains('light-mode')?'🌙 Mode sombre':'☀️ Mode clair'}btn.onclick=function(){document.body.classList.toggle('light-mode');localStorage.setItem('geah_theme',document.body.classList.contains('light-mode')?'light':'dark');label()};label();document.body.appendChild(btn)}if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',ready);else ready();})();
</script>
</body>
</html>
