<?php require_once __DIR__.'/../core.php'; require_once __DIR__.'/auth.php'; ?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Test affichage admin</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main">
<h1>Test affichage admin</h1>
<div class="card"><p>Si cette page est lisible sans être cachée par le menu de gauche, la correction V26 est active.</p></div>
<div class="kpis"><?php for($i=1;$i<=8;$i++): ?><div class="kpi"><span>Bloc <?=$i?></span><b>OK</b></div><?php endfor; ?></div>
<div class="grid"><?php for($i=1;$i<=6;$i++): ?><div class="card"><h3>Carte <?=$i?></h3><p>Contenu aligné correctement à droite du menu.</p></div><?php endfor; ?></div>
</main></div></body></html>