<?php
require_once __DIR__.'/../core.php';
require_once __DIR__.'/auth.php';

$ref = $_GET['ref'] ?? '';
$r = geah_find_receipt($ref);
if(!$r){
    die('Reçu introuvable.');
}
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Reçu <?=e($ref)?></title>
<link rel="stylesheet" href="/assets/css/style.css?v=110">
</head>
<body>
<div class="admin-layout">
<?php include __DIR__.'/sidebar.php'; ?>
<main class="admin-main">

<div class="no-print">
<a class="btn btn-light" href="/admin/receipts.php">Retour</a>
<button class="btn btn-gold" onclick="window.print()">Imprimer / PDF</button>
<a class="btn btn-primary" target="_blank" href="/modules/verify-receipt.php?ref=<?=urlencode($r['reference'])?>">Lien vérification client</a>
</div>

<div class="receipt-box">
<div class="receipt-header">
<div>
<h1>GEA-HOLDING.SAU</h1>
<p>L’immobilier du 3ᵉ millénaire</p>
</div>
<div>
<div class="receipt-ref"><?=e($r['reference'])?></div>
<p>Date : <?=e($r['date'])?></p>
<p>Statut : <b><?=e($r['status'])?></b></p>
</div>
</div>

<h2>REÇU DE <?=e($r['type'])?></h2>

<div class="receipt-line"><span>Client</span><b><?=e($r['client_name'])?></b></div>
<div class="receipt-line"><span>Téléphone</span><b><?=e($r['client_phone'])?></b></div>
<div class="receipt-line"><span>Email</span><b><?=e($r['client_email'])?></b></div>
<div class="receipt-line"><span>Objet</span><b><?=e($r['item_type'])?></b></div>
<div class="receipt-line"><span>Désignation</span><b><?=e($r['item_title'])?></b></div>
<div class="receipt-line"><span>Montant</span><b><?=money($r['amount'])?> <?=e($r['currency'])?></b></div>
<div class="receipt-line"><span>Moyen de paiement</span><b><?=e($r['payment_method'])?></b></div>
<div class="receipt-line"><span>Référence</span><b><?=e($r['reference'])?></b></div>

<?php if(!empty($r['note'])): ?>
<h3>Note</h3>
<p><?=nl2br(e($r['note']))?></p>
<?php endif; ?>

<br>
<p><b>Ce reçu est généré par l’administration GEA-H.</b></p>
<p>Vérification en ligne : /modules/verify-receipt.php?ref=<?=e($r['reference'])?></p>
<br><br>
<div style="display:flex;justify-content:space-between;gap:30px">
<div>Signature client<br><br>_____________________</div>
<div>Signature GEA-H<br><br>_____________________</div>
</div>
</div>

</main>
</div>
<script>
(function(){const saved=localStorage.getItem('geah_theme')||'dark';if(saved==='light')document.body.classList.add('light-mode');function ready(){if(document.querySelector('.theme-toggle'))return;const btn=document.createElement('button');btn.className='theme-toggle';function label(){btn.textContent=document.body.classList.contains('light-mode')?'🌙 Mode sombre':'☀️ Mode clair'}btn.onclick=function(){document.body.classList.toggle('light-mode');localStorage.setItem('geah_theme',document.body.classList.contains('light-mode')?'light':'dark');label()};label();document.body.appendChild(btn)}if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',ready);else ready();})();
</script>
</body>
</html>
