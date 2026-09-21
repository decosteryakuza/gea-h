<?php
require_once __DIR__.'/../core.php';

$ref = trim($_GET['ref'] ?? '');
$r = $ref ? geah_find_receipt($ref) : null;
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Vérification reçu GEA-H</title>
<link rel="stylesheet" href="/assets/css/style.css?v=110">
</head>
<body>
<header class="header">
<div class="wrap nav">
<a class="brand" href="/">GEA-HOLDING.SAU</a>
</div>
</header>

<section class="section">
<div class="wrap">
<h1>Vérification de reçu</h1>

<form class="card" method="get">
<label>Référence du reçu
<input name="ref" value="<?=e($ref)?>" placeholder="Ex : GEAH-PAIEMENT-20260623-ABC123">
</label>
<button class="btn btn-primary">Vérifier</button>
</form>

<?php if($ref && !$r): ?>
<div class="card">
<h2>Reçu introuvable</h2>
<p>Aucun reçu ne correspond à cette référence.</p>
</div>
<?php endif; ?>

<?php if($r): ?>
<div class="receipt-box">
<div class="receipt-header">
<div>
<h2>GEA-HOLDING.SAU</h2>
<p>Reçu vérifié</p>
</div>
<div>
<div class="receipt-ref"><?=e($r['reference'])?></div>
<p>Statut : <b><?=e($r['status'])?></b></p>
</div>
</div>

<div class="receipt-line"><span>Client</span><b><?=e($r['client_name'])?></b></div>
<div class="receipt-line"><span>Type</span><b><?=e($r['type'])?></b></div>
<div class="receipt-line"><span>Désignation</span><b><?=e($r['item_title'])?></b></div>
<div class="receipt-line"><span>Montant</span><b><?=money($r['amount'])?> <?=e($r['currency'])?></b></div>
<div class="receipt-line"><span>Date</span><b><?=e($r['date'])?></b></div>

<p><span class="status ok">Référence valide</span></p>
<p class="no-print"><button class="btn btn-gold" onclick="window.print()">🖨️ Imprimer / PDF</button></p>
</div>
<?php endif; ?>
</div>
</section>
<style>@media print{.header,.no-print,#geah-chat-btn,#geahHomeBtn{display:none!important}}</style>
<script>
(function(){const saved=localStorage.getItem('geah_theme')||'dark';if(saved==='light')document.body.classList.add('light-mode');function ready(){if(document.querySelector('.theme-toggle'))return;const btn=document.createElement('button');btn.className='theme-toggle';function label(){btn.textContent=document.body.classList.contains('light-mode')?'🌙 Mode sombre':'☀️ Mode clair'}btn.onclick=function(){document.body.classList.toggle('light-mode');localStorage.setItem('geah_theme',document.body.classList.contains('light-mode')?'light':'dark');label()};label();document.body.appendChild(btn)}if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',ready);else ready();})();
</script>
<?php echo geah_footer(); ?></body>
</html>
