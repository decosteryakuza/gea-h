<?php
$plansFile = __DIR__ . '/data/subscription_plans.json';
$plans = file_exists($plansFile) ? (json_decode(file_get_contents($plansFile), true) ?: []) : [];
?>
<!doctype html><html lang="fr"><head><meta charset="utf-8">
<title>GEA-H - Abonnements</title><link rel="stylesheet" href="assets/css/geah-v21-admin.css"></head>
<body><main class="geah-admin"><h1>Abonnements GEA-H</h1>
<p class="lead">Certaines fonctions avancées sont payantes : export de plan, image HD, vidéo, lotissement IA, Studio 3D avancé.</p>
<section class="grid"><?php foreach($plans as $p): ?><article class="card">
<h2><?=htmlspecialchars($p['name'])?></h2><p><b><?=number_format($p['price'],0,',',' ')?> FCFA</b></p>
<ul><?php foreach($p['features'] as $f): ?><li><?=htmlspecialchars($f)?></li><?php endforeach; ?></ul>
<?php if($p['id'] !== 'free'): ?><a href="payment.php?plan=<?=urlencode($p['id'])?>">Payer / S’abonner</a><?php endif; ?>
</article><?php endforeach; ?></section></main></body></html>
