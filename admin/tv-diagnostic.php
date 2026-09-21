<?php
require_once __DIR__.'/../core.php'; require_once __DIR__.'/auth.php'; require_once __DIR__.'/../modules/geah-tv-engine.php';
$playlist=geah_tv_collect_playlist();
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Diagnostic GEA-H TV</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main">
<h1>Diagnostic GEA-H TV</h1><div class="card"><p>Vérifie si les vidéos téléversées sont détectées par le moteur TV.</p></div>
<?=geah_tv_dashboard_html("380px")?>
<div class="card"><h2>Playlist détectée : <?=count($playlist)?> vidéo(s)</h2><table class="table"><thead><tr><th>Titre</th><th>URL</th><th>Type</th></tr></thead><tbody>
<?php foreach($playlist as $v): ?><tr><td><?=e($v['title'])?></td><td><?=e($v['url'])?></td><td><?=e($v['type'])?></td></tr><?php endforeach; ?>
</tbody></table><?php if(!count($playlist)): ?><p class="status warn">Aucune vidéo détectée. Vérifie Admin → GEA-H TV, ou place un fichier .mp4 dans /uploads/tv/.</p><?php endif; ?></div>
</main></div><script src="/assets/js/geah-v30-tv-engine.js" defer></script></body></html>